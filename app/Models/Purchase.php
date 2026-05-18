<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['supplier_id', 'user_id', 'purchased_at', 'status', 'inventory_applied_at', 'total_amount', 'document_path', 'comment'];

    protected $casts = [
        'purchased_at' => 'datetime',
        'inventory_applied_at' => 'datetime',
    ];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function employee() { return $this->belongsTo(User::class, 'user_id'); }
    public function items() { return $this->hasMany(PurchaseItem::class); }
}
