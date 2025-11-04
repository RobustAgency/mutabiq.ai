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
    public function import(UploadedFile $file, int $organizationId, string $artifactType, int $createdBy): array
    {

        $import = new AiModelArtifactsImport($organizationId, $artifactType, $createdBy);

        try {
            Excel::import($import, $file);

            return [
                'error' => false,
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
                'error' => true,
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
}
