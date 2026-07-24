<?php

namespace App\Events\Http;

use App\Admin\AdminAuthorization;
use App\Admin\AdminPermission;
use App\Cms\Models\NewsPost;
use App\Events\Actions\ChangeEventStatus;
use App\Events\Actions\SaveEvent;
use App\Events\Models\Event;
use App\Events\Models\EventTranslation;
use App\Identity\Models\Identity;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminEventController
{
    public function __construct(private readonly AdminAuthorization $authorization) {}

    public function index(): View
    {
        return view('admin.events.index', [
            'events' => Event::query()
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->paginate(25),
        ]);
    }

    public function create(Request $request): View
    {
        return $this->form(null, $request);
    }

    public function store(EventRequest $request, SaveEvent $save): RedirectResponse
    {
        $identity = $request->user();
        abort_unless($identity instanceof Identity, 403);

        $event = $save->execute(
            $identity,
            null,
            $this->utc($request->string('starts_at')->toString()),
            $this->utc($request->string('ends_at')->toString()),
            $request->boolean('featured'),
            $request->filled('news_post_id') ? $request->integer('news_post_id') : null,
            $this->translations($request),
            null,
        );

        return redirect()
            ->route('admin.events.edit', $event)
            ->with('status', 'Event draft saved.');
    }

    public function edit(Request $request, Event $event): View
    {
        return $this->form($event, $request);
    }

    public function update(EventRequest $request, Event $event, SaveEvent $save): RedirectResponse
    {
        $identity = $request->user();
        abort_unless($identity instanceof Identity, 403);

        try {
            $saved = $save->execute(
                $identity,
                $event,
                $this->utc($request->string('starts_at')->toString()),
                $this->utc($request->string('ends_at')->toString()),
                $request->boolean('featured'),
                $request->filled('news_post_id') ? $request->integer('news_post_id') : null,
                $this->translations($request),
                $request->integer('lock_version'),
            );
        } catch (DomainException $exception) {
            abort(409, $exception->getMessage());
        }

        return redirect()
            ->route('admin.events.edit', $saved)
            ->with('status', 'Event draft saved. Publication approval is required again.');
    }

    public function status(
        EventStatusRequest $request,
        Event $event,
        ChangeEventStatus $changeStatus,
    ): RedirectResponse {
        $identity = $request->user();
        abort_unless($identity instanceof Identity, 403);

        try {
            $saved = $changeStatus->execute(
                $identity,
                $event,
                $request->string('status')->toString(),
                $request->integer('lock_version'),
            );
        } catch (DomainException $exception) {
            abort(409, $exception->getMessage());
        }

        return redirect()
            ->route('admin.events.edit', $saved)
            ->with('status', 'Event publication state changed.');
    }

    private function form(?Event $event, Request $request): View
    {
        $translations = $event === null
            ? collect()
            : EventTranslation::query()->where('event_id', $event->id)->get()->keyBy('locale');
        $identity = $request->user();

        return view('admin.events.form', [
            'event' => $event,
            'translations' => $translations,
            'canPublish' => $identity instanceof Identity
                && $this->authorization->allows($identity, AdminPermission::PUBLISH_EVENTS),
            'newsPosts' => NewsPost::query()
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->get(['id', 'title', 'slug', 'published_at']),
        ]);
    }

    /**
     * @return array<string, array{title: string, slug: string, summary: string, body: string}>
     */
    private function translations(EventRequest $request): array
    {
        $validated = $request->validated();
        $rawTranslations = $validated['translations'] ?? [];
        abort_unless(is_array($rawTranslations), 422);

        $translations = [];

        foreach (['en', 'pl'] as $locale) {
            $raw = $rawTranslations[$locale] ?? null;

            if (! is_array($raw)) {
                continue;
            }

            $title = trim((string) ($raw['title'] ?? ''));
            $slug = trim((string) ($raw['slug'] ?? ''));
            $summary = trim((string) ($raw['summary'] ?? ''));
            $body = trim((string) ($raw['body'] ?? ''));

            if ($locale === 'pl' && $title === '' && $slug === '' && $summary === '' && $body === '') {
                continue;
            }

            $translations[$locale] = [
                'title' => $title,
                'slug' => $slug,
                'summary' => $summary,
                'body' => $body,
            ];
        }

        abort_unless(isset($translations['en']), 422, 'An English event translation is required.');

        return $translations;
    }

    private function utc(string $value): CarbonImmutable
    {
        $date = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $value, 'UTC');
        abort_if($date === false, 422, 'The date must be a valid UTC date and time.');

        return $date;
    }
}
