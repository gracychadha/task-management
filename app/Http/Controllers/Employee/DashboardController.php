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

        $totalAssigned = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))->count();
        $completedTasks = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->where('status', TaskStatus::Done->value)
            ->count();
        $inProgressTasks = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->where('status', TaskStatus::InProgress->value)
            ->count();
        $overdueTasks = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))->overdue()->count();

        $completionRate = $totalAssigned > 0
            ? round(($completedTasks / $totalAssigned) * 100, 1)
            : 0;

        $tasksByStatus = [];
        $statusCounts = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach (TaskStatus::cases() as $status) {
            $tasksByStatus[$status->value] = $statusCounts[$status->value] ?? 0;
        }

        $myTasks = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->where('status', '!=', TaskStatus::Done->value)
            ->with(['assignees', 'creator'])
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $upcomingTasks = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->where('status', '!=', TaskStatus::Done->value)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->with(['assignees'])
            ->orderBy('due_date')
            ->get();

        $recentCompleted = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->where('status', TaskStatus::Done->value)
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
            $count = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
                ->whereDate('created_at', $date)
                ->count();
            $taskTrend[$date->format('M d')] = $count;
        }

        return view('employee.dashboard', compact(
            'totalAssigned',
            'completedTasks',
            'inProgressTasks',
            'overdueTasks',
            'completionRate',
            'tasksByStatus',
            'myTasks',
            'upcomingTasks',
            'recentCompleted',
            'unreadNotifications',
            'taskTrend'
        ));
    }
}
