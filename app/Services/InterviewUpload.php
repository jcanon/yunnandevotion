<?php

namespace App\Services;

use App\Models\Observation;
use App\Support\Words;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InterviewUpload
{
    public array $paths = [];

    public function validate(Request $request): ?array
    {
        $data = $request->validate(['interview.file' => 'nullable|file|mimetypes:video/mp4,video/webm|max:102400', 'interview.title_en' => 'nullable|string|max:200', 'interview.title_zh' => 'nullable|string|max:200', 'interview.credit' => 'nullable|string|max:200', 'interview.permission' => 'nullable|boolean', 'interview.transcript_en' => 'nullable|string|max:100000', 'interview.transcript_zh' => 'nullable|string|max:100000'], ['max' => Words::get('video.invalid'), 'mimetypes' => Words::get('video.invalid'), 'file' => Words::get('video.invalid')]);
        $video = $data['interview'] ?? null;
        if (empty($video['file'])) {
            return null;
        }
        if (! filled($video['credit'] ?? null) || empty($video['permission']) || (! filled($video['title_en'] ?? null) && ! filled($video['title_zh'] ?? null))) {
            throw ValidationException::withMessages(['interview.file' => Words::get('video.required')]);
        }

        return $video;
    }

    public function store(Observation $observation, ?array $video): void
    {
        if (! $video) {
            return;
        }
        $path = 'interviews/'.Str::ulid().'/original';
        $this->paths[] = $path;
        $stream = fopen($video['file']->getRealPath(), 'rb');
        try {
            if (! Storage::disk('local')->put($path, $stream)) {
                throw new \RuntimeException('Private video write failed.');
            }
        } finally {
            fclose($stream);
        }
        $observation->interviews()->create(['original_path' => $path, 'original_sha256' => hash_file('sha256', $video['file']->getRealPath()), 'title_en' => $video['title_en'] ?? '', 'title_zh' => $video['title_zh'] ?? '', 'credit' => $video['credit'], 'permission' => true, 'transcript_en' => $video['transcript_en'] ?? '', 'transcript_zh' => $video['transcript_zh'] ?? '']);
    }

    public function cleanup(): void
    {
        Storage::disk('local')->delete($this->paths);
    }
}
