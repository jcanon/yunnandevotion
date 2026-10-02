<?php

namespace App\Services;

use App\Models\Observation;
use App\Support\Words;

class ResearchRecord
{
    public static function url(Observation $o): string
    {
        return route('sites.show', $o->site_id).'#observation-'.$o->id;
    }

    public static function citation(Observation $o): string
    {
        $credit = empty($o->content['anonymous_credit']) ? ($o->content['credit'] ?? '') : '';
        $date = $o->observed_from?->format('Y-m-d') ?? Words::get('archive.unknown');
        if ($o->observed_to) {
            $date .= ' / '.$o->observed_to->format('Y-m-d');
        }

        return ($credit ?: Words::get('profile.anonymous')).'. “'.$o->text('name').'.” Yunnan Devotional Atlas. '.$o->site->reference.'; '.$o->id.'. '.Words::get('research.observed').': '.$date.'. '.Words::get('research.accessed').': '.now()->toDateString().'. '.self::url($o);
    }

    public static function data(Observation $o, ?string $asOf, string $exportedAt): array
    {
        $exact = $o->location_visibility === 'exact' && $o->latitude !== null && $o->longitude !== null;
        $row = ['site_reference' => $o->site->reference, 'observation_id' => $o->id];
        foreach (['name_en', 'name_zh', 'description_en', 'description_zh', 'source_en', 'source_zh', 'category', 'location_notes'] as $key) {
            $row[$key] = $o->content[$key] ?? '';
        }

        return $row + [
            'condition' => $o->condition, 'date_precision' => $o->date_precision,
            'observed_from' => $o->observed_from?->format('Y-m-d'), 'observed_to' => $o->observed_to?->format('Y-m-d'),
            'published_at' => $o->published_at?->toIso8601String(),
            'credit' => empty($o->content['anonymous_credit']) ? ($o->content['credit'] ?? '') : '',
            'location_visibility' => $o->location_visibility,
            'latitude' => $exact ? (float) $o->latitude : null, 'longitude' => $exact ? (float) $o->longitude : null,
            'record_url' => self::url($o), 'as_of' => $asOf, 'exported_at' => $exportedAt,
        ];
    }
}
