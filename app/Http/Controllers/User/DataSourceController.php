<?php

namespace App\Http\Controllers\User;

use App\Models\DataSource;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataSourceResource;
use App\Repositories\DataSourceRepository;
use App\Http\Requests\DataSource\ListDataSourceRequest;
use App\Http\Requests\DataSource\StoreDataSourceRequest;
use App\Http\Requests\DataSource\UpdateDataSourceRequest;

class DataSourceController extends Controller
{
    public function __construct(private DataSourceRepository $dataSourceRepository) {}

    /**
     * Display a listing of data sources.
     */
    public function index(ListDataSourceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;
        $dataSources = $this->dataSourceRepository->getFilteredDataSources($validated);

        return response()->json([
            'error' => false,
            'data' => $dataSources,
            'message' => 'Data sources retrieved successfully',
        ]);
    }

    /**
     * Store a newly created data source.
     */
    public function store(StoreDataSourceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['organization_id'] = $request->user()->organization_id;

        $dataSource = $this->dataSourceRepository->createDataSource($validated);

        return response()->json([
            'error' => false,
            'message' => 'Data source created successfully',
            'data' => $dataSource,
        ], 201);
    }

    /**
     * Display the specified data source.
     */
    public function show(DataSource $dataSource): JsonResponse
    {
        return response()->json([
            'error' => false,
            'data' => new DataSourceResource($dataSource),
            'message' => 'Data source retrieved successfully',
        ]);
    }

    /**
     * Update the specified data source.
     */
    public function update(UpdateDataSourceRequest $request, DataSource $dataSource): JsonResponse
    {
        $validated = $request->validated();

        $this->dataSourceRepository->updateDataSource($dataSource, $validated);

        return response()->json([
            'error' => false,
            'message' => 'Data source updated successfully',
            'data' => $dataSource->fresh(),
        ], 200);
    }

    /**
     * Remove the specified data source.
     */
    public function destroy(DataSource $dataSource): JsonResponse
    {
        $this->dataSourceRepository->delete($dataSource);

        return response()->json([
            'error' => false,
            'message' => 'Data source deleted successfully',
            'data' => null,
        ]);
    }
}
