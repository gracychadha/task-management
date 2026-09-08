<?php

namespace App\Http\Controllers\Manager;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $departmentId = $user->department_id;

        $taskQuery = Task::whereHas('assignees', function ($q) use ($departmentId) {
            if ($departmentId) {
                $q->where('users.department_id', $departmentId);
            } else {
                $q->where('users.id', $user->id);
            }
        });

        $totalTasks = (clone $taskQuery)->count();
        $completedTasks = (clone $taskQuery)->where('status', TaskStatus::Done->value)->count();
        $inProgressTasks = (clone $taskQuery)->where('status', TaskStatus::InProgress->value)->count();
        $overdueTasks = (clone $taskQuery)->overdue()->count();

        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        // Tasks by status
        $statusCounts = (clone $taskQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusData = [];
        foreach (TaskStatus::cases() as $status) {
            $statusData[$status->value] = $statusCounts[$status->value] ?? 0;
        }

        // Department members performance
        $members = User::where('department_id', $departmentId)
            ->where('id', '!=', $user->id)
            ->get();

        $memberPerformance = $members->map(function ($member) {
            $memberTasks = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $member->id));
            $total = (clone $memberTasks)->count();
            $completed = (clone $memberTasks)->where('status', TaskStatus::Done->value)->count();
            return [
                'name' => $member->name,
                'avatar' => $member->avatar,
                'total' => $total,
                'completed' => $completed,
                'rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            ];
        })->sortByDesc('rate')->values();

        // Recent assignments in my department
        $recentTasks = Task::with(['assignees'])
            ->whereHas('assignees', function ($q) use ($departmentId, $user) {
                if ($departmentId) {
                    $q->where('users.department_id', $departmentId);
                } else {
                    $q->where('users.id', $user->id);
                }
            })
            ->latest()
            ->limit(8)
            ->get();

        // Upcoming deadlines (next 7 days)
        $upcomingTasks = Task::with(['assignees'])
            ->whereHas('assignees', function ($q) use ($departmentId, $user) {
                if ($departmentId) {
                    $q->where('users.department_id', $departmentId);
                } else {
                    $q->where('users.id', $user->id);
                }
            })
            ->where('status', '!=', TaskStatus::Done->value)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->orderBy('due_date')
            ->get();

        // Tasks per day for trend chart
        $taskTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Task::whereHas('assignees', function ($q) use ($departmentId, $user) {
                if ($departmentId) {
                    $q->where('users.department_id', $departmentId);
                } else {
                    $q->where('users.id', $user->id);
                }
            })
                ->whereDate('created_at', $date)
                ->count();
            $taskTrend[$date->format('M d')] = $count;
        }

        return view('manager.dashboard', compact(
            'totalTasks',
            'completedTasks',
            'inProgressTasks',
            'overdueTasks',
            'completionRate',
            'statusData',
            'memberPerformance',
            'recentTasks',
            'upcomingTasks',
            'taskTrend'
        ));
    }
}