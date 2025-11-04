<?php

namespace App\Imports;

use App\Models\AiModelArtifact;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class AiModelArtifactsImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    protected int $organizationId;
    protected string $artifactType;
    protected int $createdBy;
    protected array $errors = [];
    protected int $successCount = 0;
    protected int $failureCount = 0;

    public function __construct(int $organizationId, string $artifactType, int $createdBy)
    {
        $this->organizationId = $organizationId;
        $this->artifactType = $artifactType;
        $this->createdBy = $createdBy;
    }

    /**
     * @param Collection <int, array> $collection
     */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $index => $row) {
            try {
                $this->validateRow($row->toArray(), $index + 2);

                AiModelArtifact::create([
                    'organization_id' => $this->organizationId,
                    'ai_model_version_id' => $row['model_version_id'],
                    'artifact_type' => $this->artifactType,
                    'uri' => $row['uri'],
                    'checksum' => $row['checksum'] ?? null,
                    'size_bytes' => $row['size_bytes'] ?? null,
                    'created_by' => $this->createdBy,
                    'notes' => $row['notes'] ?? null,
                ]);

                $this->successCount++;
            } catch (ValidationException $e) {
                $this->failureCount++;
                $this->errors[] = [
                    'row' => $index + 2,
                    'errors' => $e->errors(),
                ];
            } catch (\Exception $e) {
                $this->failureCount++;
                $this->errors[] = [
                    'row' => $index + 2,
                    'errors' => ['general' => [$e->getMessage()]],
                ];
            }
        }
    }

    /**
     * Validate a single row
     *
     * @param array $row
     * @param int $rowNumber
     * @throws ValidationException
     */
    protected function validateRow(array $row, int $rowNumber): void
    {
        $validator = Validator::make($row, [
            'model_version_id' => ['required', 'integer', 'exists:ai_model_versions,id'],
            'uri' => ['required', 'string', 'max:1024'],
            'checksum' => ['nullable', 'string', 'max:255'],
            'size_bytes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'model_version_id.required' => "Row {$rowNumber}: The AI model version is required.",
            'uri.required' => "Row {$rowNumber}: The URI is required.",
            'uri.max' => "Row {$rowNumber}: The URI must not exceed 1024 characters.",
            'size_bytes.min' => "Row {$rowNumber}: The size in bytes must be at least 0.",
            'notes.max' => "Row {$rowNumber}: The notes must not exceed 2000 characters.",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Get import errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get success count
     *
     * @return int
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * Get failure count
     *
     * @return int
     */
    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 500;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
