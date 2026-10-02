<?php

namespace Tests\Feature;

use App\Models\InterfaceTranslation;
use App\Models\User;
use Database\Seeders\InterfaceTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_reads_both_languages_from_database(): void
    {
        $this->seed(InterfaceTranslationSeeder::class);
        $this->get('/')->assertOk()->assertSee('Small places. Lasting records.');
        $this->get('/?lang=zh-Hans')->assertOk()->assertSee('微小的空间，长久的记录。');
        $this->get('/?lang=unsupported')->assertNotFound();
    }

    public function test_manual_translation_changes_survive_reseeding_and_are_escaped(): void
    {
        $this->seed(InterfaceTranslationSeeder::class);
        InterfaceTranslation::where('key', 'home.headline')->where('locale', 'en')->update(['value' => '<script>example</script>']);
        $this->seed(InterfaceTranslationSeeder::class);
        $this->get('/')->assertSee('&lt;script&gt;example&lt;/script&gt;', false)->assertDontSee('<script>example</script>', false);
    }

    public function test_new_users_default_to_contributor_and_role_cannot_be_mass_assigned(): void
    {
        $user = User::create(['name' => 'Researcher', 'email' => 'researcher@example.test', 'password' => 'test-password-only', 'role' => 'admin']);
        $this->assertSame('contributor', $user->fresh()->role);
    }
}
