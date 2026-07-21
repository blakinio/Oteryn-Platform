<?php

namespace App\GameAuth\Tickets;

final class GameLoginTicketSecrets
{
    private const ENTROPY_BYTES = 32;

    public function generate(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(self::ENTROPY_BYTES)), '+/', '-_'), '=');
    }

    public function hash(string $ticket): string
    {
        return hash('sha256', $ticket);
    }
}
