<?php

namespace App\Http\Controllers;

use App\Models\Interview;
use App\Models\Observation;
use App\Services\TimedCaptions;
use App\Support\Words;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InterviewController extends Controller
{
    public function publicVideo(Interview $interview)
    {
        $this->published($interview);

        return $this->stream($interview);
    }

    private function published(Interview $video): void
    {
        abort_unless($video->status === 'approved' && $video->observation->status === 'approved' && $video->observation->published_at, 404);
    }

    private function stream(Interview $video)
    {
        abort_unless($video->web_path, 404);

        return response()->file(Storage::disk('local')->path($video->web_path), ['Content-Type' => $video->web_mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    private function authorized(Request $request, Interview $video): void
    {
        abort_unless($video->observation->author_id === $request->user()->id || $request->user()->can('moderate'), 403);
    }

    public function preview(Request $request, Interview $interview)
    {
        $this->authorized($request, $interview);

        return $this->stream($interview);
    }

    public function original(Interview $interview)
    {
        return Storage::disk('local')->download($interview->original_path, 'original-'.$interview->id.'.bin', ['Content-Type' => 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function captions(Request $request, Interview $interview, string $language)
    {
        abort_unless(in_array($language, ['en', 'zh'], true), 404);
        if ($request->routeIs('videos.private-captions')) {
            $this->authorized($request, $interview);
        } else {
            $this->published($interview);
        }

return response($interview->{'captions_'.$language} ?? '', 200, ['Content-Type' => 'text/vtt; charset=utf-8', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function prepare(Request $request, Interview $interview)
    {
        $data = $request->validate(['web_file' => 'nullable|file|mimetypes:video/mp4,video/webm|max:102400', 'duration_seconds' => 'required|integer|min:1|max:600', 'transcript_en' => 'required|string|max:100000', 'transcript_zh' => 'required|string|max:100000', 'captions_en' => 'required|string|max:100000', 'captions_zh' => 'required|string|max:100000'], ['required' => Words::get('video.required'), 'max' => Words::get('video.invalid'), 'mimetypes' => Words::get('video.invalid'), 'min' => Words::get('video.invalid'), 'integer' => Words::get('video.invalid')]);
        foreach (['en', 'zh'] as $language) {
            if (! TimedCaptions::valid($data['captions_'.$language], $data['duration_seconds'])) {
                throw ValidationException::withMessages(['captions_'.$language => Words::get('video.vtt-invalid')]);
            }
        }
        $newPath = null;
        $oldPath = null;
        try {
            DB::transaction(function () use ($request, $interview, $data, &$newPath, &$oldPath) {
                $obs = Observation::whereKey($interview->observation_id)->lockForUpdate()->firstOrFail();
                $video = Interview::whereKey($interview->id)->lockForUpdate()->firstOrFail();
                abort_unless($obs->status === 'pending' && $video->status === 'pending', 409);
                $fields = collect($data)->except('web_file')->all();
                if ($request->hasFile('web_file')) {
                    $file = $request->file('web_file');
                    $newPath = 'interviews/'.Str::ulid().'/web';
                    $stream = fopen($file->getRealPath(), 'rb');
                    try {
                        if (! Storage::disk('local')->put($newPath, $stream)) {
                            throw new \RuntimeException('Video write failed.');
                        }
                    } finally {
                        fclose($stream);
                    }$oldPath = $video->web_path;
                    $fields += ['web_path' => $newPath, 'web_sha256' => hash_file('sha256', $file->getRealPath()), 'web_mime' => $file->getMimeType()];
                }
                if (! $newPath && ! $video->web_path) {
                    throw ValidationException::withMessages(['web_file' => Words::get('video.required')]);
                }$video->update($fields);
            });
        } catch (\Throwable $error) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }throw $error;
        }
        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

return back()->with('status', Words::get('video.prepared'));
    }

    public function review(Request $request, Interview $interview)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['approved', 'rejected'])], 'scan_evidence' => 'required|string|max:5000', 'checks' => 'exclude_unless:status,approved|required|accepted'], ['required' => Words::get('video.required'), 'accepted' => Words::get('video.required')]);
        DB::transaction(function () use ($request, $interview, $data) {
            $obs = Observation::whereKey($interview->observation_id)->lockForUpdate()->firstOrFail();
            $video = Interview::whereKey($interview->id)->lockForUpdate()->firstOrFail();
            abort_unless($obs->status === 'pending' && $video->status === 'pending', 409);
            if ($data['status'] === 'approved' && (! $video->web_path || ! $video->transcript_en || ! $video->transcript_zh || ! TimedCaptions::valid($video->captions_en ?? '', (int) $video->duration_seconds) || ! TimedCaptions::valid($video->captions_zh ?? '', (int) $video->duration_seconds))) {
                throw ValidationException::withMessages(['status' => Words::get('video.required')]);
            }
            $video->update(['status' => $data['status'], 'scan_evidence' => $data['scan_evidence'], 'reviewer_id' => $request->user()->id, 'reviewed_at' => now()]);
        });

        return back()->with('status',Words::get('video.reviewed'));
    }
}
