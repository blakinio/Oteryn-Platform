<?php

namespace App\Identity\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property string $email
 * @property string $password
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
}
