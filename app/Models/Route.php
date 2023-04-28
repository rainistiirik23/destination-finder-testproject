<?php

namespace App\Models;

use App\Models\Stop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Route extends Model
{
    use HasFactory;

    public function stops()
    {
        return $this->belongsToMany(Stop::class);
    }
}
