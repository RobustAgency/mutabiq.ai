<?php

namespace App\Http\Requests\ConsentScope;

use Illuminate\Foundation\Http\FormRequest;

class ListConsentScopeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
