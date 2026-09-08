<?php

namespace App\Http\Controllers\Manager;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
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
            $total = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $member->id))
                ->count();
            $completed = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $member->id))
                ->where('status', TaskStatus::Done->value)
                ->count();
            $inProgress = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $member->id))
                ->where('status', TaskStatus::InProgress->value)
                ->count();
            $overdue = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $member->id))
                ->overdue()
                ->count();

            return [
                'name' => $member->name,
                'email' => $member->email,
                'department' => $member->department?->name,
                'total_tasks' => $total,
                'completed_tasks' => $completed,
                'in_progress_tasks' => $inProgress,
                'overdue_tasks' => $overdue,
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
                ->where('status', TaskStatus::Done->value)
                ->count();

            $tasksByMonth[$date->format('M Y')] = [
                'total' => $total,
                'completed' => $completed,
            ];
        }

        return view('manager.performance', compact('memberStats', 'tasksByMonth'));
    }
}
