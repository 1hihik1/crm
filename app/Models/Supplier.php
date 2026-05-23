<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'address', 'phone'];

    public function purchases() { return $this->hasMany(Purchase::class); }

    public function partPrices() { return $this->hasMany(SupplierPartPrice::class); }

    public function parts()
    {
        return $this->belongsToMany(Part::class, 'supplier_part_prices')
            ->withPivot('purchase_price')
            ->withTimestamps();
    }
}
