<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateMemberRequest;

class MemberController extends Controller
{
    public function __construct(private UserRepository $userRepository) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $user = Auth::user();
        $organizationID = $user->organization_id;
        $members = $this->userRepository->getUsersByOrganizationID($organizationID, $validated['per_page'] ?? 15);

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
