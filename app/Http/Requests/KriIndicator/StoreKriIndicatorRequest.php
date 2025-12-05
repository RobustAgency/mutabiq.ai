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

class StoreKriIndicatorRequest extends FormRequest
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
            'ai_risk_register_id' => ['required', 'integer', 'exists:ai_risk_registers,id'],
            'name' => ['required', 'string', 'max:255'],
            'definition' => ['required', 'string'],
            'directionality' => ['required', 'string', Rule::in(array_map(fn ($case) => $case->value, Directionality::cases()))],
            'unit' => ['nullable', 'string', 'max:100'],
            'sample_window' => ['required', 'string', 'max:100'],
            'threshold_warning' => ['required', 'numeric'],
            'threshold_critical' => ['required', 'numeric'],
            'data_source' => ['required', 'string', 'max:255'],
            'collection_method' => ['required', 'string', Rule::in(array_map(fn ($case) => $case->value, CollectionMethod::cases()))],
            'frequency' => ['required', 'string', Rule::in(array_map(fn ($case) => $case->value, Frequency::cases()))],
            'alert_routing' => ['required', 'string', Rule::in(array_map(fn ($case) => $case->value, AlertRouting::cases()))],
            'action_on_breach' => ['required', 'string', Rule::in(array_map(fn ($case) => $case->value, ActionOnBreach::cases()))],
            'status' => ['required', 'string', Rule::in(array_map(fn ($case) => $case->value, Status::cases()))],
            'owner_team' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
