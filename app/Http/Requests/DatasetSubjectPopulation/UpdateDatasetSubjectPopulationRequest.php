<?php

namespace App\Http\Requests\DatasetSubjectPopulation;

use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDatasetSubjectPopulationRequest extends FormRequest
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
            'dataset_id' => ['sometimes', 'integer', 'exists:datasets,id'],
            'snapshot_id' => ['nullable', 'integer', 'exists:dataset_snapshots,id'],
            'subject_realm' => ['sometimes', 'string', Rule::enum(SubjectRealm::class)],
            'jurisdiction' => ['sometimes', 'string', Rule::enum(Jurisdiction::class)],
            'subjects_total' => ['sometimes', 'integer', 'min:0'],
            'as_of' => ['sometimes', 'date'],
        ];
    }
}
