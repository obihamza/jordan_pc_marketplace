<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Something an admin or seller should know about (seller application, new order, new user, approval...).
 * Stored in the `notifications` table and shown in the bell of the dashboard top bar.
 */
class AdminEvent extends Notification
{
    public function __construct(
        public string $kind,      // seller_application | order | user
        public string $title,
        public string $message,
        public string $url,       // relative path the bell item opens
        public array $extra = [],
    ) {
    }

    /** Tell every admin. */
    public static function send(string $kind, string $title, string $message, string $url, array $extra = []): void
    {
        $admins = User::where('is_admin', true)->get();

        NotificationFacade::send($admins, new static($kind, $title, $message, $url, $extra));
    }

    /** Tell one specific user (for example a seller about a new order). */
    public static function sendTo(User $user, string $kind, string $title, string $message, string $url, array $extra = []): void
    {
        NotificationFacade::send($user, new static($kind, $title, $message, $url, $extra));
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'kind' => $this->kind,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ], $this->extra);
    }
}
