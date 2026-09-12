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

        $scope = Task::query()
            ->where(function ($q) use ($user) {
                $q->whereHas('assignees', function ($q2) use ($user) {
                    $q2->where('users.id', $user->id);
                    if ($user->department_id) {
                        $q2->orWhere('users.department_id', $user->department_id);
                    }
                })->orWhere('created_by', $user->id)
                    ->when($user->department_id, fn ($q3) => $q3->orWhere('department_id', $user->department_id));
            });

        $totalTasks = (clone $scope)->count();
        $newTasks = (clone $scope)->where('status', TaskStatus::New->value)->count();
        $inProgressTasks = (clone $scope)->where('status', TaskStatus::InProgress->value)->count();
        $underReviewTasks = (clone $scope)->where('status', TaskStatus::UnderReview->value)->count();
        $changesRequestedTasks = (clone $scope)->where('status', TaskStatus::ChangesRequested->value)->count();
        $completedTasks = (clone $scope)->where('status', TaskStatus::Completed->value)->count();
        $overdueTasks = (clone $scope)->overdue()->count();

        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        $statusCounts = (clone $scope)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusData = [];
        foreach (TaskStatus::cases() as $status) {
            $statusData[$status->value] = $statusCounts[$status->value] ?? 0;
        }

        $priorityCounts = (clone $scope)
            ->selectRaw('priority, count(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();

        $members = User::where('role', 'employee')
            ->where('department_id', $user->department_id)
            ->where('id', '!=', $user->id)
            ->get();

        $memberPerformance = $members->isNotEmpty()
            ? $members->map(function ($member) {
                $memberTasks = Task::assignedTo($member->id);
                $total = (clone $memberTasks)->count();
                $completed = (clone $memberTasks)->where('status', TaskStatus::Completed->value)->count();

                return [
                    'name' => $member->name,
                    'avatar' => $member->avatar,
                    'total' => $total,
                    'completed' => $completed,
                    'rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
                ];
            })->sortByDesc('rate')->values()
            : collect();

        $recentTasks = (clone $scope)
            ->with(['assignees'])
            ->latest()
            ->limit(8)
            ->get();

        $upcomingTasks = (clone $scope)
            ->with(['assignees'])
            ->where('status', '!=', TaskStatus::Completed->value)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->orderBy('due_date')
            ->get();

        $awaitingReviewTasks = (clone $scope)
            ->with(['assignees', 'reviewer'])
            ->where('status', TaskStatus::UnderReview->value)
            ->latest()
            ->limit(10)
            ->get();

        $attentionTasks = (clone $scope)
            ->with(['assignees', 'reviewer'])
            ->where('status', TaskStatus::ChangesRequested->value)
            ->latest()
            ->limit(10)
            ->get();

        $taskTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = (clone $scope)->whereDate('created_at', $date)->count();
            $taskTrend[$date->format('M d')] = $count;
        }

        return view('manager.dashboard', compact(
            'totalTasks',
            'newTasks',
            'inProgressTasks',
            'underReviewTasks',
            'changesRequestedTasks',
            'completedTasks',
            'overdueTasks',
            'completionRate',
            'statusData',
            'priorityCounts',
            'memberPerformance',
            'recentTasks',
            'upcomingTasks',
            'taskTrend',
            'awaitingReviewTasks',
            'attentionTasks'
        ));
    }
}
