<?php

namespace App\Cms;

use App\Cms\Models\NewsPost;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

final class PublicNewsQuery
{
    /**
     * @return LengthAwarePaginator<int, NewsPost>
     */
    public function published(int $perPage = 10, ?DateTimeInterface $readTime = null): LengthAwarePaginator
    {
        $readTime ??= now();

        return $this->visibleAt($readTime)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, NewsPost>
     */
    public function latestPublished(int $limit = 3, ?DateTimeInterface $readTime = null): Collection
    {
        if ($limit < 1 || $limit > 10) {
            throw new InvalidArgumentException('Latest published news limit must be between 1 and 10.');
        }

        $readTime ??= now();

        return $this->visibleAt($readTime)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function findPublishedBySlug(string $slug, ?DateTimeInterface $readTime = null): ?NewsPost
    {
        $readTime ??= now();

        return $this->visibleAt($readTime)
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @return Builder<NewsPost>
     */
    private function visibleAt(DateTimeInterface $readTime): Builder
    {
        return NewsPost::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $readTime);
    }
}
