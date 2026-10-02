<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use App\Notifications\AccountInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvitationsProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Notification::fake();
    }

    private function invite(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post('/admin/invitations', ['name' => 'Invited scholar', 'email' => 'INVITED@example.test', 'role' => 'moderator', 'locale' => 'zh-Hans'])->assertSessionHasNoErrors();
        $token = null;
        Notification::assertSentOnDemand(AccountInvitation::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        return [Invitation::first(), $token, $admin];
    }

    public function test_invitation_verifies_email_assigns_server_role_and_is_single_use(): void
    {
        [$invite,$token] = $this->invite();
        $this->assertSame(hash('sha256', $token), $invite->token_hash);
        $this->assertDatabaseMissing('users', ['email' => 'invited@example.test']);
        $this->post('/logout');
        $this->get('/invitation/'.$token.'?lang=zh-Hans')->assertOk()->assertSee('接受邀请');
        $this->post('/invitation/'.$token, ['password' => 'a-long-password', 'password_confirmation' => 'a-long-password', 'role' => 'admin', 'email' => 'attacker@example.test'])->assertRedirect('/profile');
        $user = User::where('email', 'invited@example.test')->firstOrFail();
        $this->assertSame('moderator', $user->role);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertNotNull($invite->fresh()->accepted_at);
        $this->post('/logout');
        $this->post('/invitation/'.$token, ['password' => 'a-long-password', 'password_confirmation' => 'a-long-password'])->assertStatus(410);
    }

    public function test_resend_expiry_revocation_duplicates_and_permissions(): void
    {
        [$invite,$old,$admin] = $this->invite();
        $this->post('/admin/invitations', ['name' => 'Duplicate', 'email' => 'invited@example.test', 'role' => 'admin', 'locale' => 'en'])->assertSessionHasErrors('email');
        $this->post('/admin/invitations/'.$invite->id.'/resend')->assertSessionHasNoErrors();
        $this->assertNotSame(hash('sha256', $old), $invite->fresh()->token_hash);
        $contributor = User::factory()->create();
        $this->actingAs($contributor)->post('/admin/invitations/'.$invite->id.'/resend')->assertForbidden();
        $this->delete('/admin/invitations/'.$invite->id)->assertForbidden();
        $this->post('/admin/invitations', [])->assertForbidden();
        $this->actingAs($admin)->delete('/admin/invitations/'.$invite->id)->assertSessionHasNoErrors();
        $this->assertNotNull($invite->fresh()->revoked_at);
        $this->post('/logout');
        $this->get('/invitation/'.$old)->assertStatus(410);
        $invite->update(['token_hash' => hash('sha256', 'expired'), 'revoked_at' => null, 'expires_at' => now()->subMinute()]);
        $this->post('/invitation/expired', ['password' => 'a-long-password', 'password_confirmation' => 'a-long-password'])->assertStatus(410);
    }

    public function test_profile_only_updates_current_users_editable_fields(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user)->patch('/profile', ['name' => 'Researcher', 'bio_en' => 'English biography', 'bio_zh' => '中文简介', 'locale' => 'zh-Hans', 'anonymous_credit' => 1, 'role' => 'admin', 'email' => 'changed@example.test', 'id' => $other->id])->assertRedirect('/profile');
        $user->refresh();
        $this->assertSame('contributor', $user->role);
        $this->assertSame('中文简介', $user->bio_zh);
        $this->assertTrue($user->anonymous_credit);
        $this->assertNotSame('Researcher', $other->fresh()->name);
        $this->assertNotSame('changed@example.test', $user->email);
        $this->get('/profile')->assertOk()->assertSee('资料已保存。')->assertSee('中文简介');
        $this->post('/logout');
        $this->patch('/profile',[])->assertRedirect('/login');
    }
}
