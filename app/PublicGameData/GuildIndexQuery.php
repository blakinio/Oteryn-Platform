<?php

namespace App\PublicGameData;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

final class GuildIndexQuery
{
    private const PER_PAGE = 50;

    /**
     * @return LengthAwarePaginator<int, stdClass>
     */
    public function paginate(): LengthAwarePaginator
    {
        return DB::connection('canary')
            ->table('guilds as guild')
            ->select(['guild.name'])
            ->selectSub(function (Builder $membership): void {
                $membership
                    ->from('guild_membership as membership')
                    ->join('players as player', 'player.id', '=', 'membership.player_id')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('membership.guild_id', 'guild.id')
                    ->where('player.deletion', 0);
            }, 'active_member_count')
            ->orderBy('guild.name')
            ->orderBy('guild.id')
            ->paginate(self::PER_PAGE);
    }
}
