<?php

namespace App\Services;

use App\Http\Controllers\ArchiveController;
use App\Models\Category;
use App\Models\Observation;
use App\Support\Words;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PublishedHistory
{
    public static function date(Request $request): ?string
    {
        return $request->validate(['as_of' => 'nullable|date_format:Y-m-d|before_or_equal:today'], ['date_format' => Words::get('archive.date-error'), 'before_or_equal' => Words::get('archive.date-error')])['as_of'] ?? null;
    }

    public static function filtered(Request $request, ?string $asOf)
    {
        $filters = $request->validate(['search' => 'nullable|string|max:200', 'category' => ['nullable', Rule::in(Category::keys())], 'condition' => ['nullable', Rule::in(ArchiveController::CONDITIONS)]]);
        $query = self::latest($asOf)->with('site');
        if (! empty($filters['category'])) {
            $query->where('content->category', $filters['category']);
        }
        if (! empty($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }
        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($search) {
                foreach (['name_en', 'name_zh', 'description_en', 'description_zh'] as $field) {
                    $q->orWhere('content->'.$field, 'like', $search);
                }
            });
        }

        return $query;
    }

    public static function eligible($query, ?string $date, string $prefix = '')
    {
        if ($date) {
            $query->whereNotNull($prefix.'observed_from')->whereDate(DB::raw('COALESCE('.$prefix.'observed_to, '.$prefix.'observed_from)'), '<=', $date);
        }

        return $query;
    }

    public static function latest(?string $date)
    {
        $candidate = DB::table('observations as candidate')->select('candidate.id')->whereColumn('candidate.site_id', 'observations.site_id')->where('candidate.status', 'approved')->whereNotNull('candidate.published_at');
        self::eligible($candidate, $date, 'candidate.');
        $candidate->orderByRaw('COALESCE(candidate.observed_to, candidate.observed_from) DESC')->orderByDesc('candidate.published_at')->orderByDesc('candidate.id')->limit(1);

        return Observation::published()->where('observations.id', '=', $candidate);
    }
}
