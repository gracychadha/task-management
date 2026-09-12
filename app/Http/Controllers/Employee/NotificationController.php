<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $query = UserNotification::where('user_id', auth()->id());

        $total = $query->count();
        $unread = (clone $query)->unread()->count();

        $notifications = $query->latest()->paginate(20);

        return view('employee.notifications.index', compact('notifications', 'total', 'unread'));
    }

    public function markAsRead(UserNotification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        UserNotification::where('user_id', auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
