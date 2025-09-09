<?php

namespace App\Listeners;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Admin\AdminAccountCreatedNotification;

class SendAdminWelcomeEmail implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;
        if ($user->role === UserRole::ADMIN) {
            $user->notify(new AdminAccountCreatedNotification($user));
        }
    }
}
