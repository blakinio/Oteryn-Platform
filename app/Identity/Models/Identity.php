<?php

namespace App\Identity\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

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
