<?php

namespace App\Http\Requests\GameAuth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LogicException;

final class IssueGameLoginTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'protocol_version' => ['required', 'integer', Rule::in([$this->protocolVersion()])],
            'identity_id' => ['prohibited'],
            'account_id' => ['prohibited'],
            'canary_account_id' => ['prohibited'],
        ];
    }

    private function protocolVersion(): int
    {
        $version = config('game-auth.protocol_version');

        if (! is_int($version) || $version < 1) {
            throw new LogicException('Invalid game authentication protocol version configuration.');
        }

        return $version;
    }
}
