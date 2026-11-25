<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

/** The admin's bell: list, open (marks as read) and "mark all as read". */
class NotificationController extends Controller
{
    /** What the bell shows (also used for the first render of the page). */
    public static function payload(User $user): array
    {
        return [
            'unread' => $user->unreadNotifications()->count(),
            'items' => $user->notifications()->latest()->take(10)->get()->map(fn ($n) => [
                'id' => $n->id,
                'kind' => $n->data['kind'] ?? 'info',
                'title' => $n->data['title'] ?? 'Notification',
                'message' => $n->data['message'] ?? '',
                'url' => route('notifications.open', $n->id),
                'read' => $n->read_at !== null,
                'time' => $n->created_at->diffForHumans(),
            ])->all(),
        ];
    }

    /** Polled by the bell so new events show up without reloading. */
    public function feed(): JsonResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return response()->json(self::payload(auth()->user()));
    }

    public function open(string $id)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('admin.dashboard'));
    }

    public function readAll()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }
}
