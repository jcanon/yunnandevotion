<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_svg_color_and_dynamic_submission_choices(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="red" d="M0 0 L24 24 L0 24 Z"/></svg>';
        $data = ['key' => 'temple', 'name_en' => 'Temple', 'name_zh' => '寺院', 'color' => '#aa2233', 'svg' => UploadedFile::fake()->createWithContent('marker.svg', $svg)];
        $this->post('/admin/categories', $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', ['key' => 'temple']);
        $this->get('/markers/temple')->assertOk()->assertSee('#aa2233')->assertDontSee('red');
        $this->patch('/admin/categories/temple', ['key' => 'renamed', 'name_en' => 'Temple revised', 'name_zh' => '寺院', 'color' => '#224455'])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('categories', ['key' => 'renamed']);
        $this->get('/markers/temple')->assertSee('#224455')->assertDontSee('#aa2233');
        $this->get('/observations/create')->assertSee('value="temple"', false)->assertSee('Temple revised');
        $this->get('/atlas?category=temple')->assertOk();
        $this->get('/atlas/export/geojson?category=temple')->assertOk();
    }

    public function test_svg_active_content_is_rejected_and_non_admin_cannot_modify_assets(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create(['role' => 'moderator']))->get('/admin/assets')->assertForbidden();
        $this->post('/admin/categories', [])->assertForbidden();
        $this->post('/admin/homepage-images', [])->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['<script>alert(1)</script>', '<image href="https://example.test/x"/>', '<path onload="alert(1)"/>', '<path fill="url(https://example.test/x)"/>', '<foreignObject/>'] as $bad) {
            $this->patch('/admin/categories/shrine', ['name_en' => 'Changed', 'name_zh' => '变化', 'color' => '#223344', 'svg' => UploadedFile::fake()->createWithContent('x.svg', '<svg viewBox="0 0 24 24">'.$bad.'</svg>')])->assertSessionHasErrors('svg');
        }
        $this->assertSame('Shrine', Category::find('shrine')->name_en);
    }

    public function test_homepage_images_are_reencoded_localized_and_removable(): void
    {
        $this->seed();
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = ['image' => UploadedFile::fake()->image('test.png', 100, 60), 'alt_en' => 'Fictional image', 'alt_zh' => '虚构图片', 'credit' => 'QA', 'permission' => '1'];
        $this->post('/admin/homepage-images', $data)->assertSessionHasNoErrors();
        $row = DB::table('homepage_images')->first();
        $this->assertNotNull($row);
        $this->assertStringStartsWith("\xff\xd8", Storage::disk('local')->get($row->path));
        $this->get('/?lang=en')->assertSee('alt="Fictional image"', false);
        $this->get('/?lang=zh-Hans')->assertSee('alt="虚构图片"', false);
        $this->get('/homepage-images/'.$row->id)->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->delete('/admin/homepage-images/'.$row->id)->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($row->path);
        $this->get('/homepage-images/'.$row->id)->assertNotFound();
        $this->post('/admin/homepage-images', array_replace($data, ['permission' => '0']))->assertSessionHasErrors('permission');
        $this->assertDatabaseCount('homepage_images',0);
    }
}
