<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\Photograph;
use App\Support\Words;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PhotographController extends Controller
{
    public function publicImage(Photograph $photograph)
    {
        abort_unless($photograph->status === 'approved' && $photograph->observation->status === 'approved' && $photograph->observation->published_at, 404);

        return $this->image($photograph);
    }

    private function image(Photograph $photo)
    {
        return Storage::disk('local')->response($photo->web_path, 'photograph.jpg', ['Content-Type' => 'image/jpeg', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function preview(Request $request, Photograph $photograph)
    {
        abort_unless($photograph->observation->author_id === $request->user()->id || $request->user()->can('moderate'), 403);

        return $this->image($photograph);
    }

    public function original(Photograph $photograph)
    {
        return Storage::disk('local')->download($photograph->original_path, 'original-'.$photograph->id.'.bin', ['Content-Type' => 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function review(Request $request, Photograph $photograph)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['approved', 'rejected'])], 'scan_evidence' => 'required|string|max:5000', 'checks' => 'exclude_unless:status,approved|required|accepted'], ['required' => Words::get('photo.required'), 'required_if' => Words::get('photo.required'), 'accepted' => Words::get('photo.required'), 'max' => Words::get('error.long')]);
        DB::transaction(function () use ($request, $photograph, $data) {
            $observation = Observation::whereKey($photograph->observation_id)->lockForUpdate()->firstOrFail();
            abort_unless($observation->status === 'pending', 409);
            $photo = Photograph::whereKey($photograph->id)->lockForUpdate()->firstOrFail();
            abort_unless($photo->status === 'pending', 409);
            $photo->update(['status' => $data['status'], 'scan_evidence' => $data['scan_evidence'], 'reviewer_id' => $request->user()->id, 'reviewed_at' => now()]);
        });

        return back()->with('status', Words::get('photo.reviewed'));
    }
}
