<?php

namespace App\Http\Requests\DataSource;

use Illuminate\Validation\Rule;
use App\Enums\DataSource\SystemType;
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
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'name' => ['nullable', 'string', 'max:255'],
            'system_type' => ['nullable', Rule::enum(SystemType::class)],
        ];
    }
}
