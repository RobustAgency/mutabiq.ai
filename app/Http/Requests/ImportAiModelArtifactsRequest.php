<?php

namespace App\Http\Requests;

use App\Enums\ArtifactType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportAiModelArtifactsRequest extends FormRequest
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
            'file' => [
                'required',
                'file',
                'mimes:csv,txt,xlsx,xls',
                'max:10240', // 10MB max file size
            ],
            'artifact_type' => [
                'required',
                'string',
                Rule::in(array_map(fn($c) => $c->value, ArtifactType::cases()))
            ],
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
            'file' => 'import file',
            'artifact_type' => 'artifact type',
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
            'file.required' => 'Please upload a file to import.',
            'file.file' => 'The uploaded file is invalid.',
            'file.mimes' => 'The file must be a CSV or Excel file (csv, xlsx, xls).',
            'file.max' => 'The file size must not exceed 10MB.',
            'artifact_type.required' => 'The artifact type is required.',
            'artifact_type.in' => 'The selected artifact type is invalid.',
        ];
    }
}
