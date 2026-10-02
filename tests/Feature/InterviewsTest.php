<?php

namespace Tests\Feature;

use App\Models\Interview;
use App\Models\User;
use App\Services\TimedCaptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InterviewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    private function file()
    {
        return UploadedFile::fake()->createWithContent('interview.webm', file_get_contents(base_path('tests/Fixtures/interview.webm')));
    }

    private function createVideo(User $user): Interview
    {
        $this->actingAs($user)->post('/observations', ['name_en' => 'Interview test', 'description_en' => 'Fictional interview', 'category' => 'shrine', 'condition' => 'intact', 'date_precision' => 'unknown', 'location_visibility' => 'approximate', 'anonymous_credit' => 1, 'interview' => ['file' => $this->file(), 'title_en' => 'Interview', 'credit' => 'QA contributor', 'permission' => 1]])->assertSessionHasNoErrors();

        return Interview::firstOrFail();
    }

    private function prepared(): array
    {
        return ['web_file' => $this->file(), 'duration_seconds' => 2, 'transcript_en' => 'Fictional transcript', 'transcript_zh' => '虚构文字稿', 'captions_en' => "WEBVTT\n\n00:00:00.000 --> 00:00:01.000\nFictional caption", 'captions_zh' => "WEBVTT\n\n00:00:00.000 --> 00:00:01.000\n虚构字幕"];
    }

    public function test_interview_approval_private_access_captions_and_video_ranges(): void
    {
        $author = User::factory()->create();
        $moderator = User::factory()->create(['role' => 'moderator']);
        $other = User::factory()->create();
        $video = $this->createVideo($author);
        $obs = $video->observation;
        $this->get('/interviews/'.$video->id)->assertNotFound();
        $this->get('/private/interviews/'.$video->id.'/original')->assertForbidden();
        $this->actingAs($other)->get('/private/interviews/'.$video->id)->assertForbidden();
        $this->post('/private/interviews/'.$video->id.'/prepare', $this->prepared())->assertForbidden();
        $this->actingAs($moderator)->get('/observations/'.$obs->id)->assertOk()->assertSee('Prepare web video');
        $this->post('/observations/'.$obs->id.'/decision', ['decision' => 'approved'])->assertSessionHasErrors('decision');
        $this->post('/private/interviews/'.$video->id.'/review', ['status' => 'approved', 'checks' => 1, 'scan_evidence' => 'TEST evidence'])->assertSessionHasErrors('status');
        $this->post('/private/interviews/'.$video->id.'/prepare', $this->prepared())->assertSessionHasNoErrors();
        $this->get('/private/interviews/'.$video->id)->assertOk()->assertHeader('Content-Type', 'video/webm');
        $this->get('/interviews/'.$video->id.'/captions/en')->assertNotFound();
        $this->post('/private/interviews/'.$video->id.'/review', ['status' => 'approved', 'checks' => 1, 'scan_evidence' => 'TEST: manual fixture review'])->assertSessionHasNoErrors();
        $this->get('/interviews/'.$video->id)->assertNotFound();
        $this->post('/observations/'.$obs->id.'/decision', ['decision' => 'approved'])->assertSessionHasNoErrors();
        $this->post('/logout');
        $this->get('/interviews/'.$video->id.'/captions/zh')->assertOk()->assertHeader('Content-Type', 'text/vtt; charset=utf-8')->assertSee('虚构字幕');
        $this->get('/interviews/'.$video->id, ['Range' => 'bytes=0-99'])->assertStatus(206)->assertHeader('Content-Range', 'bytes 0-99/'.strlen(file_get_contents(base_path('tests/Fixtures/interview.webm'))));
        $this->get('/sites/'.$obs->site_id)->assertOk()->assertSee('Fictional transcript')->assertDontSee($video->original_path);
        $this->actingAs($moderator)->post('/private/interviews/'.$video->id.'/prepare', $this->prepared())->assertStatus(409);
    }

    public function test_caption_validation_duration_and_rejection(): void
    {
        $user = User::factory()->create(['role' => 'moderator']);
        $video = $this->createVideo($user);
        $this->assertSame('pending', $video->observation->status);
        $data = $this->prepared();
        $data['duration_seconds'] = 601;
        $this->post('/private/interviews/'.$video->id.'/prepare', $data)->assertSessionHasErrors('duration_seconds');
        $data = $this->prepared();
        $data['captions_en'] = "WEBVTT\n\n00:00:00.000 --> 00:00:01.000\n<script>bad</script>";
        $this->post('/private/interviews/'.$video->id.'/prepare', $data)->assertSessionHasErrors('captions_en');
        $this->assertFalse(TimedCaptions::valid("WEBVTT\n\n00:00:01.000 --> 00:00:03.000\nPast end", 2));
        $this->post('/private/interviews/'.$video->id.'/review', ['status' => 'rejected', 'scan_evidence' => 'Consent needs clarification'])->assertSessionHasNoErrors();
        $this->post('/observations/'.$video->observation_id.'/decision', ['decision' => 'approved'])->assertSessionHasErrors('decision');
        $this->get('/interviews/'.$video->id)->assertNotFound();
    }
}
