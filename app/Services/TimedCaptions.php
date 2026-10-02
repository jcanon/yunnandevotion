<?php

namespace App\Services;

class TimedCaptions
{
    // Accept plain WebVTT cues only: no markup, styling, regions, or external resources.
    public static function valid(string $text, int $duration): bool
    {
        $text = str_replace("\r\n", "\n", trim($text));
        if (! str_starts_with($text, "WEBVTT\n\n")) {
            return false;
        }
        $blocks = preg_split('/\n[ \t]*\n/', substr($text, 8));
        $last = -1;
        foreach ($blocks as $block) {
            if (! preg_match('/^(\d{2}):([0-5]\d):([0-5]\d)\.(\d{3}) --> (\d{2}):([0-5]\d):([0-5]\d)\.(\d{3})\n([^<>\x00]+)$/u', $block, $m)) {
                return false;
            }
            $start = $m[1] * 3600 + $m[2] * 60 + $m[3] + $m[4] / 1000;
            $end = $m[5] * 3600 + $m[6] * 60 + $m[7] + $m[8] / 1000;
            if ($start < $last || $end <= $start || $end > $duration || ! trim($m[9])) {
                return false;
            }$last = $end;
        }

return count($blocks) > 0;
    }
}
