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
            'sensitivity' => ['nullable', 'string', 'max:255'],
            'contains_pii' => ['nullable', 'boolean'],
            'controller_role' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
