<?php

namespace App\Http\Controllers\Manager;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class TeamTaskController extends Controller
{
    public function scopeTasks()
    {
        $user = auth()->user();

        return Task::with(['assignees'])
            ->where(function ($q) use ($user) {
                $q->whereHas('assignees', function ($q2) use ($user) {
                    $q2->where('users.id', $user->id);
                    if ($user->department_id) {
                        $q2->orWhere('users.department_id', $user->department_id);
                    }
                })->orWhere('created_by', $user->id);
            });
    }

    public function index(Request $request)
    {
        $tasks = $this->scopeTasks()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->when($request->filled('assigned_to'), fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('users.id', $request->assigned_to)))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('title', 'like', "%{$request->search}%")
                        ->orWhere('description', 'like', "%{$request->search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $members = $this->getDepartmentMembers();

        return view('manager.team-tasks.index', compact('tasks', 'members'));
    }

    public function board(Request $request)
    {
        $tasks = $this->scopeTasks()
            ->get()
            ->groupBy('status');

        $members = $this->getDepartmentMembers();

        return view('manager.team-tasks.board', compact('tasks', 'members'));
    }

    public function calendar()
    {
        $tasks = $this->scopeTasks()
            ->whereNotNull('due_date')
            ->get();

        return view('manager.team-tasks.calendar', compact('tasks'));
    }

    public function gantt()
    {
        $tasks = $this->scopeTasks()
            ->whereNotNull('start_date')
            ->get();

        return view('manager.team-tasks.gantt', compact('tasks'));
    }

    public function create()
    {
        $members = $this->getDepartmentMembers();

        return view('manager.team-tasks.create', compact('members'));
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
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach (array_unique($validated['assigned_to'] ?? []) as $assigneeId) {
            $task->assignees()->attach($assigneeId, ['assigned_at' => now()]);
            if ($assigneeId !== auth()->id()) {
                UserNotification::create([
                    'user_id' => $assigneeId,
                    'type' => 'task_assignment',
                    'title' => 'New task assigned to you',
                    'message' => "Task '{$task->title}' has been assigned to you.",
                    'data' => ['task_id' => $task->id],
                ]);
            }
        }

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->log("created task '{$task->title}'");

        return redirect()->route('manager.team-tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $this->authorizeTask($task);

        $task->load(['assignees', 'creator', 'comments.user', 'attachments.user', 'labels', 'parent', 'subtasks']);

        return view('manager.team-tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorizeTask($task);

        $members = $this->getDepartmentMembers();

        return view('manager.team-tasks.edit', compact('task', 'members'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', TaskStatus::values()),
            'priority' => 'required|in:' . implode(',', TaskPriority::values()),
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:users,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
        ]);

        $oldAssigneeIds = $task->assignees()->pluck('users.id')->toArray();

        $task->update($validated);

        $newAssigneeIds = array_unique($validated['assigned_to'] ?? []);
        $task->assignees()->sync($newAssigneeIds);

        foreach (array_diff($newAssigneeIds, $oldAssigneeIds) as $newId) {
            if ($newId !== auth()->id()) {
                UserNotification::create([
                    'user_id' => $newId,
                    'type' => 'task_assignment',
                    'title' => 'New task assigned to you',
                    'message' => "Task '{$task->title}' has been assigned to you.",
                    'data' => ['task_id' => $task->id],
                ]);
            }
        }

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->log("updated task '{$task->title}'");

        return redirect()->route('manager.team-tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function move(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', TaskStatus::values()),
        ]);

        $old = $task->status;
        $task->update(['status' => $validated['status']]);

        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties(['old' => $old, 'new' => $validated['status']])
            ->log("moved task '{$task->title}'");

        return response()->json(['success' => true]);
    }

    public function destroy(Task $task)
    {
        $this->authorizeTask($task);

        $title = $task->title;
        $task->delete();

        activity()
            ->causedBy(auth()->user())
            ->log("deleted task '{$title}'");

        return redirect()->route('manager.team-tasks')
            ->with('success', 'Task deleted successfully.');
    }

    private function authorizeTask(Task $task): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isManager()) {
            $access = $task->created_by === $user->id
                || $task->assignees()->where('users.id', $user->id)->exists()
                || ($user->department_id && $task->assignees()->where('users.department_id', $user->department_id)->exists());

            if ($access) {
                return;
            }
        }

        abort(403, 'You do not have access to this task.');
    }

    private function getDepartmentMembers()
    {
        $user = auth()->user();

        return User::where('role', 'employee')
            ->where('is_active', true)
            ->when($user->department_id, fn ($q) => $q->where('department_id', $user->department_id))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'department_id']);
    }
}
