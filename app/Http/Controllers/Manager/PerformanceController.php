<?php

namespace App\Http\Controllers\Manager;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskReview;
use App\Models\User;
use Illuminate\Support\Carbon;

class PerformanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $departmentId = $user->department_id;

        $members = User::where('department_id', $departmentId)
            ->where('id', '!=', $user->id)
            ->get();

        $memberStats = $members->map(function ($member) {
            $tasks = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $member->id))->get();

            $total = $tasks->count();
            $completed = $tasks->where('status', TaskStatus::Completed->value)->count();
            $inProgress = $tasks->where('status', TaskStatus::InProgress->value)->count();
            $underReview = $tasks->where('status', TaskStatus::UnderReview->value)->count();
            $changesRequested = $tasks->where('status', TaskStatus::ChangesRequested->value)->count();
            $overdue = $tasks->filter(fn ($t) => $t->isOverdue())->count();

            $reviewCycles = TaskReview::whereIn('task_id', $tasks->pluck('id'))->count();
            $changesRequestedCount = TaskReview::whereIn('task_id', $tasks->pluck('id'))
                ->where('decision', 'changes_requested')
                ->count();

            return [
                'name' => $member->name,
                'email' => $member->email,
                'department' => $member->department?->name,
                'total_tasks' => $total,
                'completed_tasks' => $completed,
                'in_progress_tasks' => $inProgress,
                'under_review_tasks' => $underReview,
                'changes_requested_tasks' => $changesRequested,
                'overdue_tasks' => $overdue,
                'updates' => $tasks->sum('updates_count'),
                'review_cycles' => $reviewCycles,
                'changes_requested_count' => $changesRequestedCount,
                'resubmissions' => $tasks->sum->resubmissions(),
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            ];
        })->sortByDesc('completion_rate')->values();

        $tasksByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $total = Task::whereHas('assignees', function ($q) use ($departmentId, $user) {
                if ($departmentId) {
                    $q->where('users.department_id', $departmentId);
                } else {
                    $q->where('users.id', $user->id);
                }
            })
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
            $completed = Task::whereHas('assignees', function ($q) use ($departmentId, $user) {
                if ($departmentId) {
                    $q->where('users.department_id', $departmentId);
                } else {
                    $q->where('users.id', $user->id);
                }
            })
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('status', TaskStatus::Completed->value)
                ->count();

            $tasksByMonth[$date->format('M Y')] = [
                'total' => $total,
                'completed' => $completed,
            ];
        }

        return view('manager.performance', compact('memberStats', 'tasksByMonth'));
    }
}
