<?php

namespace App\Cms;

use App\Cms\Models\ManagedPage;
use DateTimeInterface;

final class PublicPageQuery
{
    public function findPublishedBySlug(string $slug, ?DateTimeInterface $readTime = null): ?ManagedPage
    {
        $readTime ??= now();

        return ManagedPage::query()
            ->where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $readTime)
            ->first();
    }
}
