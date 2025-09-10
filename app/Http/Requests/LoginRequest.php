<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $email = $this->input('email');
        $organization = User::where('email', $email)->with('organization')->first()?->organization;
        if ($organization && ! $organization->is_active) {
            return false;
        }

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
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    protected function failedAuthorization()
    {
        abort(response()->json([
            'error' => true,
            'message' => 'Your organization is inactive. Please contact admin.',
            'data' => null,
        ], 403));
    }
}
