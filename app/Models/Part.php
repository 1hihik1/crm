<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $fillable = ['name', 'manufacturer', 'category', 'brand', 'type', 'characteristics', 'condition', 'retail_price'];

    protected $casts = [
        'characteristics' => 'array', //чтобы работать с JSON как с массивом, типо превращаю строку в массив
    ];

    public function compatibleModels() { return $this->belongsToMany(CarModel::class, 'part_compatibility'); }
    public function stock() { return $this->hasMany(Warehouse::class); }
    public function purchaseItems() { return $this->hasMany(PurchaseItem::class); }
}
