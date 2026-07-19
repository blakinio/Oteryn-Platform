<?php

namespace App\Identity\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $email
 * @property string $password
 * @property int $web_session_generation
 * @property Carbon|null $disabled_at
 */
final class Identity extends Authenticatable
{
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
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'web_session_generation' => 'integer',
            'disabled_at' => 'datetime',
        ];
    }
}
