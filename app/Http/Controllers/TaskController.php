<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Controllers\Concerns\InteractsWithTaskNotifications;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\TaskWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    use InteractsWithTaskNotifications;

    public function __construct(private readonly TaskWorkflowService $workflow) {}

    public function show(Task $task)
    {
        $this->authorizeAccess($task);

        $task->load([
            'assignees',
            'creator',
            'reviewer',
            'reviews' => fn ($q) => $q->with('reviewer')->latest('review_number'),
            'comments.user',
            'attachments.user',
            'submissionAttachment.user',
            'labels',
            'parent',
            'subtasks',
        ]);

        $user = auth()->user();
        $reviewerCandidates = collect();

        if ($user->isAdmin()) {
            $reviewerCandidates = User::where('role', 'employee')->where('is_active', true)->orderBy('name')->get();
        } elseif ($user->isManager()) {
            $reviewerCandidates = User::where('role', 'employee')
                ->where('is_active', true)
                ->when($user->department_id, fn ($q) => $q->where('department_id', $user->department_id))
                ->orderBy('name')
                ->get();
        }

        return view('tasks.show', compact('task', 'reviewerCandidates'));
    }

    public function storeComment(Request $request, Task $task)
    {
        $this->authorizeAccess($task);

        $validated = $request->validate([
            'comment' => 'required|string|max:5000',
        ]);

        TaskComment::create([
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
        $this->authorizeAccess($task);

        $user = auth()->user();

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', TaskStatus::values()),
            'submission_link' => 'nullable|url|max:2048',
            'submission_image' => 'nullable|image|max:4096',
        ]);

        $target = $validated['status'];

        if ($target === TaskStatus::UnderReview->value) {
            $this->workflow->submitForReview(
                $task,
                $user,
                $validated['submission_link'] ?? null,
                $request->hasFile('submission_image') ? $request->file('submission_image') : null
            );
        } elseif ($target === TaskStatus::InProgress->value) {
            if ($task->isNew()) {
                $this->workflow->start($task, $user);
            } elseif ($task->isChangesRequested()) {
                $this->workflow->rethink($task, $user);
            } elseif ($task->isOnHold()) {
                $this->workflow->release($task, $user, TaskStatus::InProgress->value);
            } else {
                $this->workflow->assertCanStatusTransition($task, $user, $target);
                $task->update(['status' => $target]);
            }
        } elseif ($target === TaskStatus::OnHold->value) {
            $this->workflow->hold($task, $user);
        } elseif ($target === TaskStatus::Cancelled->value) {
            $this->workflow->cancel($task, $user);
        } elseif ($target === TaskStatus::New->value) {
            $this->workflow->release($task, $user, TaskStatus::New->value);
        } else {
            abort(403, 'This status change is not allowed.');
        }

        foreach ($task->assignees as $assignee) {
            if (auth()->id() === $assignee->id) {
                continue;
            }
            UserNotification::create([
                'user_id' => $assignee->id,
                'type' => 'task_status',
                'title' => 'Task status updated',
                'message' => "Task {$task->code()} moved to {$task->getStatusLabel()}",
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

        if ($task->reviewer_id === $user->id) {
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
}
