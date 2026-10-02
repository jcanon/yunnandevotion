<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    use HasUlids;

    protected $guarded = ['id'];

    protected $hidden = ['latitude', 'longitude', 'author_id'];

    protected function casts(): array
    {
        return ['content' => 'array', 'observed_from' => 'date', 'observed_to' => 'date', 'published_at' => 'datetime'];
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }

    public function photographs()
    {
        return $this->hasMany(Photograph::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function decisions()
    {
        return $this->hasMany(ObservationDecision::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'approved')->whereNotNull('published_at');
    }

    public function text(string $key): string
    {
        return $this->content[$key.(app()->getLocale() === 'zh-Hans' ? '_zh' : '_en')] ?: ($this->content[$key.'_en'] ?: $this->content[$key.'_zh']);
    }

    protected static function booted(): void
    {
        static::updating(function ($observation) {
            if ($observation->getOriginal('status') !== 'pending' || array_diff(array_keys($observation->getDirty()), ['status', 'published_at', 'updated_at'])) {
                throw new \LogicException('Observations are immutable; submit a new version.');
            }
        });
        static::deleting(fn () => throw new \LogicException('Historical observations cannot be deleted.'));
    }
}
