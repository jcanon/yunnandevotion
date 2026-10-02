<?php

namespace Tests\Feature;

use App\Models\Observation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function data(array $extra = []): array
    {
        return array_replace(['name_en' => 'Courtyard research example', 'name_zh' => '庭院研究示例', 'description_en' => 'First documented visit', 'description_zh' => '首次记录', 'source_en' => 'Field notebook', 'category' => 'shrine', 'condition' => 'intact', 'date_precision' => 'exact', 'observed_from' => '2025-01-01', 'latitude' => '25.1234567', 'longitude' => '102.7654321', 'location_visibility' => 'approximate', 'anonymous_credit' => 1], $extra);
    }

    public function test_private_review_revision_and_preserved_public_history(): void
    {
        $author = User::factory()->create();
        $reviewer = User::factory()->create(['role' => 'moderator']);
        $stranger = User::factory()->create();
        $this->actingAs($author)->get('/observations/create')->assertOk();
        $this->post('/observations', $this->data(['status' => 'approved']))->assertSessionHasNoErrors();
        $first = Observation::firstOrFail();
        $this->assertSame('pending', $first->status);
        $this->get('/sites/'.$first->site_id)->assertNotFound();
        $this->actingAs($stranger)->get('/observations/'.$first->id)->assertForbidden();
        $this->post('/observations/'.$first->id.'/decision', ['decision' => 'approved'])->assertForbidden();
        $this->actingAs($reviewer)->get('/moderation')->assertOk()->assertSee('Courtyard research example');
        $this->get('/observations/'.$first->id)->assertOk()->assertSee('25.1234567');
        $this->post('/observations/'.$first->id.'/decision', ['decision' => 'changes_requested'])->assertSessionHasErrors('reason');
        $this->post('/observations/'.$first->id.'/decision', ['decision' => 'changes_requested', 'reason' => 'Clarify source'])->assertSessionHasNoErrors();
        $this->actingAs($author)->get('/observations/create?revise='.$first->id)->assertOk()->assertSee('First documented visit');
        $this->post('/observations', $this->data(['site_id' => $first->site_id, 'revises_id' => $first->id, 'source_en' => 'Notebook page 4']))->assertSessionHasNoErrors();
        $revision = Observation::where('revises_id', $first->id)->firstOrFail();
        $this->actingAs($reviewer)->post('/observations/'.$revision->id.'/decision', ['decision' => 'approved'])->assertSessionHasNoErrors();
        $this->post('/observations/'.$revision->id.'/decision', ['decision' => 'rejected', 'reason' => 'Second action'])->assertStatus(409);
        $this->post('/logout');
        $this->get('/sites/'.$first->site_id)->assertOk()->assertSee('Notebook page 4')->assertDontSee('25.1234567')->assertDontSee('102.7654321')->assertDontSee($author->email);
        $this->actingAs($reviewer)->post('/observations', $this->data(['site_id' => $first->site_id, 'observed_from' => '2025-06-01', 'condition' => 'demolished', 'description_en' => 'Removed on subsequent visit']))->assertSessionHasNoErrors();
        $this->post('/logout');
        $this->get('/sites/'.$first->site_id)->assertOk()->assertSee('Removed on subsequent visit')->assertSee('First documented visit');
        $this->assertSame('changes_requested', $first->fresh()->status);
        $this->assertSame('Field notebook', $first->fresh()->content['source_en']);
        $this->assertDatabaseCount('sites', 1);
        $this->assertDatabaseCount('observations', 3);
    }

    public function test_dates_coordinates_language_and_permissions_are_validated(): void
    {
        $this->post('/observations', $this->data())->assertRedirect('/login');
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->post('/observations', $this->data())->assertRedirect('/email/verify');
        $user->markEmailAsVerified();
        $this->post('/observations', $this->data(['latitude' => 100]))->assertSessionHasErrors('latitude');
        $this->post('/observations', $this->data(['date_precision' => 'range', 'observed_to' => '2024-01-01']))->assertSessionHasErrors('observed_to');
        $this->post('/observations', $this->data(['name_en' => '', 'description_en' => '', 'name_zh' => '', 'description_zh' => '']))->assertSessionHasErrors('name_en');
        $this->post('/observations', $this->data(['name_en' => '', 'description_en' => '']))->assertSessionHasNoErrors();
        $this->assertSame('pending', Observation::first()->status);
    }

    public function test_published_content_cannot_be_overwritten(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $this->actingAs($moderator)->post('/observations', $this->data())->assertSessionHasNoErrors();
        $observation = Observation::firstOrFail();
        $this->expectException(\LogicException::class);
        $observation->update(['condition' => 'demolished']);
    }
}
