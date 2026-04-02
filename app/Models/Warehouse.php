<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['part_id', 'storage_location_id', 'quantity'];

    public function part() { return $this->belongsTo(Part::class); }
    public function location() { return $this->belongsTo(StorageLocation::class, 'storage_location_id'); }
}
