<?php

namespace App\Http\Requests;

use App\Enums\ArtifactType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAiModelArtifactRequest extends FormRequest
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
            'ai_model_version_id' => ['sometimes', 'integer', 'exists:ai_model_versions,id'],
            'artifact_type' => ['required', 'string', Rule::in(array_map(fn($c) => $c->value, ArtifactType::cases()))],
            'uri' => ['sometimes', 'string', 'max:1024'],
            'checksum' => ['nullable', 'string', 'max:255'],
            'size_bytes' => ['nullable', 'integer', 'min:0'],
            'created_by' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ai_model_version_id' => 'AI model version',
            'artifact_type' => 'artifact type',
            'uri' => 'URI',
            'checksum' => 'checksum',
            'size_bytes' => 'size in bytes',
            'created_by' => 'creator email',
            'notes' => 'notes',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ai_model_version_id.exists' => 'The selected AI model version does not exist.',
            'artifact_type.in' => 'The selected artifact type is invalid.',
            'uri.max' => 'The URI must not exceed 1024 characters.',
            'size_bytes.min' => 'The size in bytes must be at least 0.',
            'notes.max' => 'The notes must not exceed 2000 characters.',
        ];
    }
}
