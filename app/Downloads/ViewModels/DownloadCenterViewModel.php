<?php

namespace App\Downloads\ViewModels;

use App\Downloads\DownloadCenterState;
use App\Downloads\Models\ClientRelease;

final readonly class DownloadCenterViewModel
{
    /**
     * @param  list<ClientRelease>  $releases
     */
    public function __construct(
        public DownloadCenterState $state,
        public array $releases,
        public ?string $platform,
    ) {}
}
