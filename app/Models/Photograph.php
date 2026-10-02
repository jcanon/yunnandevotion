<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Photograph extends Model
{
    use HasUlids;

    protected $guarded = ['id'];

    protected $hidden = ['original_path', 'web_path', 'scan_evidence'];

    public function observation()
    {
        return $this->belongsTo(Observation::class);
    }

    public function caption(): string
    {
        return (app()->getLocale() === 'zh-Hans' ? $this->caption_zh : $this->caption_en) ?: ($this->caption_en ?: $this->caption_zh);
    }
}
