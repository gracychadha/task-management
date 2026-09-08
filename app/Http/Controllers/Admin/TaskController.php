<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Label;
use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['assignees', 'creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('assignees', fn ($q) => $q->where('users.department_id', $request->department_id));
        }

        if ($request->filled('assigned_to')) {
            $query->whereHas('assignees', fn ($q) => $q->where('users.id', $request->assigned_to));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $view = $request->filled('view') ? $request->view : 'kanban';

        $tasks = $query->latest()->paginate(20)->withQueryString();

        $departments = Department::with('users')->where('is_active', true)->orderBy('name')->get();
        $users = User::where('role', '!=', 'admin')->orderBy('name')->get();

        $allTasks = (clone $query)->with(['assignees', 'labels'])->get();

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
            'labels'
        ));
    }

    public function create()
    {
        $departments = Department::with('users')->where('is_active', true)->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $parentTasks = Task::whereNull('parent_id')->orderBy('title')->get();

        return view('admin.tasks.create', compact('departments', 'users', 'parentTasks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', TaskStatus::values()),
            'priority' => 'required|in:' . implode(',', TaskPriority::values()),
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:users,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'estimated_hours' => 'nullable|numeric|min:0|max:1000',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $assigneeIds = array_unique($validated['assigned_to'] ?? []);
        foreach ($assigneeIds as $assigneeId) {
            $task->assignees()->attach($assigneeId, ['assigned_at' => now()]);
            $this->notifyAssignment($task, $assigneeId);
        }

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
            'comments.user',
            'attachments.user',
            'labels',
            'parent',
            'subtasks' => function ($q) {
                $q->with('assignees');
            },
        ]);

        $labelOptions = Label::all();

        return view('admin.tasks.show', compact('task', 'labelOptions'));
    }

    public function edit(Task $task)
    {
        $departments = Department::with('users')->where('is_active', true)->orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $parentTasks = Task::whereNull('parent_id')
            ->where('id', '!=', $task->id)
            ->orderBy('title')
            ->get();

        return view('admin.tasks.edit', compact('task', 'departments', 'users', 'parentTasks'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', TaskStatus::values()),
            'priority' => 'required|in:' . implode(',', TaskPriority::values()),
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:users,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'estimated_hours' => 'nullable|numeric|min:0|max:1000',
        ]);

        $oldAssigneeIds = $task->assignees()->pluck('users.id')->toArray();

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
        ]);

        $newAssigneeIds = array_unique($validated['assigned_to'] ?? []);
        $task->assignees()->sync($newAssigneeIds);

        foreach (array_diff($newAssigneeIds, $oldAssigneeIds) as $newId) {
            $this->notifyAssignment($task, $newId);
        }

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
        $validated = $request->validate(['status' => 'required|in:' . implode(',', TaskStatus::values())]);

        $old = $task->status;
        $task->update(['status' => $validated['status']]);

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties(['old' => $old, 'new' => $validated['status']])
            ->log("changed task status to {$validated['status']}");

        foreach ($task->assignees as $assignee) {
            if ($assignee->id != auth()->id()) {
                UserNotification::create([
                    'user_id' => $assignee->id,
                    'type' => 'task_status',
                    'title' => 'Task status changed',
                    'message' => "Task '{$task->title}' status changed to {$task->getStatusLabel()}.",
                    'data' => ['task_id' => $task->id],
                ]);
            }
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
            'status' => 'required|in:' . implode(',', TaskStatus::values()),
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

    private function notifyAssignment(Task $task, int $userId): void
    {
        if ($userId === auth()->id()) {
            return;
        }

        UserNotification::create([
            'user_id' => $userId,
            'type' => 'task_assignment',
            'title' => 'New task assigned to you',
            'message' => "Task '{$task->title}' has been assigned to you.",
            'data' => ['task_id' => $task->id],
        ]);
    }
}
