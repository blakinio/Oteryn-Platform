<?php

namespace App\Cms\Actions;

use App\Audit\AdminAuditRecorder;
use App\Cms\Models\ManagedPage;
use App\Identity\Models\Identity;
use Illuminate\Support\Facades\DB;

final class SaveManagedPage
{
    public function __construct(private readonly AdminAuditRecorder $audit) {}

    public function execute(
        Identity $actor,
        ?ManagedPage $page,
        string $slug,
        string $title,
        string $body,
        ?string $publishedAt,
    ): ManagedPage {
        return DB::transaction(function () use ($actor, $page, $slug, $title, $body, $publishedAt): ManagedPage {
            $created = $page === null;
            $page ??= new ManagedPage;
            $page->fill([
                'slug' => $slug,
                'title' => $title,
                'body' => $body,
                'published_at' => $publishedAt,
            ]);
            $page->save();

            $this->audit->record(
                $actor->id,
                $created ? 'cms.page_created' : 'cms.page_updated',
                'managed_page',
                (string) $page->id,
                [
                    'slug' => $page->slug,
                    'published' => $page->published_at !== null,
                ],
            );

            return $page;
        }, 3);
    }
}
