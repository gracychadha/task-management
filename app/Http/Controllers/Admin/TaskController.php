<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Concerns\InteractsWithTaskNotifications;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Label;
use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\TaskWorkflowService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use InteractsWithTaskNotifications;

    public function __construct(private readonly TaskWorkflowService $workflow) {}

    public function index(Request $request)
    {
        $query = Task::with(['assignees', 'creator', 'reviewer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('assigned_to')) {
            $query->whereHas('assignees', fn ($q) => $q->where('users.id', $request->assigned_to));
        }

        if ($request->filled('reviewer_id')) {
            $query->where('reviewer_id', $request->reviewer_id);
        }

        if ($request->filled('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->whereBetween('created_at', [
                ($request->date_from ?: now()->startOfMonth())->format('Y-m-d 00:00:00'),
                ($request->date_to ?: now())->format('Y-m-d 23:59:59'),
            ]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        $view = $request->filled('view') ? $request->view : 'kanban';

        $tasks = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => Task::count(),
            'in_progress' => Task::where('status', TaskStatus::InProgress->value)->count(),
            'completed' => Task::where('status', TaskStatus::Completed->value)->count(),
            'overdue' => Task::overdue()->count(),
        ];

        $departments = Department::with('users')->where('is_active', true)->orderBy('name')->get();
        $users = User::where('role', '!=', 'admin')->orderBy('name')->get();

        $allTasks = (clone $query)->with(['assignees', 'labels', 'reviewer'])->get();

        $kanbanTasks = $allTasks->groupBy('status');

        $calendarTasks = $allTasks->filter(fn ($task) => $task->due_date || $task->start_date);

        $ganttTasks = $allTasks->filter(fn ($task) => $task->start_date);

        $labels = Label::withCount('tasks')->get();

        return view('admin.tasks.index', compact(
            'tasks',
            'departments',
            'users',
            'kanbanTasks',
            'calendarTasks',
            'ganttTasks',
            'view',
            'labels',
            'stats'
        ));
    }

    public function create()
    {
        $departments = Department::with(['users' => fn ($q) => $q->where('role', 'employee')->where('is_active', true)->orderBy('name')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $reviewers = User::where('role', 'employee')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'department_id']);
        $parentTasks = Task::whereNull('parent_id')->orderBy('title')->get();

        return view('admin.tasks.create', compact('departments', 'reviewers', 'parentTasks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules($request));

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'reviewer_id' => $validated['reviewer_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $assigneeIds = array_unique($validated['assigned_to'] ?? []);
        foreach ($assigneeIds as $assigneeId) {
            $task->assignees()->attach($assigneeId, ['assigned_at' => now()]);
            $this->notifyAssignment($task, $assigneeId);
        }

        if ($task->department_id === null && $assigneeIds !== []) {
            $task->update(['department_id' => $task->assignees()->first()?->department_id]);
        }

        $this->notifyManagers($task, 'task_assignment', 'New task assigned to your team',
            "New task {$task->code()} ({$task->title}) was created by {$request->user()->name} and assigned to your team.");

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties(['status' => $task->status])
            ->log("created task '{$task->title}'");

        return redirect()->route('admin.tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load([
            'assignees',
            'creator',
            'reviewer',
            'reviews' => fn ($q) => $q->with('reviewer')->latest('review_number'),
            'comments.user',
            'attachments.user',
            'submissionAttachment.user',
            'labels',
            'parent',
            'subtasks' => function ($q) {
                $q->with('assignees');
            },
        ]);

        $labelOptions = Label::all();
        $reviewerCandidates = User::where('role', 'employee')->where('is_active', true)->orderBy('name')->get();

        return view('admin.tasks.show', compact('task', 'labelOptions', 'reviewerCandidates'));
    }

    public function edit(Task $task)
    {
        $departments = Department::with('users')->where('is_active', true)->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $reviewers = User::where('role', 'employee')->where('is_active', true)->orderBy('name')->get();
        $parentTasks = Task::whereNull('parent_id')
            ->where('id', '!=', $task->id)
            ->orderBy('title')
            ->get();

        return view('admin.tasks.edit', compact('task', 'departments', 'users', 'reviewers', 'parentTasks'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate($this->rules($request));

        $oldAssigneeIds = $task->assignees()->pluck('users.id')->toArray();

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'reviewer_id' => $validated['reviewer_id'] ?? null,
        ]);

        $newAssigneeIds = array_unique($validated['assigned_to'] ?? []);
        $task->assignees()->sync($newAssigneeIds);

        if ($task->department_id === null && $newAssigneeIds !== []) {
            $task->update(['department_id' => $task->assignees()->first()?->department_id]);
        }

        foreach (array_diff($newAssigneeIds, $oldAssigneeIds) as $newId) {
            $this->notifyAssignment($task, $newId);
        }

        $this->notifyManagers($task, 'task_assignment', 'Team task updated',
            "Task {$task->code()} was updated by {$request->user()->name}.");

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties(['changes' => $request->only(['status', 'priority'])])
            ->log("updated task '{$task->title}'");

        return redirect()->route('admin.tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate(['status' => 'required|in:'.implode(',', TaskStatus::values())]);

        $this->workflow->assertCanStatusTransition($task, auth()->user(), $validated['status']);

        $old = $task->status;
        $task->update(['status' => $validated['status']]);

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties(['old' => $old, 'new' => $validated['status']])
            ->log("changed task status to {$validated['status']}");

        foreach ($task->assignees as $assignee) {
            if ($assignee->id == auth()->id()) {
                continue;
            }
            UserNotification::create([
                'user_id' => $assignee->id,
                'type' => 'task_status',
                'title' => 'Task status changed',
                'message' => "Task '{$task->title}' status changed to {$task->getStatusLabel()}.",
                'data' => ['task_id' => $task->id],
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function assign(Request $request, Task $task)
    {
        $validated = $request->validate([
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:users,id',
        ]);

        $task->assignees()->sync(array_unique($validated['assigned_to'] ?? []));

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->log("assigned task '{$task->title}'");

        return back()->with('success', 'Task assigned successfully.');
    }

    public function move(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', [
                TaskStatus::New->value,
                TaskStatus::InProgress->value,
                TaskStatus::UnderReview->value,
                TaskStatus::OnHold->value,
                TaskStatus::Cancelled->value,
            ]),
        ]);

        $old = $task->status;
        $task->update([
            'status' => $validated['status'],
            'sort_order' => $request->input('sort_order', $task->sort_order),
        ]);

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties(['old' => $old, 'new' => $validated['status']])
            ->log("moved task '{$task->title}' to {$validated['status']}");

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['tasks'] as $item) {
            Task::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Task $task)
    {
        $title = $task->title;
        $task->delete();

        activity()
            ->causedBy(auth()->user())
            ->log("deleted task '{$title}'");

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    private function rules(Request $request): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:'.implode(',', TaskStatus::values()),
            'priority' => 'required|in:'.implode(',', TaskPriority::values()),
            'department_id' => 'nullable|exists:departments,id',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:users,id',
            'reviewer_id' => ['nullable', 'exists:users,id', 'not_in:'.implode(',', $request->input('assigned_to', []))],
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'parent_id' => 'nullable|exists:tasks,id',
            'estimated_hours' => 'nullable|numeric|min:0|max:1000',
        ];
    }
}
