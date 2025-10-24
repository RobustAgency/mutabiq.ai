<?php

namespace Tests\Feature\Controllers\User;

use App\Enums\DataElement\CdeCategory;
use App\Enums\DatasetElementMap\CdeInDataset;
use App\Enums\DatasetElementMap\Deprecated;
use App\Enums\DatasetElementMap\Nullable;
use App\Enums\DatasetElementMap\PiiOverride;
use App\Enums\DatasetElementMap\SensitivityOverride;
use App\Models\DataElement;
use App\Models\Dataset;
use App\Models\DatasetDataElement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatasetElementControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        $dataset = Dataset::factory()->create();
        $dataElement = DataElement::factory()->create();

        return array_merge([
            'dataset_id' => $dataset->id,
            'data_element_id' => $dataElement->id,
            'column_name' => 'customer_email',
            'nullable' => Nullable::NO->value,
            'sensitivity_override' => SensitivityOverride::CONFIDENTIAL->value,
            'pii_override' => PiiOverride::YES->value,
            'transform_applied' => 'Encrypted',
            'quality_rules_applied' => 'Must be valid email format',
            'cde_in_dataset' => CdeInDataset::YES->value,
            'cde_category_in_dataset' => CdeCategory::FINANCIAL->value,
            'lineage_source_column' => 'source.email',
            'deprecated' => Deprecated::NO->value,
        ], $overrides);
    }

    public function test_user_can_associate_data_element_with_dataset(): void
    {
        $data = $this->validPayload();

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(201)
            ->assertJson([
                'error' => false,
                'message' => 'Data Element associated with Dataset successfully.',
                'data' => [
                    'dataset_id' => $data['dataset_id'],
                    'data_element_id' => $data['data_element_id'],
                    'column_name' => 'customer_email',
                    'nullable' => Nullable::NO->value,
                ],
            ]);

        $this->assertDatabaseHas('dataset_element', [
            'dataset_id' => $data['dataset_id'],
            'data_element_id' => $data['data_element_id'],
            'column_name' => 'customer_email',
        ]);
    }

    public function test_user_cannot_associate_without_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'dataset_id',
                'data_element_id',
                'column_name',
                'nullable',
                'cde_in_dataset',
            ]);
    }



    public function test_dataset_id_must_exist(): void
    {
        $data = $this->validPayload(['dataset_id' => 99999]);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dataset_id']);
    }

    public function test_data_element_id_must_exist(): void
    {
        $data = $this->validPayload(['data_element_id' => 99999]);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['data_element_id']);
    }

    public function test_column_name_is_required(): void
    {
        $data = $this->validPayload(['column_name' => '']);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['column_name']);
    }

    public function test_nullable_must_be_valid_enum(): void
    {
        $data = $this->validPayload(['nullable' => 'Invalid']);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nullable']);
    }

    public function test_sensitivity_override_must_be_valid_enum_when_provided(): void
    {
        $data = $this->validPayload(['sensitivity_override' => 'Invalid']);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sensitivity_override']);
    }

    public function test_pii_override_must_be_valid_enum_when_provided(): void
    {
        $data = $this->validPayload(['pii_override' => 'Invalid']);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['pii_override']);
    }

    public function test_cde_in_dataset_must_be_valid_enum(): void
    {
        $data = $this->validPayload(['cde_in_dataset' => 'Invalid']);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cde_in_dataset']);
    }

    public function test_cde_category_in_dataset_must_be_valid_enum_when_provided(): void
    {
        $data = $this->validPayload(['cde_category_in_dataset' => 'Invalid']);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cde_category_in_dataset']);
    }

    public function test_deprecated_must_be_valid_enum_when_provided(): void
    {
        $data = $this->validPayload(['deprecated' => 'Invalid']);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['deprecated']);
    }

    public function test_nullable_fields_can_be_null(): void
    {
        $data = $this->validPayload([
            'sensitivity_override' => null,
            'transform_applied' => null,
            'quality_rules_applied' => null,
            'cde_category_in_dataset' => null,
            'lineage_source_column' => null,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(201);

        $association = DatasetDataElement::find($response->json('data.id'));
        $this->assertNull($association->sensitivity_override);
        $this->assertNull($association->transform_applied);
        $this->assertNull($association->quality_rules_applied);
        $this->assertNull($association->cde_category_in_dataset);
        $this->assertNull($association->lineage_source_column);
    }

    public function test_association_with_all_enum_values(): void
    {
        $dataset = Dataset::factory()->create();
        $dataElement = DataElement::factory()->create();

        $data = [
            'dataset_id' => $dataset->id,
            'data_element_id' => $dataElement->id,
            'column_name' => 'test_column',
            'nullable' => Nullable::YES->value,
            'sensitivity_override' => SensitivityOverride::INTERNAL->value,
            'pii_override' => PiiOverride::NO->value,
            'cde_in_dataset' => CdeInDataset::NO->value,
            'deprecated' => Deprecated::YES->value,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('dataset_element', [
            'dataset_id' => $dataset->id,
            'data_element_id' => $dataElement->id,
            'nullable' => Nullable::YES->value,
            'sensitivity_override' => SensitivityOverride::INTERNAL->value,
            'pii_override' => PiiOverride::NO->value,
            'cde_in_dataset' => CdeInDataset::NO->value,
            'deprecated' => Deprecated::YES->value,
        ]);
    }

    public function test_can_associate_same_data_element_to_multiple_datasets(): void
    {
        $dataElement = DataElement::factory()->create();
        $dataset1 = Dataset::factory()->create();
        $dataset2 = Dataset::factory()->create();

        $data1 = $this->validPayload([
            'dataset_id' => $dataset1->id,
            'data_element_id' => $dataElement->id,
            'column_name' => 'column_in_dataset1',
        ]);

        $data2 = $this->validPayload([
            'dataset_id' => $dataset2->id,
            'data_element_id' => $dataElement->id,
            'column_name' => 'column_in_dataset2',
        ]);

        $response1 = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data1);
        $response2 = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data2);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        $this->assertDatabaseHas('dataset_element', [
            'dataset_id' => $dataset1->id,
            'data_element_id' => $dataElement->id,
            'column_name' => 'column_in_dataset1',
        ]);

        $this->assertDatabaseHas('dataset_element', [
            'dataset_id' => $dataset2->id,
            'data_element_id' => $dataElement->id,
            'column_name' => 'column_in_dataset2',
        ]);
    }

    public function test_can_associate_multiple_data_elements_to_same_dataset(): void
    {
        $dataset = Dataset::factory()->create();
        $dataElement1 = DataElement::factory()->create();
        $dataElement2 = DataElement::factory()->create();

        $data1 = $this->validPayload([
            'dataset_id' => $dataset->id,
            'data_element_id' => $dataElement1->id,
            'column_name' => 'column_1',
        ]);

        $data2 = $this->validPayload([
            'dataset_id' => $dataset->id,
            'data_element_id' => $dataElement2->id,
            'column_name' => 'column_2',
        ]);

        $response1 = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data1);
        $response2 = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data2);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        $this->assertDatabaseHas('dataset_element', [
            'dataset_id' => $dataset->id,
            'data_element_id' => $dataElement1->id,
        ]);

        $this->assertDatabaseHas('dataset_element', [
            'dataset_id' => $dataset->id,
            'data_element_id' => $dataElement2->id,
        ]);
    }

    public function test_column_name_max_length_validation(): void
    {
        $data = $this->validPayload(['column_name' => str_repeat('a', 256)]);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['column_name']);
    }

    public function test_transform_applied_max_length_validation(): void
    {
        $data = $this->validPayload(['transform_applied' => str_repeat('a', 256)]);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['transform_applied']);
    }

    public function test_lineage_source_column_max_length_validation(): void
    {
        $data = $this->validPayload(['lineage_source_column' => str_repeat('a', 256)]);

        $response = $this->actingAs($this->user)->postJson('/api/associate-data-element-with-dataset', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lineage_source_column']);
    }
}
