<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalEmployees = User::where('role', 'employee')->count();
        $totalManagers = User::where('role', 'manager')->count();
        $totalDepartments = Department::where('is_active', true)->count();
        $totalTasks = Task::count();

        $statusCounts = Task::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $priorityCounts = Task::selectRaw('priority, count(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();

        $tasksByStatus = [];
        foreach (TaskStatus::cases() as $status) {
            $tasksByStatus[$status->value] = $statusCounts[$status->value] ?? 0;
        }

        $newTasks = Task::where('status', TaskStatus::New->value)->count();
        $inProgressTasks = Task::where('status', TaskStatus::InProgress->value)->count();
        $underReviewTasks = Task::where('status', TaskStatus::UnderReview->value)->count();
        $changesRequestedTasks = Task::where('status', TaskStatus::ChangesRequested->value)->count();
        $overdueTasks = Task::overdue()->count();
        $completedTasks = Task::where('status', TaskStatus::Completed->value)->count();

        $taskCompletionRate = $totalTasks > 0
            ? round(($completedTasks / $totalTasks) * 100, 1)
            : 0;

        $recentTasks = Task::with(['assignees', 'reviewer'])
            ->latest()
            ->limit(10)
            ->get();

        $attentionTasks = Task::with(['assignees', 'reviewer'])
            ->whereIn('status', [TaskStatus::ChangesRequested->value, TaskStatus::UnderReview->value])
            ->latest()
            ->limit(10)
            ->get();

        $recentActivity = Activity::with('causer')
            ->latest()
            ->limit(10)
            ->get();

        $topPerformers = User::where('role', 'employee')
            ->withCount(['assignedTasks as tasks_count' => function ($q) {
                $q->where('status', TaskStatus::Completed->value);
            }])
            ->orderBy('tasks_count', 'desc')
            ->limit(5)
            ->get();

        $taskTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $label = $date->format('M d');
            $taskTrend[$label] = Task::whereDate('created_at', $date)->count();
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalEmployees',
            'totalManagers',
            'totalDepartments',
            'totalTasks',
            'tasksByStatus',
            'priorityCounts',
            'newTasks',
            'inProgressTasks',
            'underReviewTasks',
            'changesRequestedTasks',
            'overdueTasks',
            'completedTasks',
            'taskCompletionRate',
            'recentTasks',
            'attentionTasks',
            'recentActivity',
            'topPerformers',
            'taskTrend'
        ));
    }
}
