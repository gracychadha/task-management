<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::withCount('users');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $departments = $query->latest()->paginate(12)->withQueryString();

        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Department::create($validated);

        activity()
            ->performedOn(Department::latest()->first())
            ->causedBy(auth()->user())
            ->log("created department '{$validated['name']}'");

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        $department->load('users');

        $stats = [
            'total_members' => $department->users()->count(),
            'active_members' => $department->users()->where('is_active', true)->count(),
            'managers' => $department->users()->where('role', 'manager')->count(),
            'employees' => $department->users()->where('role', 'employee')->count(),
        ];

        return view('admin.departments.show', compact('department', 'stats'));
    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $department->update($validated);

        activity()
            ->performedOn($department)
            ->causedBy(auth()->user())
            ->log("updated department '{$department->name}'");

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $name = $department->name;

        $department->users()->update(['department_id' => null]);
        $department->delete();

        activity()
            ->causedBy(auth()->user())
            ->log("deleted department '{$name}'");

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
