<?php

namespace App\Http\Controllers;

use App\Models\InterfaceTranslation;
use App\Support\Words;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContentAdminController extends Controller
{
    public const HOME = ['home.headline', 'home.intro', 'home.project', 'home.funding', 'home.contributors'];

    public static function version($rows): string
    {
        return hash('sha256', json_encode($rows->sortBy('locale')->pluck('value', 'locale')->all(), JSON_UNESCAPED_UNICODE));
    }

    public function index(Request $request)
    {
        $home = $request->routeIs('admin.content');
        $search = $request->validate(['search' => 'nullable|string|max:200'])['search'] ?? '';
        $keys = InterfaceTranslation::query()->select('key')->distinct();
        if ($home) {
            $keys->whereIn('key', self::HOME);
        } else {
            $keys->whereNotIn('key', self::HOME);
            if ($search !== '') {
                $keys->where(fn ($q) => $q->where('key', 'like', '%'.$search.'%')->orWhere('value', 'like', '%'.$search.'%'));
            }
        }
        $items = $keys->orderBy('key')->paginate(15)->withQueryString();
        $rows = InterfaceTranslation::whereIn('key', $items->pluck('key'))->get()->groupBy('key');

        return view('admin-content', compact('items', 'rows', 'home', 'search') + ['screen' => $home ? 'content.title' : 'translations.title']);
    }

    public function update(Request $request)
    {
        $data = $request->validate(['key' => 'required|string|max:200', 'version' => 'required|string|size:64', 'en' => 'required|string|max:20000', 'zh' => 'required|string|max:20000'], ['required' => Words::get('error.required'), 'max' => Words::get('error.long')]);
        DB::transaction(function () use ($data) {
            $rows = InterfaceTranslation::where('key', $data['key'])->orderBy('locale')->lockForUpdate()->get();
            abort_unless($rows->count() === 2 && $rows->pluck('locale')->sort()->values()->all() === ['en', 'zh-Hans'], 404);
            if (! hash_equals(self::version($rows), $data['version'])) {
                throw ValidationException::withMessages(['version' => Words::get('content.conflict')]);
            }
            foreach (['en' => 'en', 'zh-Hans' => 'zh'] as $locale => $field) {
                $row = $rows->firstWhere('locale', $locale);
                // Preserve interpolation tokens used by notification and interface code.
                preg_match_all('/:[a-zA-Z_][a-zA-Z0-9_]*/', $row->value, $before);
                preg_match_all('/:[a-zA-Z_][a-zA-Z0-9_]*/', $data[$field], $after);
                sort($before[0]);
                sort($after[0]);
                if ($before[0] !== $after[0]) {
                    throw ValidationException::withMessages([$field => Words::get('content.tokens')]);
                }
                $row->update(['value' => $data[$field]]);
            }
        });

        return back()->with('status', Words::get('content.saved'));
    }
}
