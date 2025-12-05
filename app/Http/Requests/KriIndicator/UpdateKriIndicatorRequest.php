<?php

namespace App\Http\Requests\KriIndicator;

use Illuminate\Validation\Rule;
use App\Enums\KriIndicator\Status;
use App\Enums\KriIndicator\Frequency;
use App\Enums\KriIndicator\AlertRouting;
use App\Enums\KriIndicator\ActionOnBreach;
use App\Enums\KriIndicator\Directionality;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\KriIndicator\CollectionMethod;

class UpdateKriIndicatorRequest extends FormRequest
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
            'ai_risk_register_id' => ['sometimes', 'integer', 'exists:ai_risk_registers,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'definition' => ['sometimes', 'string'],
            'directionality' => ['sometimes', 'string', Rule::in(array_map(fn ($case) => $case->value, Directionality::cases()))],
            'unit' => ['sometimes', 'nullable', 'string', 'max:100'],
            'sample_window' => ['sometimes', 'string', 'max:100'],
            'threshold_warning' => ['sometimes', 'numeric'],
            'threshold_critical' => ['sometimes', 'numeric'],
            'data_source' => ['sometimes', 'string', 'max:255'],
            'collection_method' => ['sometimes', 'string', Rule::in(array_map(fn ($case) => $case->value, CollectionMethod::cases()))],
            'frequency' => ['sometimes', 'string', Rule::in(array_map(fn ($case) => $case->value, Frequency::cases()))],
            'alert_routing' => ['sometimes', 'string', Rule::in(array_map(fn ($case) => $case->value, AlertRouting::cases()))],
            'action_on_breach' => ['sometimes', 'string', Rule::in(array_map(fn ($case) => $case->value, ActionOnBreach::cases()))],
            'status' => ['sometimes', 'string', Rule::in(array_map(fn ($case) => $case->value, Status::cases()))],
            'owner_team' => ['sometimes', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
