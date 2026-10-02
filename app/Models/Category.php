<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'name_en', 'name_zh', 'color', 'svg'];

    public static function keys(): array
    {
        return self::orderBy('key')->pluck('key')->all();
    }

    public static function label(string $key): string
    {
        return self::whereKey($key)->value(app()->getLocale() === 'zh-Hans' ? 'name_zh' : 'name_en') ?? $key;
    }

    public function marker(): string
    {
        $shape = ['shrine' => '<path d="M12 2 L22 12 L12 22 L2 12 Z"/>', 'altar' => '<rect x="3" y="3" width="18" height="18"/>', 'incense' => '<path d="M12 2 L22 22 L2 22 Z"/>', 'niche' => '<circle cx="12" cy="12" r="10"/>', 'other' => '<path d="M9 2 H15 V9 H22 V15 H15 V22 H9 V15 H2 V9 H9 Z"/>'][$this->key] ?? '<circle cx="12" cy="12" r="10"/>';

        return $this->svg ?? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="'.$this->color.'">'.$shape.'</svg>';
    }
}
