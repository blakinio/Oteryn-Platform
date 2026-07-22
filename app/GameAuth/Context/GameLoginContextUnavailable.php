<?php

namespace App\GameAuth\Context;

use RuntimeException;

final class GameLoginContextUnavailable extends RuntimeException
{
    public function __construct(
        public readonly string $reason,
        public readonly int $httpStatus,
    ) {
        parent::__construct($reason);
    }
}
