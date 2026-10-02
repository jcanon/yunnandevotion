<?php

namespace Tests\Feature;

use App\Http\Controllers\ContentAdminController;
use App\Models\InterfaceTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentAdminTest extends TestCase
{
    use RefreshDatabase;

    private function payload(string $key): array
    {
        $rows = InterfaceTranslation::where('key', $key)->get();

        return ['key' => $key, 'version' => ContentAdminController::version($rows), 'en' => $rows->firstWhere('locale', 'en')->value, 'zh' => $rows->firstWhere('locale', 'zh-Hans')->value];
    }

    public function test_only_verified_active_admins_can_edit_and_changes_are_bilingual_escaped_and_survive_seeding(): void
    {
        $this->seed();
        $this->get('/admin/content')->assertRedirect('/login');
        foreach (['contributor', 'moderator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get('/admin/translations')->assertForbidden();
            $this->patch('/admin/content', $this->payload('home.intro'))->assertForbidden();
        }
        $this->actingAs(User::factory()->unverified()->create(['role' => 'admin']))->get('/admin/content')->assertRedirect('/email/verify');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $payload = array_replace($this->payload('home.funding'), ['en' => '<script>Funding</script>', 'zh' => '已确认的资助']);
        $this->patch('/admin/content', $payload)->assertSessionHasNoErrors();
        $this->seed();
        $this->get('/?lang=en')->assertSee('&lt;script&gt;Funding&lt;/script&gt;', false)->assertDontSee('<script>Funding</script>', false);
        $this->get('/?lang=zh-Hans')->assertSee('已确认的资助');
    }

    public function test_stale_edits_and_changed_placeholders_are_rejected_atomically(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $payload = $this->payload('home.intro');
        $this->patch('/admin/content', array_replace($payload, ['en' => 'New introduction']))->assertSessionHasNoErrors();
        $this->patch('/admin/content', array_replace($payload, ['en' => 'Stale introduction']))->assertSessionHasErrors('version');
        $this->assertDatabaseHas('interface_translations', ['key' => 'home.intro', 'locale' => 'en', 'value' => 'New introduction']);
        $tokens = $this->payload('content.tokens');
        $this->patch('/admin/content', array_replace($tokens, ['en' => 'Changed :placeholder text', 'zh' => 'Missing token']))->assertSessionHasErrors('zh');
        $this->assertDatabaseHas('interface_translations', ['key' => 'content.tokens', 'locale' => 'en', 'value' => $tokens['en']]);
        $this->patch('/admin/content', array_replace($payload, ['key' => 'invented.key']))->assertNotFound();
    }

    public function test_search_returns_both_languages_and_separates_home_content(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin/translations?search=language.label')->assertOk()->assertSee('Interface language')->assertSee('界面语言')->assertDontSee('name="key" value="home.intro"', false);
        $this->get('/admin/content?lang=zh-Hans')->assertOk()->assertSee('首页内容')->assertSee('name="key" value="home.intro"',false);
    }
}
