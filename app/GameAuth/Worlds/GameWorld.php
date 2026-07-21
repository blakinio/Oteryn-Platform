<?php

namespace App\GameAuth\Worlds;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $region
 * @property GameWorldStatus $status
 * @property bool $login_enabled
 * @property string $game_host
 * @property int $game_port
 */
final class GameWorld extends Model
{
    protected $table = 'game_worlds';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'name',
        'region',
        'status',
        'login_enabled',
        'game_host',
        'game_port',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => GameWorldStatus::class,
            'login_enabled' => 'boolean',
            'game_port' => 'integer',
        ];
    }
}
