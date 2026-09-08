<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Task;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->date_from ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?: now()->format('Y-m-d');

        $taskQuery = Task::with(['assignees', 'creator'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($request->filled('department_id')) {
            $taskQuery->whereHas('assignees', function ($q) use ($request) {
                $q->where('users.department_id', $request->department_id);
            });
        }

        if ($request->filled('status')) {
            $taskQuery->where('status', $request->status);
        }

        $tasks = $taskQuery->get();
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', TaskStatus::Done->value)->count();
        $inProgressTasks = $tasks->where('status', TaskStatus::InProgress->value)->count();
        $pendingTasks = $tasks->where('status', TaskStatus::Todo->value)->count();
        $overdueTasks = $tasks->filter(fn ($t) => $t->isOverdue())->count();

        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        // Tasks by status chart
        $statusBreakdown = [
            'labels' => collect(TaskStatus::cases())->map->label()->values(),
            'data' => collect(TaskStatus::cases())->map(function ($status) use ($tasks) {
                return $tasks->where('status', $status->value)->count();
            }),
        ];

                // Tasks by department
        $deptBreakdown = $tasks->flatMap(function ($task) {
            return $task->assignees->map(fn ($assignee) => [
                'department' => $assignee->department?->name ?? 'Unassigned',
                'completed' => $task->status === TaskStatus::Done->value,
            ]);
        })->groupBy('department')->map(function ($rows, $name) {
            return [
                'name' => $name,
                'total' => $rows->count(),
                'completed' => $rows->where('completed', true)->count(),
            ];
        })->values();

        // Task trend
        $taskTrend = $tasks->groupBy(function ($task) {
            return $task->created_at->format('M d');
        })->map->count();

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.reports.index', compact(
            'tasks',
            'totalTasks',
            'completedTasks',
            'inProgressTasks',
            'pendingTasks',
            'overdueTasks',
            'completionRate',
            'statusBreakdown',
            'deptBreakdown',
            'taskTrend',
            'departments',
            'dateFrom',
            'dateTo'
        ));
    }

    public function export(Request $request)
    {
        $dateFrom = $request->date_from ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?: now()->format('Y-m-d');

        $taskQuery = Task::with(['assignees'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($request->filled('department_id')) {
            $taskQuery->whereHas('assignees', function ($q) use ($request) {
                $q->where('users.department_id', $request->department_id);
            });
        }

        $tasks = $taskQuery->get();

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

        $fileName = 'tasks-report-' . now()->format('Y-m-d-Hi') . '.xlsx';

        return Excel::download(new \App\Exports\ArrayExport($data->toArray()), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $dateFrom = $request->date_from ?: now()->startOfMonth()->format('Y-m-d');
        $dateTo = $request->date_to ?: now()->format('Y-m-d');

        $taskQuery = Task::with(['assignees'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($request->filled('department_id')) {
            $taskQuery->whereHas('assignees', function ($q) use ($request) {
                $q->where('users.department_id', $request->department_id);
            });
        }

        $tasks = $taskQuery->get();
        $summary = [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', TaskStatus::Done->value)->count(),
            'in_progress' => $tasks->where('status', TaskStatus::InProgress->value)->count(),
            'pending' => $tasks->where('status', TaskStatus::Todo->value)->count(),
        ];

        $pdf = Pdf::loadView('exports.report-pdf', compact('tasks', 'summary', 'dateFrom', 'dateTo'));

        return $pdf->download('tasks-report-' . now()->format('Y-m-d-Hi') . '.pdf');
    }
}