<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Notification::fake();
    }

    public function test_registration_verification_and_logout(): void
    {
        $this->get('/register?lang=zh-Hans')->assertOk()->assertSee('创建账户');
        $this->post('/register', ['name' => 'Researcher', 'email' => 'RESEARCHER@example.test', 'password' => 'a-long-password', 'password_confirmation' => 'a-long-password', 'role' => 'admin'])
            ->assertRedirect('/email/verify');
        $user = User::first();
        $this->assertSame('contributor', $user->role);
        $this->assertSame('zh-Hans', $user->locale);
        $this->assertTrue(Hash::check('a-long-password', $user->password));
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->get('/profile')->assertRedirect('/email/verify');
        $this->get('/email/verify/'.$user->id.'/wrong')->assertForbidden();
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($url)->assertRedirect('/profile');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->get('/profile')->assertOk();
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_limits_and_suspended_accounts(): void
    {
        $user = User::factory()->create(['password' => 'a-long-password']);
        $this->post('/login', ['email' => $user->email, 'password' => 'a-long-password'])->assertRedirect('/profile');
        $this->assertAuthenticatedAs($user);
        $user->forceFill(['active' => false])->save();
        $this->get('/profile')->assertRedirect('/login');
        $this->assertGuest();
        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'a-long-password'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_roles_are_enforced_on_server(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        foreach (['contributor' => [403, 403], 'moderator' => [403, 200], 'admin' => [200, 200]] as $role => [$admin,$moderation]) {
            $user = User::factory()->create();
            $user->forceFill(['role' => $role])->save();
            $this->actingAs($user)->get('/admin')->assertStatus($admin);
            $this->get('/moderation')->assertStatus($moderation);
        }
    }

    public function test_reset_has_generic_response_and_single_use_tokens(): void
    {
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        $known = session('status');
        $this->post('/forgot-password', ['email' => 'missing@example.test'])->assertSessionHas('status', $known);
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });
        $data = ['email' => $user->email, 'token' => $token, 'password' => 'replacement-password', 'password_confirmation' => 'replacement-password'];
        $this->post('/reset-password', $data)->assertRedirect('/login');
        $this->assertTrue(Hash::check('replacement-password', $user->fresh()->password));
        $this->post('/reset-password', $data)->assertSessionHasErrors('email');
    }

    public function test_validation_and_notification_content_are_bilingual(): void
    {
        $this->get('/register?lang=zh-Hans');
        $this->post('/register', ['email' => 'invalid', 'password' => 'short'])->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertSame('请输入有效的电子邮箱。', session('errors')->first('email'));
        $user = User::factory()->create(['locale' => 'zh-Hans']);
        $mail = (new VerifyEmail)->toMail($user);
        $this->assertSame('验证邮箱', $mail->subject);
        $this->assertStringContainsString('请验证邮箱', view($mail->view, $mail->viewData)->render());
        $expired = URL::temporarySignedRoute('verification.verify', now()->subMinute(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($expired)->assertForbidden();
    }
}
