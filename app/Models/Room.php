<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['name', 'address', 'area', 'purpose'];

    public function storageLocations() { return $this->hasMany(StorageLocation::class); }
    public function workplaces() { return $this->hasMany(Workplace::class); }
}
