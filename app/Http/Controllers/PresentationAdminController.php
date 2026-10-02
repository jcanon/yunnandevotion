<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\MarkerSvg;
use App\Support\Words;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PresentationAdminController extends Controller
{
    public function index()
    {
        return view('admin-assets', ['screen' => 'assets.title', 'categories' => Category::orderBy('key')->get(), 'photos' => DB::table('homepage_images')->orderBy('id')->get()]);
    }

    public function category(Request $request, ?Category $category = null)
    {
        $data = $request->validate(['key' => [$category ? 'nullable' : 'required', 'regex:/^[a-z][a-z0-9-]{0,59}$/', Rule::unique('categories', 'key')->ignore($category?->key, 'key')], 'name_en' => 'required|string|max:200', 'name_zh' => 'required|string|max:200', 'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'], 'svg' => 'nullable|file|max:50', 'reset' => 'nullable|boolean'], ['required' => Words::get('error.required'), 'max' => Words::get('error.long'), 'regex' => Words::get('assets.invalid'), 'unique' => Words::get('assets.invalid')]);
        $svg = $category?->svg;
        if ($request->boolean('reset')) {
            $svg = null;
        }
        if ($request->hasFile('svg')) {
            $svg = MarkerSvg::clean(file_get_contents($request->file('svg')->getRealPath()), $data['color']);
        } elseif ($svg) {
            $svg = MarkerSvg::clean($svg, $data['color']);
        }
        $values = ['name_en' => $data['name_en'], 'name_zh' => $data['name_zh'], 'color' => $data['color'], 'svg' => $svg];
        if ($category) {
            $category->update($values);
        } else {
            Category::create(['key' => $data['key']] + $values);
        }

        return back()->with('status', Words::get('content.saved'));
    }

    public function marker(Category $category)
    {
        return response($category->marker(), 200, ['Content-Type' => 'image/svg+xml', 'Content-Security-Policy' => "default-src 'none'; sandbox", 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'no-cache']);
    }

    public function upload(Request $request)
    {
        $data = $request->validate(['image' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=6000,max_height=6000', 'alt_en' => 'required|string|max:500', 'alt_zh' => 'required|string|max:500', 'credit' => 'required|string|max:200', 'permission' => 'accepted'], ['required' => Words::get('error.required'), 'accepted' => Words::get('photo.required'), 'max' => Words::get('photo.invalid'), 'dimensions' => Words::get('photo.invalid'), 'mimes' => Words::get('photo.invalid')]);
        $file = $request->file('image');
        $size = @getimagesize($file->getRealPath());
        if (! $size || $size[0] * $size[1] > 16000000) {
            throw ValidationException::withMessages(['image' => Words::get('photo.invalid')]);
        }
        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $source) {
            throw ValidationException::withMessages(['image' => Words::get('photo.invalid')]);
        }
        $scale = min(1, 1800 / max(imagesx($source), imagesy($source)));
        $web = imagecreatetruecolor(max(1, (int) (imagesx($source) * $scale)), max(1, (int) (imagesy($source) * $scale)));
        imagefill($web, 0, 0, imagecolorallocate($web, 255, 255, 255));
        imagecopyresampled($web, $source, 0, 0, 0, 0, imagesx($web), imagesy($web), imagesx($source), imagesy($source));
        ob_start();
        imagejpeg($web, null, 85);
        $bytes = ob_get_clean();
        imagedestroy($source);
        imagedestroy($web);
        $path = 'homepage/'.Str::ulid().'.jpg';
        try {
            if (! Storage::disk('local')->put($path, $bytes)) {
                throw new \RuntimeException('Image write failed');
            }
            DB::table('homepage_images')->insert(['path' => $path, 'alt_en' => $data['alt_en'], 'alt_zh' => $data['alt_zh'], 'credit' => $data['credit'], 'created_at' => now(), 'updated_at' => now()]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return back()->with('status', Words::get('content.saved'));
    }

    public function photo(int $image)
    {
        $row = DB::table('homepage_images')->find($image);
        abort_unless($row, 404);

        return response()->file(Storage::disk('local')->path($row->path), ['Content-Type' => 'image/jpeg', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'no-cache']);
    }

    public function remove(int $image)
    {
        $row = DB::table('homepage_images')->find($image);
        abort_unless($row, 404);
        DB::table('homepage_images')->where('id', $image)->delete();
        Storage::disk('local')->delete($row->path);

        return back()->with('status',Words::get('content.saved'));
    }
}
