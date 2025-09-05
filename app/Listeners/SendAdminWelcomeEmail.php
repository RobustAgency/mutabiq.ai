<?php

namespace App\Listeners;

use App\Models\User;
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
        $user->notify(new AdminAccountCreatedNotification($user));
    }
}
