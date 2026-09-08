<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $users = \App\Models\User::where('role', '!=', 'admin')->orderBy('name')->get();

        return view('admin.activity-logs.index', compact('activities', 'users'));
    }
}