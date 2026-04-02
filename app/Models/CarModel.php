<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    protected $fillable = ['brand', 'model', 'year'];

    public function parts() { return $this->belongsToMany(Part::class, 'part_compatibility'); }
}
