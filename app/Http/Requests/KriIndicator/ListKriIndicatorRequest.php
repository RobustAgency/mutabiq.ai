<?php

namespace App\Http\Requests\KriIndicator;

use Illuminate\Validation\Rule;
use App\Enums\KriIndicator\Status;
use App\Enums\KriIndicator\Frequency;
use App\Enums\KriIndicator\ActionOnBreach;
use App\Enums\KriIndicator\Directionality;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\KriIndicator\CollectionMethod;

class ListKriIndicatorRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::in(array_map(fn ($case) => $case->value, Status::cases()))],
            'frequency' => ['sometimes', 'string', Rule::in(array_map(fn ($case) => $case->value, Frequency::cases()))],
            'directionality' => ['sometimes', 'string', Rule::in(array_map(fn ($case) => $case->value, Directionality::cases()))],
            'collection_method' => ['sometimes', 'string', Rule::in(array_map(fn ($case) => $case->value, CollectionMethod::cases()))],
            'action_on_breach' => ['sometimes', 'string', Rule::in(array_map(fn ($case) => $case->value, ActionOnBreach::cases()))],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
