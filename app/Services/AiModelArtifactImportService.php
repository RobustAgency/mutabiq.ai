<?php

namespace App\Services;

use App\Imports\AiModelArtifactsImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class AiModelArtifactImportService
{
    /**
     * Import AI model artifacts from a CSV or Excel file
     *
     * @param UploadedFile $file
     * @param int $organizationId
     * @param string $artifactType
     * @param int $createdBy
     * @return array
     */
    public function import(
        UploadedFile $file,
        int $organizationId,
        string $artifactType,
        int $createdBy,
    ): array {
        // Validate file type
        $this->validateFileType($file);

        // Create import instance
        $import = new AiModelArtifactsImport(
            $organizationId,
            $artifactType,
            $createdBy
        );

        try {
            // Import the file
            Excel::import($import, $file);

            return [
                'success' => true,
                'message' => 'Import completed successfully',
                'data' => [
                    'total_processed' => $import->getSuccessCount() + $import->getFailureCount(),
                    'successful' => $import->getSuccessCount(),
                    'failed' => $import->getFailureCount(),
                    'errors' => $import->getErrors(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'data' => [
                    'total_processed' => 0,
                    'successful' => 0,
                    'failed' => 0,
                    'errors' => [
                        [
                            'row' => 'N/A',
                            'errors' => ['general' => [$e->getMessage()]],
                        ],
                    ],
                ],
            ];
        }
    }

    /**
     * Validate the uploaded file type
     *
     * @param UploadedFile $file
     * @throws \InvalidArgumentException
     */
    protected function validateFileType(UploadedFile $file): void
    {
        $allowedExtensions = ['csv', 'xlsx', 'xls'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException(
                'Invalid file type. Only CSV, XLS, and XLSX files are allowed.'
            );
        }

        // Validate MIME type
        $allowedMimeTypes = [
            'text/csv',
            'text/plain',
            'application/csv',
            'text/comma-separated-values',
            'application/excel',
            'application/vnd.ms-excel',
            'application/vnd.msexcel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \InvalidArgumentException(
                'Invalid file MIME type. Please upload a valid CSV or Excel file.'
            );
        }
    }

    /**
     * Generate a sample template for import
     *
     * @return string Path to the generated template
     */
    public function generateTemplate(): string
    {
        $headers = [
            'ai_model_version_id',
            'uri',
            'checksum',
            'size_bytes',
            'notes',
        ];

        $sampleData = [
            [
                'ai_model_version_id' => '1',
                'uri' => 's3://ai-model-artifacts/models/example/model.bin',
                'checksum' => 'abc123def456',
                'size_bytes' => '1048576',
                'notes' => 'Example model binary artifact',
            ],
            [
                'ai_model_version_id' => '2',
                'uri' => 's3://ai-model-artifacts/configs/example/config.yaml',
                'checksum' => '',
                'size_bytes' => '',
                'notes' => 'Example configuration file',
            ],
        ];

        $filename = 'ai_model_artifacts_template_' . now()->format('Y-m-d_His') . '.csv';
        $path = storage_path('app/public/templates/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $handle = fopen($path, 'w');
        fputcsv($handle, $headers);

        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $path;
    }

    /**
     * Validate the artifact type
     *
     * @param string $artifactType
     * @return bool
     */
    public function isValidArtifactType(string $artifactType): bool
    {
        $validTypes = array_map(
            fn($case) => $case->value,
            \App\Enums\ArtifactType::cases()
        );

        return in_array($artifactType, $validTypes);
    }
}
