<?php

namespace Tests\Feature;

use App\Models\Observation;
use App\Models\Photograph;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotographsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    private function data($file = null): array
    {
        return ['name_en' => 'Photo test', 'description_en' => 'Preservation photograph', 'category' => 'shrine', 'condition' => 'intact', 'date_precision' => 'unknown', 'location_visibility' => 'approximate', 'anonymous_credit' => 1, 'photos' => [['file' => $file ?? UploadedFile::fake()->image('photo.jpg', 100, 80), 'caption_en' => 'Photographic evidence', 'caption_zh' => '照片证据', 'credit' => 'Field researcher', 'permission' => 1]]];
    }

    public function test_photos_remain_private_until_attachment_and_observation_approval(): void
    {
        $author = User::factory()->create();
        $moderator = User::factory()->create(['role' => 'moderator']);
        $stranger = User::factory()->create();
        $this->actingAs($author)->post('/observations', $this->data())->assertSessionHasNoErrors();
        $photo = Photograph::firstOrFail();
        $obs = $photo->observation;
        Storage::disk('local')->assertExists($photo->original_path);
        Storage::disk('local')->assertExists($photo->web_path);
        $this->get('/photographs/'.$photo->id)->assertNotFound();
        $this->get('/private/photographs/'.$photo->id)->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->get('/private/photographs/'.$photo->id.'/original')->assertForbidden();
        $this->actingAs($stranger)->get('/private/photographs/'.$photo->id)->assertForbidden();
        $this->post('/private/photographs/'.$photo->id.'/review', ['status' => 'approved', 'checks' => 1, 'scan_evidence' => 'Pretend scan'])->assertForbidden();
        $this->actingAs($moderator)->get('/observations/'.$obs->id)->assertOk()->assertSee('Manual scan evidence');
        $this->post('/observations/'.$obs->id.'/decision', ['decision' => 'approved'])->assertSessionHasErrors('decision');
        $this->post('/private/photographs/'.$photo->id.'/review', ['status' => 'approved'])->assertSessionHasErrors(['scan_evidence', 'checks']);
        $this->post('/private/photographs/'.$photo->id.'/review', ['status' => 'approved', 'checks' => 1, 'scan_evidence' => 'Test-only evidence for '.$photo->sha256])->assertSessionHasNoErrors();
        $this->get('/photographs/'.$photo->id)->assertNotFound();
        $this->post('/observations/'.$obs->id.'/decision', ['decision' => 'approved'])->assertSessionHasNoErrors();
        $this->post('/logout');
        $this->get('/photographs/'.$photo->id)->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->get('/sites/'.$obs->site_id)->assertOk()->assertSee('Photographic evidence')->assertDontSee($photo->original_path)->assertDontSee($photo->scan_evidence);
    }

    public function test_moderator_photos_cannot_bypass_checks_and_rejection_blocks_publication(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $this->actingAs($moderator)->post('/observations', $this->data())->assertSessionHasNoErrors();
        $obs = Observation::firstOrFail();
        $photo = Photograph::firstOrFail();
        $this->assertSame('pending', $obs->status);
        $this->post('/private/photographs/'.$photo->id.'/review', ['status' => 'rejected', 'scan_evidence' => 'Permission not established'])->assertSessionHasNoErrors();
        $this->post('/observations/'.$obs->id.'/decision', ['decision' => 'approved'])->assertSessionHasErrors('decision');
        $this->post('/observations/'.$obs->id.'/decision', ['decision' => 'changes_requested', 'reason' => 'Replace photograph'])->assertSessionHasNoErrors();
        $this->get('/photographs/'.$photo->id)->assertNotFound();
    }

    public function test_invalid_images_and_missing_permission_create_no_records_or_files(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/observations', $this->data(UploadedFile::fake()->createWithContent('evil.jpg', '<script>not an image</script>')))->assertSessionHasErrors();
        $data = $this->data();
        $data['photos'][0]['permission'] = 0;
        $this->post('/observations', $data)->assertSessionHasErrors();
        $this->assertDatabaseCount('observations', 0);
        $this->assertDatabaseCount('photographs', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }
}
