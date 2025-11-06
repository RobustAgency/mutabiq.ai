<?php

namespace App\Http\Requests;

use App\Enums\ArtifactType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiModelArtifactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'version_id' => ['required', 'exists:ai_model_versions,id'],
            'url' => ['required', 'url', 'max:2048'],
            'checksum' => ['required', 'string', 'max:255'],
            'size_bytes' => ['required', 'integer', 'min:0'],
            'artifact_type' => ['required', 'string', Rule::in(array_map(fn($c) => $c->value, ArtifactType::cases()))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'created_by' => ['nullable', 'email', 'max:255'],

        ];
    }
}
