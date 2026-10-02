<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasUlids;

    protected $fillable = ['reference'];

    public function observations()
    {
        return $this->hasMany(Observation::class);
    }
}
