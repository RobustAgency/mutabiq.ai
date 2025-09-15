<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Repositories\TagRepository;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\SearchTagsRequest;

class TagController extends Controller
{
    public function __construct(private TagRepository $tagRepository) {}

    public function index(SearchTagsRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();
        $tags = $this->tagRepository->getFilteredTagsForAdmin($user, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Tags retrieved successfully',
            'data' => $tags,
        ]);
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();
        foreach ($validated['names'] as $name) {
            $this->tagRepository->createForUser($user, [
                'group' => $validated['group'],
                'name' => $name,
            ]);
        }

        return response()->json([
            'error' => false,
            'message' => 'Tags created successfully',
            'data' => null,
        ], 201);
    }
}
