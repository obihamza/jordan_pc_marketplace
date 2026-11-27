<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class AdminEvent extends Notification
{
    public function __construct(
        public string $kind,
        public string $title,
        public string $message,
        public string $url,
        public array $extra = [],
    ) {
    }

    public static function send(string $kind, string $title, string $message, string $url, array $extra = []): void
    {
        $admins = User::where('is_admin', true)->get();

        NotificationFacade::send($admins, new static($kind, $title, $message, $url, $extra));
    }

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
