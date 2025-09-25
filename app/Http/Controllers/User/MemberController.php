<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function __construct(private UserRepository $userRepository) {}

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $organizationID = $user->organization_id;
        $members = $this->userRepository->getUsersByOrganizationID($organizationID);

        return response()->json([
            'error' => false,
            'message' => 'Members retrieved successfully',
            'data' => $members,
        ]);
    }

    public function update(UpdateMemberRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();
        $updatedMember = $this->userRepository->updateUser($user, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Member updated successfully',
            'data' => $updatedMember,
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userRepository->deleteUser($user);

        return response()->json([
            'error' => false,
            'message' => 'Member deleted successfully',
            'data' => null,
        ]);
    }
}
