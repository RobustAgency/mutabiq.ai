<?php

namespace App\Http\Requests;

use App\Enums\Framework\Status;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFrameworkRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
            'version' => ['sometimes', 'required', 'string', 'max:50'],
            'jurisdictions' => ['sometimes', 'required', 'array'],
            'scope' => ['sometimes', 'required', 'string'],
            'status' => ['sometimes', 'required', Rule::in(array_map(fn ($c) => $c->value, Status::cases()))],
            'effective_date' => ['sometimes', 'required', 'date'],
            'source_url' => ['sometimes', 'required', 'url', 'max:255'],
        ];
    }
}
