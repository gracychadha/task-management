<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskWorkflowService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private readonly TaskWorkflowService $workflow) {}

    public function assign(Request $request, Task $task)
    {
        $this->authorizeManage($task);

        $validated = $request->validate([
            'reviewer_id' => 'required|exists:users,id',
        ]);

        $reviewer = User::findOrFail($validated['reviewer_id']);
        if (! $reviewer->isEmployee() || ! $reviewer->is_active) {
            return back()->withErrors(['reviewer_id' => 'You can only assign an active employee as reviewer.']);
        }

        $this->workflow->assignReviewer($task, auth()->user(), $reviewer->id);

        return back()->with('success', "{$reviewer->name} has been assigned to review this task.");
    }

    public function approve(Request $request, Task $task)
    {
        $validated = $request->validate([
            'review_comment' => 'nullable|string|max:5000',
        ]);

        $this->workflow->approve($task, auth()->user(), $validated['review_comment'] ?? null);

        return back()->with('success', 'Task approved. Task is now complete.');
    }

    public function requestChanges(Request $request, Task $task)
    {
        $validated = $request->validate([
            'review_comment' => 'required|string|max:5000',
        ]);

        $this->workflow->requestChanges($task, auth()->user(), $validated['review_comment']);

        return back()->with('success', 'Changes requested. Task returned to the employee with your remarks.');
    }

    public function sendBack(Request $request, Task $task)
    {
        $validated = $request->validate([
            'assignee_id' => 'nullable|exists:users,id',
            'update_message' => 'required|string|max:5000',
        ]);

        $this->workflow->sendBack($task, auth()->user(), $validated['update_message'], $validated['assignee_id'] ?? null);

        return back()->with('success', 'Task sent back to the employee with updates.');
    }

    private function authorizeManage(Task $task): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isManager()) {
            $access = $task->created_by === $user->id
                || $task->assignees()->where('users.id', $user->id)->exists()
                || ($user->department_id && $task->assignees()->where('users.department_id', $user->department_id)->exists());

            if ($access) {
                return;
            }
        }

        abort(403, 'You do not have permission to manage this task.');
    }
}
