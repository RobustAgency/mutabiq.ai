<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListAiModelCardRequest extends FormRequest
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
            'status' => ['nullable', 'string'],
            'publication_status' => ['nullable', 'string'],
            'owner' => ['nullable', 'string'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'creator_role' => ['nullable', 'string'],
            'format' => ['nullable', 'string'],
        ];
    }
}
