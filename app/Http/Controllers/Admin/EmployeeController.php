<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', '!=', UserRole::Admin->value);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhereHas('department', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query->with('department')
            ->withCount(['assignedTasks as tasks_count'])
            ->withCount(['assignedTasks as tasks_count_done' => function ($q) {
                $q->where('status', 'done');
            }])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.employees.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:manager,employee',
            'department_id' => 'nullable|exists:departments,id',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
            'position' => $validated['position'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->log("created user '{$user->name}'");

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function show(User $employee)
    {
        if ($employee->role === UserRole::Admin->value && $employee->id !== auth()->id()) {
            abort(403);
        }

        $employee->load([
            'assignedTasks' => function ($q) {
                $q->latest();
            },
        ]);

        $stats = [
            'total_tasks' => Task::whereHas('assignees', fn ($q) => $q->where('users.id', $employee->id))->count(),
            'completed_tasks' => Task::whereHas('assignees', fn ($q) => $q->where('users.id', $employee->id))->where('status', 'done')->count(),
            'in_progress_tasks' => Task::whereHas('assignees', fn ($q) => $q->where('users.id', $employee->id))->where('status', 'in_progress')->count(),
            'overdue_tasks' => Task::whereHas('assignees', fn ($q) => $q->where('users.id', $employee->id))->overdue()->count(),
        ];

        $completionRate = $stats['total_tasks'] > 0
            ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100, 1)
            : 0;

        $recentActivities = Activity::causedBy($employee)->latest()->limit(10)->get();

        return view('admin.employees.show', compact('employee', 'stats', 'completionRate', 'recentActivities'));
    }

    public function edit(User $employee)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('admin.employees.edit', compact('employee', 'departments'));
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $employee->id,
            'role' => 'required|in:admin,manager,employee',
            'department_id' => 'nullable|exists:departments,id',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
            'position' => $validated['position'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $employee->update($data);

        activity()
            ->performedOn($employee)
            ->causedBy(auth()->user())
            ->log("updated user '{$employee->name}'");

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function toggleActive(User $employee)
    {
        $employee->update(['is_active' => !$employee->is_active]);

        activity()
            ->performedOn($employee)
            ->causedBy(auth()->user())
            ->log(($employee->is_active ? 'activated' : 'deactivated') . " user '{$employee->name}'");

        return back()->with('success', 'Employee status updated successfully.');
    }

    public function destroy(User $employee)
    {
        if ($employee->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $name = $employee->name;
        $employee->delete();

        activity()
            ->causedBy(auth()->user())
            ->log("deleted user '{$name}'");

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}