<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Services\PublishedHistory;
use App\Services\ResearchRecord;
use Illuminate\Http\Request;

class ResearchController extends Controller
{
    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['csv', 'geojson'], true), 404);
        $asOf = PublishedHistory::date($request);
        $query = PublishedHistory::filtered($request, $asOf)->orderBy('site_id');
        $exportedAt = now()->toIso8601String();

        return response()->streamDownload(function () use ($query, $asOf, $exportedAt, $format) {
            $output = fopen('php://output', 'w');
            if ($format === 'csv') {
                fwrite($output, "\xEF\xBB\xBF");
                // An empty export still has the same documented column names.
                $headers = ['site_reference', 'observation_id', 'name_en', 'name_zh', 'description_en', 'description_zh', 'source_en', 'source_zh', 'category', 'location_notes', 'condition', 'date_precision', 'observed_from', 'observed_to', 'published_at', 'credit', 'location_visibility', 'latitude', 'longitude', 'record_url', 'as_of', 'exported_at'];
                fputcsv($output, $headers, ',', '"', '');
            } else {
                fwrite($output, '{"type":"FeatureCollection","features":[');
            }
            $first = true;
            foreach ($query->lazy(200) as $observation) {
                $row = ResearchRecord::data($observation, $asOf, $exportedAt);
                if ($format === 'csv') {
                    $cells = array_map(function ($value) {
                        // Prefix potential spreadsheet formulas without altering canonical JSON data.
                        return is_string($value) && preg_match('/^[\s\x00-\x1F]*[=+@\-\t\r\n＝＋－＠]/u', $value) ? "'".$value : $value;
                    }, array_values($row));
                    fputcsv($output, $cells, ',', '"', '');
                } else {
                    $geometry = $row['latitude'] === null ? null : ['type' => 'Point', 'coordinates' => [$row['longitude'], $row['latitude']]];
                    fwrite($output, ($first ? '' : ',').json_encode(['type' => 'Feature', 'id' => $observation->id, 'geometry' => $geometry, 'properties' => $row], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
                }
                $first = false;
            }
            if ($format === 'geojson') {
                fwrite($output, ']}');
            }
            fclose($output);
        }, 'yunnan-devotional-atlas-'.now()->toDateString().'.'.$format, ['Content-Type' => $format === 'csv' ? 'text/csv; charset=UTF-8' : 'application/geo+json; charset=UTF-8', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function citation(Observation $observation)
    {
        abort_unless($observation->status === 'approved' && $observation->published_at !== null, 404);

        return response(ResearchRecord::citation($observation)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="citation-'.$observation->id.'.txt"', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
