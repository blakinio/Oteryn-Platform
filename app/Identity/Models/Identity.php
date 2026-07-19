<?php

namespace App\Identity\Models;

use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $email
 * @property string $password
 * @property int $web_session_generation
 * @property Carbon|null $disabled_at
 * @property string|null $mfa_secret
 * @property array<int, string>|null $mfa_recovery_codes
 * @property Carbon|null $mfa_confirmed_at
 */
final class Identity extends Authenticatable implements CanResetPasswordContract
{
    use CanResetPasswordTrait;
    use Notifiable;

    protected $table = 'identities';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'mfa_secret',
        'mfa_recovery_codes',
    ];

    public function hasConfirmedMfa(): bool
    {
        return $this->mfa_secret !== null && $this->mfa_confirmed_at !== null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'web_session_generation' => 'integer',
            'disabled_at' => 'datetime',
            'mfa_secret' => 'encrypted',
            'mfa_recovery_codes' => 'encrypted:array',
            'mfa_confirmed_at' => 'datetime',
        ];
    }
}
