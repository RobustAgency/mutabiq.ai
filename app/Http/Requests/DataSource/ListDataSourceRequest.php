<?php

namespace App\Http\Requests\DataSource;

use Illuminate\Foundation\Http\FormRequest;

class ListDataSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date'],
            'name' => ['sometimes', 'string', 'max:255'],
            'system_type' => ['sometimes', 'string', 'max:255'],
            'access_method' => ['sometimes', 'string', 'max:255'],
            'classification' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
