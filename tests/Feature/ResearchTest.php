<?php

namespace Tests\Feature;

use App\Models\Observation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResearchTest extends TestCase
{
    use RefreshDatabase;

    private function record(array $extra = [], ?Site $site = null): Observation
    {
        $site ??= Site::create(['reference' => 'YDA-'.fake()->uuid()]);

        return $site->observations()->create(array_replace(['author_id' => User::factory()->create()->id, 'content' => ['name_en' => '=1+1', 'name_zh' => '测试地点', 'description_en' => "A quote \" and comma,\nand new line", 'description_zh' => '观察', 'source_en' => '', 'source_zh' => '', 'category' => 'shrine', 'location_notes' => '', 'anonymous_credit' => true, 'credit' => 'Hidden name'], 'status' => 'approved', 'published_at' => now(), 'observed_from' => '2025-01-01', 'date_precision' => 'exact', 'condition' => 'intact', 'location_visibility' => 'exact', 'latitude' => 25.1234567, 'longitude' => 102.1234567], $extra));
    }

    public function test_exports_use_latest_filtered_history_and_hide_nonpublic_data(): void
    {
        $old = $this->record();
        $new = $this->record(['observed_from' => '2025-06-01', 'condition' => 'demolished'], $old->site);
        $private = $this->record(['location_visibility' => 'approximate', 'latitude' => 24.7654321]);
        $pending = $this->record(['status' => 'pending', 'published_at' => null]);
        $this->record(['status' => 'approved', 'published_at' => null]);
        $response = $this->get('/atlas/export/geojson?as_of=2025-03-01&condition=intact');
        $response->assertOk()->assertDownload();
        $body = $response->streamedContent();
        $data = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        $features = collect($data['features'])->keyBy('id');
        $this->assertCount(2, $features);
        $this->assertTrue($features->has($old->id));
        $this->assertFalse($features->has($new->id));
        $this->assertFalse($features->has($pending->id));
        $this->assertSame([102.1234567, 25.1234567], $features[$old->id]['geometry']['coordinates']);
        $this->assertNull($features[$private->id]['geometry']);
        $this->assertNull($features[$private->id]['properties']['latitude']);
        $this->assertStringNotContainsString('24.7654321', $body);
        $this->assertStringNotContainsString('Hidden name', $body);
        $this->assertStringNotContainsString('author_id', $body);
        $this->assertSame('测试地点', $features[$old->id]['properties']['name_zh']);
        $this->assertSame('=1+1', $features[$old->id]['properties']['name_en']);
        $current = json_decode($this->get('/atlas/export/geojson?condition=intact')->streamedContent(), true);
        $this->assertCount(1, $current['features']);
        $this->get('/atlas/export/csv?as_of=bad')->assertSessionHasErrors('as_of');
        $this->get('/atlas/export/csv?category=invalid')->assertSessionHasErrors('category');
        $this->get('/atlas/export/xml')->assertNotFound();
    }

    public function test_csv_preserves_bilingual_multiline_cells_and_exports_beyond_pagination(): void
    {
        for ($i = 0; $i < 21; $i++) {
            $this->record();
        }
        $response = $this->get('/atlas/export/csv?page=2');
        $response->assertOk()->assertDownload();
        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, substr($csv, 3));
        rewind($stream);
        $headers = fgetcsv($stream, escape: '');
        $rows = [];
        while (($cells = fgetcsv($stream, escape: '')) !== false) {
            $rows[] = array_combine($headers, $cells);
        }
        fclose($stream);
        $this->assertCount(21, $rows);
        $this->assertSame("'=1+1", $rows[0]['name_en']);
        $this->assertSame('测试地点', $rows[0]['name_zh']);
        $this->assertSame("A quote \" and comma,\nand new line", $rows[0]['description_en']);
        $empty = $this->get('/atlas/export/csv?search=not-present')->streamedContent();
        $this->assertStringContainsString('observation_id', $empty);
        $this->assertStringNotContainsString('测试地点', $empty);
    }

    public function test_citation_is_public_only_and_identifies_the_exact_observation(): void
    {
        $this->seed();
        $record = $this->record();
        $response = $this->get('/citations/'.$record->id.'?lang=zh-Hans');
        $response->assertOk()->assertDownload()->assertSee('访问日期')->assertSee('#observation-'.$record->id)->assertDontSee('Hidden name');
        $this->get('/sites/'.$record->site_id.'?lang=en')->assertSee('Cite this observation')->assertSee('/citations/'.$record->id, false);
        $pending = $this->record(['status' => 'pending', 'published_at' => null]);
        $this->get('/citations/'.$pending->id)->assertNotFound();
        $unpublished = $this->record(['published_at' => null]);
        $this->get('/citations/'.$unpublished->id)->assertNotFound();
    }
}
