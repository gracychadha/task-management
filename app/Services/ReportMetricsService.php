<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Department;
use App\Models\Task;
use App\Models\TaskReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportMetricsService
{
    public function dateRange(?string $from, ?string $to): array
    {
        $dateFrom = $from ? Carbon::parse($from)->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d');
        $dateTo = $to ? Carbon::parse($to)->format('Y-m-d') : now()->format('Y-m-d');

        return [$dateFrom, $dateTo];
    }

    public function scopeQuery(Builder $query, string $dateFrom, string $dateTo, ?Builder $base = null): Builder
    {
        $query = $query->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59']);

        return $query;
    }

    public function employeePerformance(User $employee, string $dateFrom, string $dateTo): array
    {
        $tasks = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $employee->id))
            ->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
            ->with([
                'reviews',
                'comments' => fn ($q) => $q->where('user_id', $employee->id),
                'attachments' => fn ($q) => $q->where('user_id', $employee->id),
            ])
            ->get();

        $total = $tasks->count();
        $completed = $tasks->where('status', TaskStatus::Completed->value);
        $inProgress = $tasks->where('status', TaskStatus::InProgress->value)->count();
        $underReview = $tasks->where('status', TaskStatus::UnderReview->value)->count();
        $changesRequested = $tasks->where('status', TaskStatus::ChangesRequested->value)->count();
        $overdue = $tasks->filter(fn (Task $task) => $task->isOverdue())->count();

        $updates = $tasks->sum('updates_count');
        $reviewCycles = $tasks->sum(fn (Task $task) => $task->reviews->count());
        $changesRequestedCount = TaskReview::whereIn('task_id', $tasks->pluck('id'))
            ->where('decision', 'changes_requested')
            ->count();
        $resubmissions = $tasks->sum(fn (Task $task) => $task->resubmissions());
        $comments = $tasks->sum(fn (Task $task) => $task->comments->count());

        $timeDiffs = $completed->map(function (Task $task) {
            $end = $task->completed_at ?? $task->reviewed_at ?? $task->updated_at;
            $start = $task->start_date ?? $task->created_at;

            return $start->diffInDays($end, true);
        });

        $averageCompletionDays = $timeDiffs->isNotEmpty() ? round($timeDiffs->avg(), 1) : 0;

        return [
            'employee' => $employee,
            'tasks' => $tasks,
            'total' => $total,
            'completed' => $completed->count(),
            'in_progress' => $inProgress,
            'under_review' => $underReview,
            'changes_requested_status' => $changesRequested,
            'overdue' => $overdue,
            'completion_rate' => $total > 0 ? round(($completed->count() / $total) * 100, 1) : 0,
            'updates' => $updates,
            'review_cycles' => $reviewCycles,
            'changes_requested' => $changesRequestedCount,
            'resubmissions' => $resubmissions,
            'comments' => $comments,
            'average_completion_days' => $averageCompletionDays,
        ];
    }

    public function employeeTaskRows(Collection $tasks): Collection
    {
        return $tasks->map(function (Task $task) {
            $end = $task->completed_at ?? $task->reviewed_at ?? null;
            $start = $task->start_date ?? $task->created_at;
            $completionDays = $end ? round($start->diffInDays($end, true), 1) : null;

            return [
                'task' => $task,
                'status' => $task->getStatusLabel(),
                'updates' => $task->updates_count,
                'review_cycles' => $task->reviews->count(),
                'changes_requested' => $task->reviews()->where('decision', 'changes_requested')->count(),
                'completion_days' => $completionDays,
            ];
        });
    }

    public function departmentPerformance(Department $department, string $dateFrom, string $dateTo): array
    {
        $employees = $department->users;
        $employeeIds = $employees->pluck('id');

        $tasks = Task::where('department_id', $department->id)
            ->orWhereHas('assignees', fn ($q) => $q->where('users.department_id', $department->id))
            ->where(function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59']);
            })
            ->get();

        $total = $tasks->count();
        $completed = $tasks->where('status', TaskStatus::Completed->value);

        $completionDays = $completed->map(function (Task $task) {
            $end = $task->completed_at ?? $task->reviewed_at ?? $task->updated_at;
            $start = $task->start_date ?? $task->created_at;

            return $start->diffInDays($end, true);
        });

        return [
            'department' => $department,
            'employees' => $employees->count(),
            'tasks' => $tasks,
            'total' => $total,
            'completed' => $completed->count(),
            'pending' => $tasks->where('status', TaskStatus::New->value)->count(),
            'in_progress' => $tasks->where('status', TaskStatus::InProgress->value)->count(),
            'under_review' => $tasks->where('status', TaskStatus::UnderReview->value)->count(),
            'changes_requested' => $tasks->where('status', TaskStatus::ChangesRequested->value)->count(),
            'overdue' => $tasks->filter(fn (Task $task) => $task->isOverdue())->count(),
            'completion_rate' => $total > 0 ? round(($completed->count() / $total) * 100, 1) : 0,
            'average_completion_days' => $completionDays->isNotEmpty() ? round($completionDays->avg(), 1) : 0,
        ];
    }

    public function updateReport(Collection $tasks): Collection
    {
        return $tasks->map(function (Task $task) {
            return [
                'task' => $task,
                'title' => $task->title,
                'assignee' => $task->assignees->pluck('name')->implode(', ') ?: 'Unassigned',
                'status' => $task->getStatusLabel(),
                'updates' => $task->updates_count,
                'review_cycles' => $task->reviewCyclesCount(),
                'changes_requested' => $task->changesRequestedCount(),
                'resubmissions' => $task->resubmissions(),
                'comments' => $task->comments->count(),
                'attachments' => $task->attachments->count(),
            ];
        });
    }

    public function reviewReport(Collection $tasks): Collection
    {
        $reviews = TaskReview::with(['task.assignees', 'reviewer'])
            ->whereIn('task_id', $tasks->pluck('id'))
            ->orderByDesc('reviewed_at')
            ->get();

        return $reviews->map(function (TaskReview $review) {
            return [
                'review' => $review,
                'id' => $review->id,
                'task_id' => $review->task_id,
                'task' => $review->task,
                'reviewer' => $review->reviewer,
                'review_number' => $review->review_number,
                'decision' => $review->decisionLabel(),
                'comment' => $review->comment,
                'reviewed_at' => $review->reviewed_at?->format('Y-m-d H:i'),
            ];
        });
    }

    public function turnaroundReport(Collection $tasks): Collection
    {
        return $tasks->where('status', TaskStatus::Completed->value)->map(function (Task $task) {
            $end = $task->completed_at ?? $task->reviewed_at;
            $start = $task->start_date ?? $task->created_at;
            $days = $end ? round($start->diffInDays($end, true), 1) : null;

            return [
                'task' => $task,
                'title' => $task->title,
                'assignee' => $task->assignees->pluck('name')->implode(', ') ?: 'Unassigned',
                'created_at' => $task->created_at?->format('Y-m-d'),
                'start_date' => $task->start_date?->format('Y-m-d'),
                'completed_at' => $end?->format('Y-m-d H:i'),
                'days' => $days,
            ];
        });
    }
}
