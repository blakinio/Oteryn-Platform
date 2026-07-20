<?php

namespace Tests\Feature\Cms;

use App\Admin\AdminRoleManager;
use App\Cms\Models\ManagedPage;
use App\Cms\Models\NewsPost;
use App\Identity\Models\Identity;
use App\Identity\Sessions\WebSessionState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminCmsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_editor_can_create_draft_then_publish_news_with_audit(): void
    {
        $actor = $this->createIdentity('news-editor@example.com');
        $this->assignRole($actor, AdminRoleManager::CONTENT_EDITOR);
        $this->actingAsCurrent($actor);

        $this->post(route('admin.news.store'), [
            'slug' => 'phase-six-news',
            'title' => 'Phase Six News',
            'body' => 'Draft body',
            'published_at' => null,
        ])->assertRedirect();

        $post = NewsPost::query()->where('slug', 'phase-six-news')->firstOrFail();
        $this->get(route('news.show', ['slug' => $post->slug]))->assertNotFound();
        $this->assertDatabaseHas('admin_audit_events', [
            'actor_identity_id' => $actor->id,
            'action' => 'cms.news_created',
            'target_type' => 'news_post',
            'target_id' => (string) $post->id,
        ]);

        $publishedAt = now()->subMinute()->format('Y-m-d H:i:s');
        $this->put(route('admin.news.update', $post), [
            'slug' => 'phase-six-news',
            'title' => 'Phase Six News Published',
            'body' => 'Published body',
            'published_at' => $publishedAt,
        ])->assertRedirect(route('admin.news.edit', $post));

        $this->get(route('news.show', ['slug' => $post->slug]))
            ->assertOk()
            ->assertSeeText('Phase Six News Published')
            ->assertSeeText('Published body');
        $this->assertDatabaseHas('admin_audit_events', [
            'actor_identity_id' => $actor->id,
            'action' => 'cms.news_updated',
            'target_type' => 'news_post',
            'target_id' => (string) $post->id,
        ]);
    }

    public function test_content_editor_can_manage_published_plain_text_page_with_escaped_output_and_audit(): void
    {
        $actor = $this->createIdentity('page-editor@example.com');
        $this->assignRole($actor, AdminRoleManager::CONTENT_EDITOR);
        $this->actingAsCurrent($actor);

        $this->post(route('admin.pages.store'), [
            'slug' => 'about-oteryn',
            'title' => '<script>alert("title")</script>',
            'body' => '<img src=x onerror=alert("body")>',
            'published_at' => now()->subMinute()->format('Y-m-d H:i:s'),
        ])->assertRedirect();

        $page = ManagedPage::query()->where('slug', 'about-oteryn')->firstOrFail();

        $this->get(route('pages.show', ['slug' => $page->slug]))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;title&quot;)&lt;/script&gt;', false)
            ->assertSee('&lt;img src=x onerror=alert(&quot;body&quot;)&gt;', false)
            ->assertDontSee('<script>', false)
            ->assertDontSee('<img src=x', false);

        $this->assertDatabaseHas('admin_audit_events', [
            'actor_identity_id' => $actor->id,
            'action' => 'cms.page_created',
            'target_type' => 'managed_page',
            'target_id' => (string) $page->id,
        ]);
    }

    public function test_public_managed_page_hides_drafts_and_future_scheduled_pages(): void
    {
        ManagedPage::query()->create([
            'slug' => 'draft-page',
            'title' => 'Draft',
            'body' => 'Draft body',
            'published_at' => null,
        ]);
        ManagedPage::query()->create([
            'slug' => 'future-page',
            'title' => 'Future',
            'body' => 'Future body',
            'published_at' => now()->addHour(),
        ]);

        $this->get(route('pages.show', ['slug' => 'draft-page']))->assertNotFound();
        $this->get(route('pages.show', ['slug' => 'future-page']))->assertNotFound();
    }

    public function test_security_admin_without_cms_permission_cannot_mutate_news_or_pages(): void
    {
        $actor = $this->createIdentity('security-only@example.com');
        $this->assignRole($actor, AdminRoleManager::SECURITY_ADMIN);
        $this->actingAsCurrent($actor);

        $this->post(route('admin.news.store'), [
            'slug' => 'denied-news',
            'title' => 'Denied',
            'body' => 'Denied',
        ])->assertForbidden();

        $this->post(route('admin.pages.store'), [
            'slug' => 'denied-page',
            'title' => 'Denied',
            'body' => 'Denied',
        ])->assertForbidden();

        self::assertSame(0, NewsPost::query()->where('slug', 'denied-news')->count());
        self::assertSame(0, ManagedPage::query()->where('slug', 'denied-page')->count());
    }

    public function test_cms_permission_does_not_bypass_confirmed_mfa_requirement(): void
    {
        $actor = $this->createIdentity('editor-no-mfa@example.com', false);
        $this->assignRole($actor, AdminRoleManager::CONTENT_EDITOR);
        $this->actingAsCurrent($actor);

        $this->post(route('admin.news.store'), [
            'slug' => 'mfa-denied',
            'title' => 'Denied',
            'body' => 'Denied',
        ])->assertForbidden();

        self::assertSame(0, NewsPost::query()->where('slug', 'mfa-denied')->count());
    }

    private function createIdentity(string $email, bool $confirmedMfa = true): Identity
    {
        $identity = Identity::query()->create([
            'email' => $email,
            'password' => Hash::make('Correct-Horse-9!Battery'),
        ]);

        if ($confirmedMfa) {
            $identity->forceFill([
                'two_factor_secret' => 'TEST-MFA-SECRET-NOT-REAL',
                'two_factor_confirmed_at' => now(),
            ])->save();
        }

        return $identity;
    }

    private function assignRole(Identity $identity, string $roleKey): void
    {
        $roleId = DB::table('admin_roles')->where('key', $roleKey)->value('id');

        if (! is_int($roleId) && ! (is_string($roleId) && ctype_digit($roleId))) {
            self::fail('Expected an integer-compatible administrator role id.');
        }

        DB::table('identity_admin_roles')->insert([
            'identity_id' => $identity->id,
            'role_id' => (int) $roleId,
        ]);
    }

    private function actingAsCurrent(Identity $identity): void
    {
        $currentIdentity = Identity::query()->findOrFail($identity->id);

        $this->actingAs($identity, 'web')
            ->withSession([WebSessionState::GENERATION_KEY => $currentIdentity->web_session_generation]);
    }
}
