<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class MyTaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->with(['assignees', 'creator', 'labels']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->latest()->paginate(15)->withQueryString();

        return view('employee.my-tasks.index', compact('tasks'));
    }

    public function show(Task $task)
    {
        $user = auth()->user();

        if ($task->assignees->doesntContain('id', $user->id) && $task->created_by !== $user->id) {
            abort(403);
        }

        $task->load(['assignees', 'creator', 'comments.user', 'attachments.user', 'labels', 'parent', 'subtasks']);

        return view('employee.my-tasks.show', compact('task'));
    }
}
