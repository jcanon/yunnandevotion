<?php

namespace App\Support;

use App\Models\InterfaceTranslation;

class Words
{
    public static function get(string $key, ?string $locale = null): string
    {
        return InterfaceTranslation::where('key', $key)->where('locale', $locale ?? app()->getLocale())->value('value') ?? $key;
    }
}
