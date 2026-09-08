<?php

namespace App\Http\Controllers\Manager;

use App\Enums\TaskStatus;
use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $departmentId = $user->department_id;

        $dateFrom = $request->date_from ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?: now()->format('Y-m-d');

        $taskQuery = Task::with(['assignees', 'creator'])
            ->whereHas('assignees', function ($q) use ($departmentId, $user) {
                if ($departmentId) {
                    $q->where('users.department_id', $departmentId);
                } else {
                    $q->where('users.id', $user->id);
                }
            })
            ->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59']);

        if ($request->filled('status')) {
            $taskQuery->where('status', $request->status);
        }

        $tasks = $taskQuery->get();
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', TaskStatus::Done->value)->count();
        $inProgressTasks = $tasks->where('status', TaskStatus::InProgress->value)->count();
        $overdueTasks = $tasks->filter(fn ($t) => $t->isOverdue())->count();

        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        $statusBreakdown = [
            'labels' => collect(TaskStatus::cases())->map->label()->values(),
            'data' => collect(TaskStatus::cases())->map(function ($status) use ($tasks) {
                return $tasks->where('status', $status->value)->count();
            }),
        ];

        $taskTrend = $tasks->groupBy(function ($task) {
            return $task->created_at->format('M d');
        })->map->count();

        return view('manager.reports.index', compact(
            'tasks',
            'totalTasks',
            'completedTasks',
            'inProgressTasks',
            'overdueTasks',
            'completionRate',
            'statusBreakdown',
            'taskTrend',
            'dateFrom',
            'dateTo'
        ));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $departmentId = $user->department_id;

        $dateFrom = $request->date_from ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?: now()->format('Y-m-d');

        $tasks = Task::with(['assignees'])
            ->whereHas('assignees', function ($q) use ($departmentId, $user) {
                if ($departmentId) {
                    $q->where('users.department_id', $departmentId);
                } else {
                    $q->where('users.id', $user->id);
                }
            })
            ->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
            ->get();

        $data = $tasks->map(fn ($task) => [
            'Title' => $task->title,
            'Status' => $task->getStatusLabel(),
            'Priority' => $task->getPriorityLabel(),
            'Assignee' => $task->assignees->pluck('name')->implode(', ') ?: 'Unassigned',
            'Department' => $task->assignees->pluck('department.name')->unique()->implode(', '),
            'Due Date' => $task->due_date?->format('Y-m-d'),
            'Created At' => $task->created_at?->format('Y-m-d'),
            'Completed' => $task->isCompleted() ? 'Yes' : 'No',
        ]);

        $fileName = 'team-tasks-report-'.now()->format('Y-m-d-Hi').'.xlsx';

        return Excel::download(new ArrayExport($data->toArray()), $fileName);
    }
}
