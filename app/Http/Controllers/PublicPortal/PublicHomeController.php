<?php

namespace App\Http\Controllers\PublicPortal;

use App\PublicPortal\HomePageQuery;
use Illuminate\Contracts\View\View;

final readonly class PublicHomeController
{
    public function __construct(private HomePageQuery $homePage) {}

    public function __invoke(): View
    {
        return view('home', [
            'homePage' => $this->homePage->get(),
        ]);
    }
}
