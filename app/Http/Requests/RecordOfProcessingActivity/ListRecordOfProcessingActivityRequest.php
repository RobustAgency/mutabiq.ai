<?php

namespace App\Http\Requests\RecordOfProcessingActivity;

use Illuminate\Foundation\Http\FormRequest;

class ListRecordOfProcessingActivityRequest extends FormRequest
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
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', 'string'],
            'owner_team' => ['nullable', 'string'],
            'to' => ['nullable', 'date'],
            'from' => ['nullable', 'date'],
        ];
    }
}
