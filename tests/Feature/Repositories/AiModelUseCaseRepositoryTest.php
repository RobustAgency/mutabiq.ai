<?php

namespace Tests\Feature\Repositories;

use App\Models\AiModel;
use App\Models\AiModelUseCase;
use App\Models\UseCase;
use App\Models\AiModelVersion;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\AiModelUseCaseRepository;
use App\Models\User;
use Tests\TestCase;

class AiModelUseCaseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AiModelUseCaseRepository $aiModelUseCaseRepository;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiModelUseCaseRepository = new AiModelUseCaseRepository();
        $this->organization = Organization::factory()->create();
    }

    public function test_it_get_filtered_ai_model_use_cases(): void
    {
        AiModelUseCase::factory()->count(15)->create([
            'ai_model_id' => 1,
            'organization_id' => $this->organization->id,
        ]);

        $filters = [
            'organization_id' => $this->organization->id,
            'ai_model_id' => 1,
            'per_page' => 5,
        ];

        $paginator = $this->aiModelUseCaseRepository->getFilteredAiModelUseCases($filters);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(5, $paginator->perPage());
        $this->assertCount(5, $paginator->items());
        $this->assertTrue($paginator->total() >= 15);
    }

    public function test_it_handles_no_filters_gracefully(): void
    {
        AiModelUseCase::factory()->count(10)->create(['organization_id' => $this->organization->id]);

        $filters = ['organization_id' => $this->organization->id];

        $paginator = $this->aiModelUseCaseRepository->getFilteredAiModelUseCases($filters);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(10, $paginator->perPage());
        $this->assertCount(10, $paginator->items());
        $this->assertTrue($paginator->total() >= 10);
    }

    public function test_it_applies_ai_model_id_filter_correctly(): void
    {
        AiModelUseCase::factory()->count(5)->create(['ai_model_id' => 1]);
        AiModelUseCase::factory()->count(3)->create(['ai_model_id' => 2]);

        $filters = [
            'ai_model_id' => 2,
            'per_page' => 10,
        ];

        $paginator = $this->aiModelUseCaseRepository->getFilteredAiModelUseCases($filters);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(10, $paginator->perPage());
        $this->assertCount(3, $paginator->items());
        $this->assertTrue($paginator->total() >= 3);
        foreach ($paginator->items() as $item) {
            $this->assertEquals(2, $item->ai_model_id);
        }
    }

    public function test_it_creates_ai_model_use_case_with_user_tracking(): void
    {
        $user = User::factory()->create();
        $aiModel = AiModel::factory()->create();
        $useCase = UseCase::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $data = [
            'organization_id' => $this->organization->id,
            'ai_model_id' => $aiModel->id,
            'use_case_id' => $useCase->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'relationship_type' => 'primary',
        ];

        $aiModelUseCase = $this->aiModelUseCaseRepository->createAiModelUseCase($user, $data);

        $this->assertInstanceOf(AiModelUseCase::class, $aiModelUseCase);
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $aiModelUseCase->{$key});
        }
        $this->assertEquals($user->id, $aiModelUseCase->created_by);
        $this->assertEquals($user->id, $aiModelUseCase->updated_by);
    }

    public function test_it_updates_ai_model_use_case_with_user_tracking(): void
    {
        $user = User::factory()->create();
        $aiModel = AiModel::factory()->create();
        $useCase = UseCase::factory()->create();
        $aiModelVersion = AiModelVersion::factory()->create(['ai_model_id' => $aiModel->id]);
        $aiModelUseCase = AiModelUseCase::factory()->create([
            'ai_model_id' => $aiModel->id,
            'use_case_id' => $useCase->id,
            'ai_model_version_id' => $aiModelVersion->id,
            'relationship_type' => 'primary',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $newUser = User::factory()->create();
        $updateData = [
            'relationship_type' => 'secondary',
        ];

        $result = $this->aiModelUseCaseRepository->updateAiModelUseCase($aiModelUseCase, $newUser, $updateData);

        $this->assertTrue($result);
        $aiModelUseCase->refresh();
        $this->assertEquals('secondary', $aiModelUseCase->relationship_type);
        $this->assertEquals($newUser->id, $aiModelUseCase->updated_by);
    }
}
