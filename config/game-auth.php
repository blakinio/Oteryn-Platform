<?php

$gatewayServiceTokenHashes = array_values(array_unique(array_filter(array_map(
    static fn (string $hash): string => strtolower(trim($hash)),
    array_merge(
        explode(',', (string) env('GAME_AUTH_GATEWAY_SERVICE_TOKEN_SHA256S', '')),
        [(string) env('GAME_AUTH_GATEWAY_SERVICE_TOKEN_SHA256', '')],
    ),
), static fn (string $hash): bool => $hash !== '')));

return [
    'protocol_version' => 1,

    'oauth' => [
        'native_client_name' => env('GAME_AUTH_OAUTH_NATIVE_CLIENT_NAME', 'Oteryn OTClient'),
        'native_redirect_uri' => env('GAME_AUTH_OAUTH_NATIVE_REDIRECT_URI', 'http://127.0.0.1/callback'),
        'scope' => 'game:ticket',
        'access_token_ttl_minutes' => (int) env('GAME_AUTH_OAUTH_ACCESS_TOKEN_TTL_MINUTES', 5),
        'refresh_token_ttl_minutes' => (int) env('GAME_AUTH_OAUTH_REFRESH_TOKEN_TTL_MINUTES', 10),
    ],

    'ticket' => [
        'audience' => 'oteryn-game-gateway',
        'ttl_seconds' => (int) env('GAME_AUTH_TICKET_TTL_SECONDS', 60),
    ],

    'gateway' => [
        'service_token_sha256s' => $gatewayServiceTokenHashes,
    ],
];
