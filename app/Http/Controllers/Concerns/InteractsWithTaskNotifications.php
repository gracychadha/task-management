<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;

trait InteractsWithTaskNotifications
{
    protected function notifyAssignment(Task $task, int $userId): void
    {
        if ($userId === auth()->id()) {
            return;
        }

        $lines = collect([
            "Task '{$task->title}' has been assigned to you.",
            '',
            'Priority: '.$task->getPriorityLabel(),
            $task->start_date ? 'Start date: '.$task->start_date->format('M d, Y') : null,
            $task->due_date ? 'Due date: '.$task->due_date->format('M d, Y') : null,
            $task->estimated_hours !== null ? 'Estimated hours: '.$task->estimated_hours : null,
            'Assignees: '.($task->assignees->pluck('name')->implode(', ') ?: 'Unassigned'),
            'Created by: '.($task->creator?->name ?? 'Unknown'),
            '',
            'Description:',
            $task->description ? str($task->description)->trim()->limit(200, '…') : '(none)',
        ])->filter()->implode("\n");

        $this->notifyUser($userId, 'task_assignment', 'New task assigned to you', $lines, $task->id);
    }

    protected function notifyAssignees(Task $task, string $type, string $title, string $message, array $except = []): void
    {
        foreach ($task->assignees as $assignee) {
            if (in_array($assignee->id, $except, true)) {
                continue;
            }
            $this->notifyUser($assignee->id, $type, $title, $message, $task->id);
        }
    }

    protected function notifyManagers(Task $task, string $type, string $title, string $message, array $except = []): void
    {
        $managerIds = collect();

        if ($task->creator && ($task->creator->isManager() || $task->creator->isAdmin())) {
            $managerIds->push($task->creator->id);
        }

        $departmentIds = $task->assignees->pluck('department_id')->filter()->unique();

        if ($task->department_id) {
            $departmentIds->push($task->department_id);
        }

        $managerIds = $managerIds->merge(
            User::where('role', 'manager')
                ->whereIn('department_id', $departmentIds)
                ->pluck('id')
        );

        foreach ($managerIds->unique() as $userId) {
            if (in_array($userId, $except, true) || $userId === auth()->id()) {
                continue;
            }
            $this->notifyUser($userId, $type, $title, $message, $task->id);
        }
    }

    protected function notifyAdmins(Task $task, string $type, string $title, string $message, array $except = []): void
    {
        $adminIds = User::where('role', 'admin')->pluck('id');

        foreach ($adminIds as $adminId) {
            if (in_array($adminId, $except, true) || $adminId === auth()->id()) {
                continue;
            }
            $this->notifyUser($adminId, $type, $title, $message, $task->id);
        }
    }

    protected function notifyUser(int $userId, string $type, string $title, string $message, ?int $taskId): void
    {
        UserNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => ['task_id' => $taskId],
        ]);
    }
}
