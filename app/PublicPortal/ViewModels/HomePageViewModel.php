<?php

namespace App\PublicPortal\ViewModels;

final readonly class HomePageViewModel
{
    public function __construct(
        public HomeWorldSummary $world,
        public HomeNewsSummary $news,
    ) {}
}
