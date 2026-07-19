<?php

namespace App\Http\Requests\Identity;

use App\Identity\Models\Identity;
use App\Identity\Support\CanonicalEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class LoginIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');

        if (is_string($email)) {
            $this->merge([
                'email' => CanonicalEmail::normalize($email),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'max:254',
                'email:rfc',
            ],
            'password' => [
                'required',
                'string',
                'max:1024',
            ],
        ];
    }

    public function authenticate(): Identity
    {
        $email = $this->validated('email');
        $password = $this->validated('password');

        if (! is_string($email) || ! is_string($password)) {
            $this->failAuthentication();
        }

        $authenticated = Auth::guard('web')->attempt([
            'email' => $email,
            'password' => $password,
            'disabled_at' => null,
        ], false);

        if (! $authenticated) {
            $this->failAuthentication();
        }

        $identity = Auth::guard('web')->user();

        if (! $identity instanceof Identity) {
            Auth::guard('web')->logout();
            $this->failAuthentication();
        }

        return $identity;
    }

    /**
     * @throws ValidationException
     */
    private function failAuthentication(): never
    {
        throw ValidationException::withMessages([
            'email' => 'The provided credentials are invalid.',
        ]);
    }
}
