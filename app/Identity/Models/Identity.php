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
 * @property string|null $two_factor_secret
 * @property array<int, string>|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
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
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public function hasConfirmedMfa(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'web_session_generation' => 'integer',
            'disabled_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
