<?php

namespace App\Http\Requests\DatasetSubjectPopulation;

use App\Enums\UserConsent\Jurisdiction;
use App\Enums\UserConsent\SubjectRealm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDatasetSubjectPopulationRequest extends FormRequest
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
            'dataset_id' => ['required', 'integer', 'exists:datasets,id'],
            'snapshot_id' => ['nullable', 'integer', 'exists:dataset_snapshots,id'],
            'subject_realm' => ['required', 'string', Rule::enum(SubjectRealm::class)],
            'jurisdiction' => ['required', 'string', Rule::enum(Jurisdiction::class)],
            'subjects_total' => ['required', 'integer', 'min:0'],
            'as_of' => ['required', 'date'],
        ];
    }
}
