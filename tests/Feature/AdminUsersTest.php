<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_users_but_cannot_remove_last_admin(): void
    {
        $this->seed();
        $admin = User::factory()->create(['role' => 'admin']);
        $contributor = User::factory()->create();
        $this->actingAs($contributor)->get('/admin/users')->assertForbidden();
        $this->patch('/admin/users/'.$admin->id, ['role' => 'contributor', 'active' => 0])->assertForbidden();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->patch('/admin/users/'.$contributor->id, ['role' => 'moderator', 'active' => 1])->assertSessionHasNoErrors();
        $this->assertSame('moderator', $contributor->fresh()->role);
        $this->patch('/admin/users/'.$admin->id, ['role' => 'contributor', 'active' => 1])->assertSessionHasErrors('role');
        $this->patch('/admin/users/'.$admin->id, ['role' => 'admin', 'active' => 0])->assertSessionHasErrors('role');
        $this->patch('/admin/users/'.$contributor->id, ['role' => 'moderator', 'active' => 0])->assertSessionHasNoErrors();
        $this->assertFalse($contributor->fresh()->active);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_first_admin_command_requires_verification_and_cannot_be_reused(): void
    {
        $user = User::factory()->unverified()->create();
        $this->artisan('yda:first-admin', ['email' => $user->email])->assertFailed();
        $user->markEmailAsVerified();
        $this->artisan('yda:first-admin', ['email' => $user->email])->assertSuccessful();
        $this->assertSame('admin', $user->fresh()->role);
        $other = User::factory()->create();
        $this->artisan('yda:first-admin', ['email' => $other->email])->assertFailed();
        $this->assertSame('contributor', $other->fresh()->role);
    }
}
