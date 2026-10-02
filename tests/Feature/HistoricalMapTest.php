<?php

namespace Tests\Feature;

use App\Models\Observation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalMapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function observation(Site $site, string $name, array $extra = []): Observation
    {
        return $site->observations()->create(array_replace(['author_id' => User::factory()->create()->id, 'content' => ['name_en' => $name, 'name_zh' => '测试地点', 'description_en' => 'Recorded visit', 'description_zh' => '观察', 'source_en' => '', 'source_zh' => '', 'category' => 'shrine', 'location_notes' => '', 'anonymous_credit' => true, 'credit' => ''], 'status' => 'approved', 'published_at' => now(), 'observed_from' => '2025-01-01', 'date_precision' => 'exact', 'condition' => 'intact', 'location_visibility' => 'exact', 'latitude' => 25.1234567, 'longitude' => 102.1234567], $extra));
    }

    public function test_history_uses_latest_eligible_record_then_filters_and_does_not_leak_future_positions(): void
    {
        $site = Site::create(['reference' => 'QA-history']);
        $this->observation($site, 'Original shrine');
        $this->observation($site, 'Demolished shrine', ['observed_from' => '2025-06-01', 'condition' => 'demolished', 'latitude' => 26.7654321]);
        $this->get('/atlas?as_of=2025-03-01')->assertOk()->assertSee('Original shrine')->assertDontSee('Demolished shrine')->assertDontSee('26.7654321');
        $this->get('/atlas?condition=intact')->assertOk()->assertDontSee('Original shrine')->assertDontSee('Demolished shrine');
        $this->get('/atlas?as_of=2025-03-01&condition=intact')->assertSee('Original shrine');
        $this->get('/sites/'.$site->id.'?as_of=2025-03-01')->assertOk()->assertSee('Original shrine')->assertDontSee('Demolished shrine');
        $this->get('/sites/'.$site->id)->assertSee('Original shrine')->assertSee('Demolished shrine');
        $this->get('/sites/'.$site->id.'?as_of=2024-01-01')->assertNotFound();
    }

    public function test_uncertain_dates_and_private_coordinates_are_excluded_from_map_payload(): void
    {
        $undated = Site::create(['reference' => 'QA-undated']);
        $this->observation($undated, 'Undated site', ['observed_from' => null, 'date_precision' => 'unknown']);
        $range = Site::create(['reference' => 'QA-range']);
        $this->observation($range, 'Range site', ['observed_from' => '2025-01-01', 'observed_to' => '2025-06-01', 'date_precision' => 'range']);
        $private = Site::create(['reference' => 'QA-private']);
        $this->observation($private, 'Private location', ['latitude' => 24.9876543, 'longitude' => 101.9876543, 'location_visibility' => 'approximate']);
        $pending = Site::create(['reference' => 'QA-pending']);
        $this->observation($pending, 'Pending secret', ['status' => 'pending', 'published_at' => null, 'latitude' => 23.9876543]);
        $this->get('/atlas?as_of=2025-03-01')->assertOk()->assertDontSee('Undated site')->assertDontSee('Range site')->assertSee('Private location')->assertDontSee('24.9876543')->assertDontSee('101.9876543')->assertDontSee('Pending secret')->assertDontSee('23.9876543');
        $this->get('/atlas?as_of=2025-06-01')->assertSee('Range site');
        $this->get('/atlas')->assertSee('Undated site');
    }

    public function test_coordinate_prefill_and_history_validation(): void
    {
        $this->get('/observations/create?lat=25.2&lng=102.3')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/observations/create?lat=25.2&lng=102.3')->assertOk()->assertSee('value="25.2"', false)->assertSee('value="102.3"', false);
        $this->get('/atlas?as_of=invalid')->assertSessionHasErrors('as_of');
        $this->get('/observations/create?lat=100&lng=102')->assertSessionHasErrors('lat');
    }
}
