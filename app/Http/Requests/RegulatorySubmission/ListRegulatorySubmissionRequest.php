<?php

namespace App\Http\Requests\RegulatorySubmission;

use Illuminate\Validation\Rule;
use App\Enums\RegulatorySubmission\Status;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RegulatorySubmission\SubmissionType;

class ListRegulatorySubmissionRequest extends FormRequest
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
            'authority' => ['nullable', 'string', 'max:255'],
            'submission_type' => ['nullable', 'string', Rule::enum(SubmissionType::class)],
            'status' => ['nullable', 'string', Rule::enum(Status::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
