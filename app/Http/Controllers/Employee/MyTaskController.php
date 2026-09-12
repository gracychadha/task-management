<?php

namespace App\Http\Controllers\Employee;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class MyTaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Task::whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
            ->with(['assignees', 'creator', 'labels', 'reviewer'])
            ->latest();

        $query = $this->applyFilters($query, $request);

        $stats = [
            'total' => (clone $query)->count(),
            'in_progress' => (clone $query)->where('status', TaskStatus::InProgress->value)->count(),
            'completed' => (clone $query)->where('status', TaskStatus::Completed->value)->count(),
            'overdue' => (clone $query)->overdue()->count(),
        ];

        $tasks = $query->paginate(15)->withQueryString();

        return view('employee.my-tasks.index', compact('tasks', 'stats'));
    }

    public function reviewTasks(Request $request)
    {
        $user = auth()->user();

        $query = Task::where('reviewer_id', $user->id)
            ->with(['assignees', 'creator', 'labels', 'reviewer'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'awaiting_review' => (clone $query)->where('status', TaskStatus::UnderReview->value)->count(),
            'changes_requested' => (clone $query)->where('status', TaskStatus::ChangesRequested->value)->count(),
            'completed' => (clone $query)->where('status', TaskStatus::Completed->value)->count(),
        ];

        $tasks = $query->paginate(15)->withQueryString();

        return view('employee.review-tasks.index', compact('tasks', 'stats'));
    }

    public function show(Task $task)
    {
        $user = auth()->user();

        if ($task->assignees->doesntContain('id', $user->id)
            && $task->created_by !== $user->id
            && $task->reviewer_id !== $user->id) {
            abort(403);
        }

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

        return view('employee.my-tasks.show', compact('task'));
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('due_date')) {
            $query->whereDate('due_date', $request->due_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        return $query;
    }
}
