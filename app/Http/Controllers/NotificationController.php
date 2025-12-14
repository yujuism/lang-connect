<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Show all notifications
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notifications = $user
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Mark all as read
        $user->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('notifications.index', compact('notifications'));
    }

    // Mark notification as read
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    // Get unread count (for AJAX)
    public function getUnreadCount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return response()->json([
            'count' => $user->getUnreadNotificationCount(),
        ]);
    }

    // Fetch recent notifications (for dropdown)
    public function fetch()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $notifications = $user
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->getUnreadNotificationCount(),
        ]);
    }
}
