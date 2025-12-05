<?php

namespace App\Http\Requests;

use App\Enums\Framework\Status;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFrameworkRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'version' => ['required', 'string', 'max:50'],
            'jurisdictions' => ['required', 'array'],
            'scope' => ['required', 'string'],
            'status' => ['required', Rule::in(array_map(fn ($c) => $c->value, Status::cases()))],
            'effective_date' => ['required', 'date'],
            'source_url' => ['required', 'url', 'max:255'],
        ];
    }
}
