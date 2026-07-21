<?php

return [
    'protocol_version' => 1,

    'ticket' => [
        'audience' => 'oteryn-game-gateway',
        'ttl_seconds' => (int) env('GAME_AUTH_TICKET_TTL_SECONDS', 60),
    ],
];
