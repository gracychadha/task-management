<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use App\Services\ReportMetricsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private readonly ReportMetricsService $metrics) {}

    public function index(Request $request)
    {
        $type = $request->filled('type') ? $request->type : 'overview';
        [$dateFrom, $dateTo] = $this->metrics->dateRange($request->date_from, $request->date_to);

        $taskQuery = Task::with(['assignees', 'creator', 'reviewer', 'comments', 'attachments', 'reviews', 'submissionAttachment'])
            ->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59']);

        if ($request->filled('department_id')) {
            $taskQuery->where(function ($q) use ($request) {
                $q->where('department_id', $request->department_id)
                    ->orWhereHas('assignees', fn ($q2) => $q2->where('users.department_id', $request->department_id));
            });
        }

        if ($request->filled('status')) {
            $taskQuery->where('status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $taskQuery->whereHas('assignees', fn ($q) => $q->where('users.id', $request->employee_id));
        }

        if ($request->filled('reviewer_id')) {
            $taskQuery->where('reviewer_id', $request->reviewer_id);
        }

        $tasks = $taskQuery->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $employees = User::where('role', '!=', 'admin')->orderBy('name')->get();

        $data = ['type' => $type, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'departments' => $departments, 'employees' => $employees];

        $employeeId = $request->integer('employee_id');
        $employee = $employeeId ? User::find($employeeId) : null;

        $data['summary'] = [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'pending' => $tasks->where('status', 'new')->count(),
            'under_review' => $tasks->where('status', 'under_review')->count(),
            'changes_requested' => $tasks->where('status', 'changes_requested')->count(),
            'overdue' => $tasks->filter(fn ($t) => $t->isOverdue())->count(),
        ];
        $data['completionRate'] = $data['summary']['total'] > 0
            ? round(($data['summary']['completed'] / $data['summary']['total']) * 100, 1)
            : 0;

        if ($type === 'overview') {
            $data['departmentReports'] = $data['departments']
                ->map(fn ($dept) => $this->metrics->departmentPerformance($dept, $dateFrom, $dateTo));
            $data['reportTasks'] = $tasks;
        } elseif ($type === 'employee' && $employee) {
            $data['performance'] = $this->metrics->employeePerformance($employee, $dateFrom, $dateTo);
            $data['taskRows'] = $this->metrics->employeeTaskRows($data['performance']['tasks']);
        } elseif ($type === 'department') {
            $data['departmentReports'] = $departments->map(
                fn ($dept) => $this->metrics->departmentPerformance($dept, $dateFrom, $dateTo)
            );
        } elseif ($type === 'review') {
            $data['reviews'] = $this->metrics->reviewReport($tasks);
        } elseif ($type === 'overdue') {
            $data['overdueTasks'] = $tasks->filter(fn ($t) => $t->isOverdue())->sortBy('due_date');
        } elseif ($type === 'updates') {
            $data['updates'] = $this->metrics->updateReport($tasks);
        } elseif ($type === 'turnaround') {
            $data['turnaround'] = $this->metrics->turnaroundReport($tasks);
        }

        $data['statusBreakdown'] = [
            'labels' => ['new', 'in_progress', 'under_review', 'changes_requested', 'completed', 'on_hold', 'cancelled'],
            'data' => collect(['new', 'in_progress', 'under_review', 'changes_requested', 'completed', 'on_hold', 'cancelled'])
                ->map(fn ($s) => $tasks->where('status', $s)->count()),
        ];

        return view('admin.reports.index', $data);
    }

    public function export(Request $request)
    {
        $type = $request->filled('type') ? $request->type : 'overview';
        [$dateFrom, $dateTo] = $this->metrics->dateRange($request->date_from, $request->date_to);

        $taskQuery = Task::with(['assignees', 'creator', 'reviewer', 'comments', 'attachments', 'reviews'])
            ->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59']);

        if ($request->filled('department_id')) {
            $taskQuery->where(function ($q) use ($request) {
                $q->where('department_id', $request->department_id)
                    ->orWhereHas('assignees', fn ($q2) => $q2->where('users.department_id', $request->department_id));
            });
        }

        $tasks = $taskQuery->get();

        $data = match ($type) {
            'employee' => $this->employeeExport($tasks, $request->integer('employee_id'), $request->input('employee_name', 'Employee')),
            'department' => collect($this->departmentExport($tasks, $request->input('department_name', 'Department')))->toArray(),
            'review' => $this->metrics->reviewReport($tasks)
                ->map(fn ($r) => [
                    'Review ID' => $r['id'],
                    'Task ID' => $r['task_id'],
                    'Task' => $r['task']->title,
                    'Reviewer' => $r['reviewer']?->name,
                    'Review #' => $r['review_number'],
                    'Result' => $r['decision'],
                    'Comment' => $r['comment'],
                    'Reviewed At' => $r['reviewed_at'],
                ])->values()->all(),
            'overdue' => $tasks->filter(fn ($t) => $t->isOverdue())->map(fn ($t) => [
                'Task ID' => $t->id,
                'Title' => $t->title,
                'Assignee' => $t->assignees->pluck('name')->implode(', '),
                'Priority' => $t->getPriorityLabel(),
                'Due Date' => $t->due_date?->format('Y-m-d'),
                'Status' => $t->getStatusLabel(),
            ])->values()->all(),
            'updates' => $this->metrics->updateReport($tasks)->map(fn ($r) => [
                'Task ID' => $r['task']->id,
                'Title' => $r['title'],
                'Assignee' => $r['assignee'],
                'Status' => $r['status'],
                'Updates' => $r['updates'],
                'Review Cycles' => $r['review_cycles'],
                'Changes Requested' => $r['changes_requested'],
                'Resubmissions' => $r['resubmissions'],
                'Comments' => $r['comments'],
                'Attachments' => $r['attachments'],
            ])->values()->all(),
            'turnaround' => $this->metrics->turnaroundReport($tasks)->map(fn ($r) => [
                'Task ID' => $r['task']->id,
                'Title' => $r['title'],
                'Assignee' => $r['assignee'],
                'Created At' => $r['created_at'],
                'Start Date' => $r['start_date'],
                'Completed At' => $r['completed_at'],
                'Completion Days' => $r['days'],
            ])->values()->all(),
            default => $tasks->map(fn ($task) => [
                'Task ID' => $task->id,
                'Title' => $task->title,
                'Status' => $task->getStatusLabel(),
                'Priority' => $task->getPriorityLabel(),
                'Assignee' => $task->assignees->pluck('name')->implode(', ') ?: 'Unassigned',
                'Department' => $task->department?->name,
                'Due Date' => $task->due_date?->format('Y-m-d'),
                'Created At' => $task->created_at?->format('Y-m-d'),
                'Completed' => $task->isCompleted() ? 'Yes' : 'No',
            ])->all(),
        };

        $fileName = "reports-{$type}-".now()->format('Y-m-d-Hi').'.xlsx';

        return Excel::download(new ArrayExport($data), $fileName);
    }

    public function exportPdf(Request $request)
    {
        [$dateFrom, $dateTo] = $this->metrics->dateRange($request->date_from, $request->date_to);

        $tasks = Task::with(['assignees'])
            ->whereBetween('created_at', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
            ->get();

        $summary = [
            'total' => $tasks->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'pending' => $tasks->where('status', 'new')->count(),
        ];

        $pdf = Pdf::loadView('exports.report-pdf', compact('tasks', 'summary', 'dateFrom', 'dateTo'));

        return $pdf->download('tasks-report-'.now()->format('Y-m-d-Hi').'.pdf');
    }

    private function employeeExport($tasks, int $employeeId, string $employeeName): array
    {
        if ($employeeId) {
            $tasks = $tasks->filter(fn ($t) => $t->assignees->contains('id', $employeeId));
        }

        return $tasks->map(fn ($t) => [
            'Employee' => $employeeName,
            'Task ID' => $t->id,
            'Title' => $t->title,
            'Status' => $t->getStatusLabel(),
            'Updates' => $t->updates_count,
            'Review Cycles' => $t->reviews->count(),
            'Changes Requested' => $t->reviews->where('decision', 'changes_requested')->count(),
            'Resubmissions' => $t->resubmissions(),
            'Completed At' => $t->completed_at?->format('Y-m-d'),
        ])->values()->all();
    }

    private function departmentExport($tasks, string $departmentName): array
    {
        $completed = $tasks->where('status', 'completed')->count();
        $total = $tasks->count();

        return [[
            'Department' => $departmentName,
            'Total Tasks' => $total,
            'Completed' => $completed,
            'Pending' => $tasks->where('status', 'new')->count(),
            'In Progress' => $tasks->where('status', 'in_progress')->count(),
            'Under Review' => $tasks->where('status', 'under_review')->count(),
            'Changes Requested' => $tasks->where('status', 'changes_requested')->count(),
            'Overdue' => $tasks->filter(fn ($t) => $t->isOverdue())->count(),
            'Completion %' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        ]];
    }
}
