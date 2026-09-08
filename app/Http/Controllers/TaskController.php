<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

class TaskController extends Controller
{
    public function show(Task $task)
    {
        $this->authorizeAccess($task);

        $task->load(['assignees', 'creator', 'comments.user', 'comments' => function ($q) {
            $q->latest();
        }, 'attachments.user', 'labels', 'parent', 'subtasks']);

        return view('tasks.show', compact('task'));
    }

    public function storeComment(Request $request, Task $task)
    {
        $this->authorizeAccess($task);

        $validated = $request->validate([
            'comment' => 'required|string|max:5000',
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->log("commented on task #{$task->id}");

        return back()->with('success', 'Comment added successfully.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorizeUpdate($task);

        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);

        $oldStatus = $task->status;
        $task->update(['status' => $validated['status']]);

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties(['old' => $oldStatus, 'new' => $validated['status']])
            ->log("updated task #{$task->id} status");

        foreach ($task->assignees as $assignee) {
            if (auth()->id() === $assignee->id) {
                continue;
            }
            \App\Models\UserNotification::create([
                'user_id' => $assignee->id,
                'type' => 'task_status',
                'title' => 'Task status updated',
                'message' => "Task '{$task->title}' moved to {$task->status}",
                'data' => ['task_id' => $task->id],
            ]);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Task status updated successfully.');
    }

    public function storeAttachment(Request $request, Task $task)
    {
        $this->authorizeAccess($task);

        $validated = $request->validate([
            'file' => 'required|file|max:20480',
        ]);

        $file = $validated['file'];

        $filePath = $file->store('task-attachments', 'public');
        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->log("attached file '{$attachment->file_name}' to task #{$task->id}");

        return back()->with('success', 'File attached successfully.');
    }

    public function destroyAttachment(Task $task, TaskAttachment $attachment)
    {
        $this->authorizeAccess($task);

        if ($attachment->task_id !== $task->id) {
            abort(404);
        }

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'File removed successfully.');
    }

    private function authorizeAccess(Task $task): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isManager()) {
            $assigneeIds = $task->assignees->pluck('id');

            if ($assigneeIds->contains($user->id) || $task->created_by === $user->id) {
                return;
            }

            $departmentAssignees = $task->assignees->filter(fn ($a) => $a->department_id === $user->department_id);
            if ($departmentAssignees->isNotEmpty()) {
                return;
            }
        }

        if ($task->assignees->contains('id', $user->id) || $task->created_by === $user->id) {
            return;
        }

        abort(403, 'You do not have access to this task.');
    }

    private function authorizeUpdate(Task $task): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isManager() && $task->assignees->contains('id', $user->id)) {
            return;
        }

        if ($task->assignees->contains('id', $user->id)) {
            return;
        }

        abort(403, 'You do not have permission to update this task.');
    }
}