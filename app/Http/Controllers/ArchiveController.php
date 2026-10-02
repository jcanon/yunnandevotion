<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Observation;
use App\Models\ObservationDecision;
use App\Models\Site;
use App\Services\InterviewUpload;
use App\Services\PhotographUpload;
use App\Services\PublishedHistory;
use App\Support\Words;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ArchiveController extends Controller
{
    public const CATEGORIES = ['shrine', 'altar', 'incense', 'niche', 'other'];

    public const CONDITIONS = ['intact', 'altered', 'relocated', 'demolished', 'unknown'];

    public function index(Request $request)
    {
        $asOf = PublishedHistory::date($request);
        $query = PublishedHistory::filtered($request, $asOf);
        $items = $query->orderBy('site_id')->paginate(20)->withQueryString();
        $markers = $items->getCollection()->filter(fn ($o) => $o->location_visibility === 'exact' && $o->latitude !== null && $o->longitude !== null)->map(fn ($o) => ['lat' => (float) $o->latitude, 'lng' => (float) $o->longitude, 'title' => $o->text('name'), 'category' => $o->content['category'], 'icon' => route('assets.marker', $o->content['category']), 'url' => route('sites.show', ['site' => $o->site_id, 'as_of' => $asOf])])->values();

        return view('archive.index', compact('items', 'markers', 'asOf') + ['screen' => 'archive', 'categories' => Category::keys(), 'conditions' => self::CONDITIONS]);
    }

    public function show(Request $request, Site $site)
    {
        $asOf = PublishedHistory::date($request);
        $observations = PublishedHistory::eligible($site->observations()->published(), $asOf)->orderByRaw('COALESCE(observed_to, observed_from) DESC')->orderByDesc('published_at')->orderByDesc('id')->get();
        abort_if($observations->isEmpty(), 404);

        return view('archive.site', compact('site', 'observations', 'asOf') + ['screen' => 'archive.history']);
    }

    public function create(Request $request)
    {
        $picked = $request->validate(['lat' => 'nullable|numeric|between:-90,90', 'lng' => 'nullable|numeric|between:-180,180']);
        $site = $request->query('site') ? Site::findOrFail($request->query('site')) : null;
        if ($site) {
            abort_unless($site->observations()->published()->exists() || $site->observations()->where('author_id', $request->user()->id)->exists() || $request->user()->can('moderate'), 404);
        }
        $revision = $request->query('revise') ? Observation::findOrFail($request->query('revise')) : null;
        if ($revision) {
            abort_unless($revision->author_id === $request->user()->id && in_array($revision->status, ['changes_requested', 'rejected']), 403);
            $site = $revision->site;
        }
        $latest = $site?->observations()->published()->orderByDesc('observed_from')->first();

        return view('archive.form', ['screen' => 'archive.contribute', 'site' => $site, 'revision' => $revision, 'latest' => $latest, 'picked' => $picked, 'categories' => Category::keys(), 'conditions' => self::CONDITIONS]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'nullable|ulid|exists:sites,id', 'revises_id' => 'nullable|ulid|exists:observations,id',
            'name_en' => 'nullable|string|max:200', 'name_zh' => 'nullable|string|max:200',
            'description_en' => 'nullable|string|max:20000', 'description_zh' => 'nullable|string|max:20000',
            'source_en' => 'nullable|string|max:5000', 'source_zh' => 'nullable|string|max:5000',
            'category' => ['required', Rule::in(Category::keys())], 'condition' => ['required', Rule::in(self::CONDITIONS)],
            'date_precision' => ['required', Rule::in(['exact', 'range', 'unknown'])],
            'observed_from' => 'nullable|required_unless:date_precision,unknown|date_format:Y-m-d|before_or_equal:today',
            'observed_to' => 'nullable|required_if:date_precision,range|date_format:Y-m-d|after_or_equal:observed_from|before_or_equal:today',
            'latitude' => 'nullable|required_with:longitude|numeric|between:-90,90', 'longitude' => 'nullable|required_with:latitude|numeric|between:-180,180',
            'location_visibility' => ['required', Rule::in(['exact', 'approximate'])],
            'location_notes' => 'nullable|string|max:2000', 'anonymous_credit' => 'required|boolean',
        ], ['required' => Words::get('error.required'), 'required_unless' => Words::get('archive.date-error'), 'required_if' => Words::get('archive.date-error'), 'required_with' => Words::get('archive.coordinate-error'), 'date_format' => Words::get('archive.date-error'), 'before_or_equal' => Words::get('archive.date-error'), 'after_or_equal' => Words::get('archive.date-error'), 'between' => Words::get('archive.coordinate-error'), 'numeric' => Words::get('archive.coordinate-error'), 'in' => Words::get('error.role'), 'max' => Words::get('error.long')]);
        if (! (filled($data['name_en'] ?? null) && filled($data['description_en'] ?? null)) && ! (filled($data['name_zh'] ?? null) && filled($data['description_zh'] ?? null))) {
            return back()->withInput()->withErrors(['name_en' => Words::get('archive.language-error')]);
        }
        $uploads = new PhotographUpload;
        $photos = $uploads->validate($request);
        $videoUploads = new InterviewUpload;
        $video = $videoUploads->validate($request);
        try {
            $observation = DB::transaction(function () use ($request, $data, $uploads, $photos, $videoUploads, $video) {
                $site = empty($data['site_id']) ? null : Site::findOrFail($data['site_id']);
                if ($site) {
                    abort_unless($site->observations()->published()->exists() || $site->observations()->where('author_id', $request->user()->id)->exists() || $request->user()->can('moderate'), 404);
                }
                if (! empty($data['revises_id'])) {
                    $prior = Observation::whereKey($data['revises_id'])->lockForUpdate()->firstOrFail();
                    abort_unless($prior->author_id === $request->user()->id && in_array($prior->status, ['changes_requested', 'rejected']) && $site?->id === $prior->site_id, 403);
                    abort_if(Observation::where('revises_id', $prior->id)->exists(), 409);
                }
                $site ??= Site::create(['reference' => 'YDA-'.Str::ulid()]);
                $content = [];
                foreach (['name_en', 'name_zh', 'description_en', 'description_zh', 'source_en', 'source_zh', 'category', 'location_notes'] as $key) {
                    $content[$key] = $data[$key] ?? '';
                }
                $content['credit'] = $data['anonymous_credit'] ? '' : $request->user()->name;
                $content['anonymous_credit'] = (bool) $data['anonymous_credit'];
                $obs = $site->observations()->create(['author_id' => $request->user()->id, 'content' => $content, 'condition' => $data['condition'], 'date_precision' => $data['date_precision'], 'observed_from' => $data['date_precision'] === 'unknown' ? null : ($data['observed_from'] ?? null), 'observed_to' => $data['date_precision'] === 'range' ? $data['observed_to'] : null, 'latitude' => $data['latitude'] ?? null, 'longitude' => $data['longitude'] ?? null, 'location_visibility' => $data['location_visibility'], 'revises_id' => $data['revises_id'] ?? null, 'status' => ($request->user()->can('moderate') && ! $photos && ! $video) ? 'approved' : 'pending', 'published_at' => ($request->user()->can('moderate') && ! $photos && ! $video) ? now() : null]);
                if ($obs->status === 'approved') {
                    ObservationDecision::create(['observation_id' => $obs->id, 'reviewer_id' => $request->user()->id, 'decision' => 'approved']);
                }

                $uploads->store($obs, $photos);
                $videoUploads->store($obs, $video);

                return $obs;
            });

        } catch (\Throwable $error) {
            $uploads->cleanup();
            $videoUploads->cleanup();
            throw $error;
        }

        return redirect()->route('observations.private', $observation)->with('status', Words::get('archive.saved'));
    }

    public function mine(Request $request)
    {
        return view('archive.queue', ['screen' => 'archive.mine', 'items' => Observation::where('author_id', $request->user()->id)->latest()->paginate(20)]);
    }

    public function queue()
    {
        return view('archive.queue', ['screen' => 'moderation', 'items' => Observation::where('status', 'pending')->oldest()->paginate(20)]);
    }

    public function privateShow(Request $request, Observation $observation)
    {
        abort_unless($observation->author_id === $request->user()->id || $request->user()->can('moderate'), 403);

        return view('archive.review', ['screen' => 'archive.submission', 'observation' => $observation->load('decisions', 'author')]);
    }

    public function decide(Request $request, Observation $observation)
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'changes_requested', 'rejected'])], 'reason' => 'nullable|required_unless:decision,approved|string|max:5000'], ['required_unless' => Words::get('archive.reason-error'), 'in' => Words::get('error.role'), 'max' => Words::get('error.long')]);
        DB::transaction(function () use ($request, $observation, $data) {
            $obs = Observation::whereKey($observation->id)->lockForUpdate()->firstOrFail();
            abort_unless($obs->status === 'pending', 409);
            if ($data['decision'] === 'approved' && ($obs->photographs()->where('status', '!=', 'approved')->exists() || $obs->interviews()->where('status', '!=', 'approved')->exists())) {
                throw ValidationException::withMessages(['decision' => Words::get('video.blocked')]);
            }
            $obs->update(['status' => $data['decision'], 'published_at' => $data['decision'] === 'approved' ? now() : null]);
            ObservationDecision::create(['observation_id' => $obs->id, 'reviewer_id' => $request->user()->id, 'decision' => $data['decision'], 'reason' => $data['reason'] ?? null]);
        });

        return back()->with('status', Words::get('archive.decision-saved'));
    }
}
