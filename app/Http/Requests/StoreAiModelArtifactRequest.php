<?php

namespace App\Http\Requests;

use App\Enums\ArtifactType;
use Illuminate\Validation\Rule;
use App\Enums\ArtifactFileFormat;
use App\Enums\ArtifactEnvironment;
use App\Enums\ArtifactChecksumAlgorithm;
use Illuminate\Foundation\Http\FormRequest;

class StoreAiModelArtifactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ai_model_version_id' => ['required', 'exists:ai_model_versions,id'],
            'name' => ['required', 'string', 'max:255'],
            'uri' => ['nullable', 'url', 'max:2048', 'required_without:file'],
            'file' => ['nullable', 'file', 'max:26214400', 'required_without:uri'],
            'checksum_algorithm' => ['nullable', Rule::enum(ArtifactChecksumAlgorithm::class)],
            'checksum_value' => ['required_with:checksum_algorithm', 'string', 'max:255'],
            'environment' => ['nullable', Rule::enum(ArtifactEnvironment::class)],
            'file_format' => ['nullable', Rule::enum(ArtifactFileFormat::class)],
            'size_bytes' => ['nullable', 'integer', 'min:1'],
            'artifact_type' => ['required', Rule::enum(ArtifactType::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
