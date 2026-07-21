<?php

namespace App\Console\Commands;

use App\GameAuth\OAuth\NativeOAuthClientManager;
use Illuminate\Console\Command;

final class EnsureNativeOAuthClient extends Command
{
    protected $signature = 'game-auth:oauth-client:ensure';

    protected $description = 'Ensure the first-party public Oteryn native OAuth client exists.';

    public function handle(NativeOAuthClientManager $clients): int
    {
        $client = $clients->ensure();

        $this->components->info("Oteryn native OAuth client id: {$client->getKey()}");

        return self::SUCCESS;
    }
}
