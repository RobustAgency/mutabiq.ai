<?php

namespace App\Http\Requests\Dataset;

use Illuminate\Foundation\Http\FormRequest;

class ListDatasetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'owner_team' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:100'],
            'data_steward' => ['nullable', 'string', 'max:255'],
            'license_type' => ['nullable', 'string', 'max:100'],
            'purpose' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
