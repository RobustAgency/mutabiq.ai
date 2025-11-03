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
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls' . '|max:10240'],
            'artifact_type' => ['required', 'string', Rule::in(array_map(fn($c) => $c->value, ArtifactType::cases()))],

        ];
    }
}
