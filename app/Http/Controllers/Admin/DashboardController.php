<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalEmployees = User::where('role', 'employee')->count();
        $totalManagers = User::where('role', 'manager')->count();
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

        $overdueTasks = Task::where('due_date', '<', now())
            ->whereNotIn('status', [TaskStatus::Done->value])
            ->count();

        $completedTasks = Task::where('status', TaskStatus::Done->value)->count();
        $taskCompletionRate = $totalTasks > 0
            ? round(($completedTasks / $totalTasks) * 100, 1)
            : 0;

        // Recent tasks
        $recentTasks = Task::with(['assignees'])
            ->latest()
            ->limit(10)
            ->get();

        // Tasks assigned to each employee (top performers)
        $topPerformers = User::where('role', 'employee')
            ->withCount(['assignedTasks as tasks_count' => function ($q) {
                $q->where('status', TaskStatus::Done->value);
            }])
            ->orderBy('tasks_count', 'desc')
            ->limit(5)
            ->get();

        // Tasks created in last 7 days
        $last7Days = [];
        $last14Days = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $key = $date->format('Y-m-d');
            $label = $date->format('M d');

            $count = Task::whereDate('created_at', $date)->count();

            if ($i >= 7) {
                $last14Days[$label] = $count;
            } else {
                $last7Days[$label] = $count;
            }
        }

        // Merge for single chart
        $taskTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $label = $date->format('M d');
            $count = Task::whereDate('created_at', $date)->count();
            $taskTrend[$label] = $count;
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalEmployees',
            'totalManagers',
            'totalTasks',
            'tasksByStatus',
            'priorityCounts',
            'overdueTasks',
            'completedTasks',
            'taskCompletionRate',
            'recentTasks',
            'topPerformers',
            'taskTrend'
        ));
    }
}