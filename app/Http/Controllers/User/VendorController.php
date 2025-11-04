<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorRequest;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Repositories\VendorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct(
        private readonly VendorRepository $repository
    ) {}

    /**
     * Display a paginated listing of vendors.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $organizationID = $request->user()->organization_id;
        $vendors = $this->repository->getPaginatedVendors($organizationID, $perPage);

        return response()->json([
            'error' => false,
            'message' => 'Vendors retrieved successfully',
            'data' => $vendors,
        ]);
    }

    /**
     * Store a newly created vendor.
     */
    public function store(StoreVendorRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;
        $vendor = $this->repository->createVendor($validated);

        return response()->json([
            'error' => false,
            'message' => 'Vendor created successfully',
            'data' => new VendorResource($vendor),
        ], 201);
    }

    /**
     * Display the specified vendor.
     */
    public function show(Vendor $vendor): JsonResponse
    {
        return response()->json([
            'error' => false,
            'message' => 'Vendor retrieved successfully',
            'data' => new VendorResource($vendor),
        ]);
    }

    /**
     * Update the specified vendor.
     */
    public function update(
        UpdateVendorRequest $request,
        Vendor $vendor
    ): JsonResponse {
        $vendor = $this->repository->updateVendor($vendor, $request->validated());

        return response()->json([
            'error' => false,
            'message' => 'Vendor updated successfully',
            'data' => new VendorResource($vendor),
        ]);
    }

    /**
     * Remove the specified vendor.
     */
    public function destroy(Vendor $vendor): JsonResponse
    {
        $this->repository->deleteVendor($vendor);

        return response()->json([
            'error' => false,
            'message' => 'Vendor deleted successfully',
            'data' => null,
        ]);
    }
}
