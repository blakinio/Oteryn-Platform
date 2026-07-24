<?php

namespace App\Announcements\ViewModels;

use App\Announcements\Models\SiteAnnouncement;
use App\PublicPortal\PublicContentState;

final readonly class AnnouncementTicker
{
    /**
     * @param  list<SiteAnnouncement>  $items
     */
    public function __construct(
        public PublicContentState $state,
        public array $items,
    ) {}
}
