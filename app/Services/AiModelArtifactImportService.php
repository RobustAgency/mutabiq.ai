<?php

namespace App\Services;

use App\Imports\AiModelArtifactsImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class AiModelArtifactImportService
{
    /**
     * Import AI model artifacts from a CSV or Excel file.
     */
    public function import(UploadedFile $file, int $organizationId, string $artifactType, int $createdBy): array
    {
        $import = new AiModelArtifactsImport($organizationId, $artifactType, $createdBy);

        try {
            Excel::import($import, $file);

            $successCount = $import->getSuccessCount();
            $failureCount = $import->getFailureCount();
            $errors = $import->getErrors();

            $hasErrors = $failureCount > 0;

            return [
                'error' => $hasErrors,
                'message' => $hasErrors
                    ? 'Import completed with some errors.'
                    : 'Import completed successfully.',
                'data' => [
                    'total_processed' => $successCount + $failureCount,
                    'successful' => $successCount,
                    'failed' => $failureCount,
                    'errors' => $errors,
                ],
            ];
        } catch (Throwable $e) {
            return $this->formatErrorResponse($e->getMessage());
        }
    }

    /**
     * Format standardized error response.
     */
    protected function formatErrorResponse(string $message): array
    {
        return [
            'error' => true,
            'message' => 'Import failed: ' . $message,
            'data' => [
                'total_processed' => 0,
                'successful' => 0,
                'failed' => 0,
                'errors' => [
                    [
                        'row' => 'N/A',
                        'errors' => ['general' => [$message]],
                    ],
                ],
            ],
        ];
    }
}
