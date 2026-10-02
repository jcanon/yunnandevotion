<?php

namespace App\Services;

use App\Models\Observation;
use App\Support\Words;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PhotographUpload
{
    public array $paths = [];

    public function validate(Request $request): array
    {
        $data = $request->validate(['photos' => 'nullable|array|max:4', 'photos.*.file' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120|dimensions:max_width=6000,max_height=6000', 'photos.*.caption_en' => 'nullable|string|max:2000', 'photos.*.caption_zh' => 'nullable|string|max:2000', 'photos.*.credit' => 'nullable|string|max:200', 'photos.*.permission' => 'nullable|boolean'], ['max' => Words::get('photo.invalid'), 'mimes' => Words::get('photo.invalid'), 'dimensions' => Words::get('photo.invalid'), 'file' => Words::get('photo.invalid')]);
        foreach ($data['photos'] ?? [] as $index => $photo) {
            if (! empty($photo['file'])) {
                $size = @getimagesize($photo['file']->getRealPath());
                if (! $size || $size[0] * $size[1] > 16000000 || ! filled($photo['credit'] ?? null) || empty($photo['permission']) || (! filled($photo['caption_en'] ?? null) && ! filled($photo['caption_zh'] ?? null))) {
                    throw ValidationException::withMessages(["photos.$index.file" => Words::get('photo.required')]);
                }
            }
        }

        return array_filter($data['photos'] ?? [], fn ($photo) => ! empty($photo['file']));
    }

    public function store(Observation $observation, array $photos): void
    {
        foreach ($photos as $photo) {
            $file = $photo['file'];
            $raw = file_get_contents($file->getRealPath());
            $image = @imagecreatefromstring($raw);
            if (! $image) {
                throw ValidationException::withMessages(['photos' => Words::get('photo.invalid')]);
            }
            $scale = min(1, 1800 / max(imagesx($image), imagesy($image)));
            $web = imagecreatetruecolor(max(1, (int) (imagesx($image) * $scale)), max(1, (int) (imagesy($image) * $scale)));
            imagefill($web, 0, 0, imagecolorallocate($web, 255, 255, 255));
            imagecopyresampled($web, $image, 0, 0, 0, 0, imagesx($web), imagesy($web), imagesx($image), imagesy($image));
            ob_start();
            imagejpeg($web, null, 85);
            $jpeg = ob_get_clean();
            imagedestroy($image);
            imagedestroy($web);
            $id = Str::ulid();
            $original = "photographs/$id/original";
            $derivative = "photographs/$id/web.jpg";
            foreach ([$original => $raw, $derivative => $jpeg] as $path => $bytes) {
                $this->paths[] = $path;
                if (! Storage::disk('local')->put($path, $bytes)) {
                    throw new \RuntimeException('Private media write failed.');
                }
            }
            $observation->photographs()->create(['original_path' => $original, 'web_path' => $derivative, 'sha256' => hash('sha256', $raw), 'bytes' => strlen($raw), 'caption_en' => $photo['caption_en'] ?? '', 'caption_zh' => $photo['caption_zh'] ?? '', 'credit' => $photo['credit'], 'permission' => true]);
        }
    }

    public function cleanup(): void
    {
        Storage::disk('local')->delete($this->paths);
    }
}
