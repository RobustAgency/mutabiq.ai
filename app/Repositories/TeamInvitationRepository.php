<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\TeamInvitation;
use App\Enums\InvitationStatus;

class TeamInvitationRepository
{
    public function getByToken(string $token): ?TeamInvitation
    {
        return TeamInvitation::where('token', $token)->first();
    }

    public function createInvite(User $user, array $members): TeamInvitation
    {
        $token = Str::random(16);

        return TeamInvitation::create([
            'organization_id' => $user->organization_id,
            'invited_by' => $user->id,
            'email' => $members['email'],
            'role' => $members['role'],
            'token' => $token,
            'expires_at' => Carbon::now()->addDays(7),
            'status' => InvitationStatus::PENDING,
        ]);
    }

    public function markAsAccepted(TeamInvitation $invitation): TeamInvitation
    {
        $invitation->update([
            'status' => InvitationStatus::ACCEPTED,
        ]);

        return $invitation;
    }
}
