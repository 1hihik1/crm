<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageLocation extends Model
{
    protected $fillable = ['room_id', 'name', 'rack', 'shelf', 'cell', 'row', 'section'];

    public function room() { return $this->belongsTo(Room::class); }
}
