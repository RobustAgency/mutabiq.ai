<?php

namespace App\Listeners;

use App\Mail\TeamInvitationMail;
use App\Events\TeamInvitationSent;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInvitationEmail implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(TeamInvitationSent $event): void
    {
        $email = $event->invitation->email;
        Mail::to($email)->send(new TeamInvitationMail($event->invitation));
    }
}
