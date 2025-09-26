<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Get notifications for current user
    public function index()
    {
        $user = Auth::user();
        
        $notifications = Notification::where('user_id', $user->user_id)
            ->with('fromUser')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Mark as read when viewed
        Notification::where('user_id', $user->user_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('notifications.index', compact('notifications'));
    }

    // Get unread count for badge
    public function getUnreadCount()
    {
        $user = Auth::user();
        $count = Notification::where('user_id', $user->user_id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    // Get recent notifications for dropdown
    public function getRecent()
    {
        $user = Auth::user();
        
        $notifications = Notification::where('user_id', $user->user_id)
            ->with('fromUser')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->notification_id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'from_user' => $notification->fromUser ? ($notification->fromUser->full_name ?? $notification->fromUser->username) : 'System',
                    'created_at' => $notification->created_at->diffForHumans(),
                    'is_read' => $notification->is_read,
                    'type' => $notification->type,
                    'url' => $this->getNotificationUrl($notification)
                ];
            });

        return response()->json($notifications);
    }

    // Mark notification as read
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        Notification::where('notification_id', $id)
            ->where('user_id', $user->user_id)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    // Mark all notifications as read
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        Notification::where('user_id', $user->user_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    // Delete notification
    public function destroy($id)
    {
        $user = Auth::user();
        
        Notification::where('notification_id', $id)
            ->where('user_id', $user->user_id)
            ->delete();

        return response()->json(['success' => true]);
    }

    // Helper method to get URL based on notification type
    private function getNotificationUrl($notification)
    {
        switch ($notification->type) {
            case 'task_assigned':
            case 'task_completed':
                return route('tasks.show', $notification->related_id);
            case 'help_request':
                return route('leader.help-requests');
            case 'help_response':
                return route('tasks.show', $notification->related_id);
            default:
                return '#';
        }
    }
}