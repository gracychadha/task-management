<?php

namespace App\Http\Controllers\Manager;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Concerns\InteractsWithTaskNotifications;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class TeamTaskController extends Controller
{
    use InteractsWithTaskNotifications;

    public function scopeTasks()
    {
        $user = auth()->user();

        return Task::with(['assignees', 'reviewer'])
            ->where(function ($q) use ($user) {
                $q->whereHas('assignees', function ($q2) use ($user) {
                    $q2->where('users.id', $user->id);
                    if ($user->department_id) {
                        $q2->orWhere('users.department_id', $user->department_id);
                    }
                })->orWhere('created_by', $user->id)
                    ->when($user->department_id, fn ($q3) => $q3->orWhere('department_id', $user->department_id));
            });
    }

    public function index(Request $request)
    {
        $base = $this->scopeTasks();

        $stats = [
            'total' => (clone $base)->count(),
            'in_progress' => (clone $base)->where('status', TaskStatus::InProgress->value)->count(),
            'completed' => (clone $base)->where('status', TaskStatus::Completed->value)->count(),
            'overdue' => (clone $base)->overdue()->count(),
        ];

        $tasks = (clone $base)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->when($request->filled('assigned_to'), fn ($q) => $q->whereHas('assignees', fn ($q2) => $q2->where('users.id', $request->assigned_to)))
            ->when($request->filled('reviewer_id'), fn ($q) => $q->where('reviewer_id', $request->reviewer_id))
            ->when($request->filled('due_date'), fn ($q) => $q->whereDate('due_date', $request->due_date))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('title', 'like', "%{$request->search}%")
                        ->orWhere('description', 'like', "%{$request->search}%")
                        ->orWhere('id', $request->search);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $members = $this->getDepartmentMembers();

        return view('manager.team-tasks.index', compact('tasks', 'members', 'stats'));
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
        $departments = Department::with(['users' => fn ($q) => $q->where('role', 'employee')->where('is_active', true)->orderBy('name')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $reviewers = User::where('role', 'employee')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'department_id']);

        return view('manager.team-tasks.create', compact('departments', 'reviewers'));
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
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'reviewer_id' => $validated['reviewer_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach (array_unique($validated['assigned_to'] ?? []) as $assigneeId) {
            $task->assignees()->attach($assigneeId, ['assigned_at' => now()]);
            $this->notifyAssignment($task, $assigneeId);
        }

        if ($task->department_id === null) {
            $task->update(['department_id' => auth()->user()->department_id ?? $task->assignees()->first()?->department_id]);
        }

        $this->notifyManagers($task, 'task_assignment', 'New task assigned to your team',
            "New task {$task->code()} ({$task->title}) was created by {$request->user()->name} and assigned to your team.");

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
            'subtasks',
        ]);

        $reviewerCandidates = $this->getDepartmentMembers();
        $activities = Activity::forSubject($task)
            ->with('causer')
            ->latest()
            ->take(15)
            ->get();

        return view('manager.team-tasks.show', compact('task', 'reviewerCandidates', 'activities'));
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

        $validated = $request->validate($this->rules($request) + [
            'actual_hours' => 'nullable|numeric|min:0',
        ]);

        $oldAssigneeIds = $task->assignees()->pluck('users.id')->toArray();

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'actual_hours' => $validated['actual_hours'] ?? null,
            'reviewer_id' => $validated['reviewer_id'] ?? null,
        ]);

        $newAssigneeIds = array_unique($validated['assigned_to'] ?? []);
        $task->assignees()->sync($newAssigneeIds);

        foreach (array_diff($newAssigneeIds, $oldAssigneeIds) as $newId) {
            $this->notifyAssignment($task, $newId);
        }

        $this->notifyManagers($task, 'task_assignment', 'Team task updated',
            "Task {$task->code()} was updated by {$request->user()->name}.");

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
            'status' => 'required|in:'.implode(',', [
                TaskStatus::New->value,
                TaskStatus::InProgress->value,
                TaskStatus::UnderReview->value,
                TaskStatus::OnHold->value,
                TaskStatus::Cancelled->value,
            ]),
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
            'estimated_hours' => 'nullable|numeric|min:0',
        ];
    }
}
