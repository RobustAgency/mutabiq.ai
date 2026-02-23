<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Clients\SupabaseClient;
use App\Enums\InvitationStatus;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreTeamInviteRequest;
use App\Http\Requests\AcceptInvitationRequest;
use App\Repositories\TeamInvitationRepository;

class TeamInvitationController extends Controller
{
    public function __construct(protected TeamInvitationRepository $teamInvitationRepository) {}

    public function inviteMembers(StoreTeamInviteRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();

        $members = $validated['members'];

        foreach ($members as $member) {
            $this->teamInvitationRepository->createInvite($user, $member);
        }

        return response()->json([
            'error' => false,
            'message' => 'Invitations sent successfully',
            'data' => null,
        ], 200);
    }

    public function acceptInvitation(AcceptInvitationRequest $request, SupabaseClient $supabaseClient): JsonResponse
    {
        $data = $request->validated();
        $invite = $this->teamInvitationRepository->getByToken($data['token']);

        if (! $invite || $invite->status !== InvitationStatus::PENDING) {
            return response()->json([
                'error' => true,
                'message' => 'Invalid or expired invitation.',
            ], 400);
        }

        $data['email'] = $invite->email;
        $data['role'] = $invite->role->name;
        $data['organization_id'] = $invite->organization_id;

        $supabaseResponse = $supabaseClient->createUser($data);
        $data['supabase_id'] = $supabaseResponse['id'];

        $user = User::registerUser($data);
        $this->teamInvitationRepository->markAsAccepted($invite);

        return response()->json([
            'error' => false,
            'message' => 'Invitation accepted successfully',
            'data' => new UserResource($user),
        ], 201);
    }
}
