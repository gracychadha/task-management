<?php

namespace App\Http\Controllers\Employee;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\UserNotification;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $assigned = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id));

        $completedTasks = (clone $assigned)->where('status', TaskStatus::Completed->value)->count();
        $inProgressTasks = (clone $assigned)->where('status', TaskStatus::InProgress->value)->count();
        $newTasks = (clone $assigned)->where('status', TaskStatus::New->value)->count();
        $underReviewTasks = (clone $assigned)->where('status', TaskStatus::UnderReview->value)->count();
        $changesRequestedTasks = (clone $assigned)->where('status', TaskStatus::ChangesRequested->value)->count();
        $overdueTasks = (clone $assigned)->overdue()->count();
        $totalAssigned = (clone $assigned)->count();

        $completionRate = $totalAssigned > 0
            ? round(($completedTasks / $totalAssigned) * 100, 1)
            : 0;

        $tasksByStatus = [];
        $statusCounts = (clone $assigned)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach (TaskStatus::cases() as $status) {
            $tasksByStatus[$status->value] = $statusCounts[$status->value] ?? 0;
        }

        $priorityCounts = (clone $assigned)
            ->selectRaw('priority, count(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();

        $myTasks = (clone $assigned)
            ->where('status', '!=', TaskStatus::Completed->value)
            ->with(['assignees', 'creator'])
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $pendingReviewCount = Task::pendingReviewFor($user->id)->count();

        $reviewTasks = Task::where('reviewer_id', $user->id)
            ->with(['assignees', 'creator'])
            ->where('status', '!=', TaskStatus::Completed->value)
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $upcomingTasks = (clone $assigned)
            ->where('status', '!=', TaskStatus::Completed->value)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->with(['assignees'])
            ->orderBy('due_date')
            ->get();

        $recentCompleted = (clone $assigned)
            ->where('status', TaskStatus::Completed->value)
            ->with(['assignees'])
            ->latest()
            ->limit(5)
            ->get();

        $unreadNotifications = UserNotification::where('user_id', $user->id)
            ->unread()
            ->count();

        $taskTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = (clone $assigned)->whereDate('created_at', $date)->count();
            $taskTrend[$date->format('M d')] = $count;
        }

        return view('employee.dashboard', compact(
            'totalAssigned',
            'completedTasks',
            'inProgressTasks',
            'newTasks',
            'underReviewTasks',
            'changesRequestedTasks',
            'overdueTasks',
            'completionRate',
            'tasksByStatus',
            'priorityCounts',
            'myTasks',
            'reviewTasks',
            'pendingReviewCount',
            'upcomingTasks',
            'recentCompleted',
            'unreadNotifications',
            'taskTrend'
        ));
    }
}
