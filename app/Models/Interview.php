<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasUlids;

    protected $guarded = ['id'];

    protected $hidden = ['original_path', 'web_path', 'scan_evidence'];

    public function observation()
    {
        return $this->belongsTo(Observation::class);
    }

    public function title(): string
    {
        return (app()->getLocale() === 'zh-Hans' ? $this->title_zh : $this->title_en) ?: ($this->title_en ?: $this->title_zh);
    }
}
