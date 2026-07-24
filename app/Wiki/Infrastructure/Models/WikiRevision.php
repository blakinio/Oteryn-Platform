<?php

namespace App\Wiki\Infrastructure\Models;

use App\Wiki\Domain\WikiContentRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * @property int $id
 * @property int $article_id
 * @property string $locale
 * @property int $revision_number
 * @property int $article_version
 * @property string $title
 * @property string $slug
 * @property string $summary
 * @property string $source_markdown
 * @property int|null $editor_identity_id
 * @property string|null $change_note
 * @property int|null $source_revision_id
 * @property Carbon $created_at
 */
final class WikiRevision extends Model
{
    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'article_id',
        'locale',
        'revision_number',
        'article_version',
        'title',
        'slug',
        'summary',
        'source_markdown',
        'editor_identity_id',
        'change_note',
        'source_revision_id',
        'created_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $revision): void {
            WikiContentRules::assertArticleTranslation(
                $revision->locale,
                $revision->title,
                $revision->slug,
                $revision->summary,
                $revision->source_markdown,
            );

            $revision->created_at ??= now();
        });

        static::updating(static function (): never {
            throw new LogicException('Wiki revisions are append-only and cannot be updated.');
        });

        static::deleting(static function (): never {
            throw new LogicException('Wiki revisions are append-only and cannot be deleted.');
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'revision_number' => 'integer',
            'article_version' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
