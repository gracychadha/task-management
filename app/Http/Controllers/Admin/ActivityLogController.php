<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%");
        }

        if ($request->filled('user')) {
            $query->where('causer_id', $request->user);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $activities = $query->latest()->paginate(15)->withQueryString();
        $users = User::where('role', '!=', 'admin')->orderBy('name')->get();

        $statsQuery = Activity::query();
        if ($request->filled('search')) {
            $statsQuery->where('description', 'like', "%{$request->search}%");
        }
        if ($request->filled('user')) {
            $statsQuery->where('causer_id', $request->user);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'today' => (clone $statsQuery)->whereDate('created_at', now())->count(),
            'active_users' => Activity::where('created_at', '>=', now()->subDays(7))->distinct('causer_id')->count('causer_id'),
        ];

        return view('admin.activity-logs.index', compact('activities', 'users', 'stats'));
    }
}
