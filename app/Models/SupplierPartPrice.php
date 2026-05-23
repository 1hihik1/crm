<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPartPrice extends Model
{
    protected $fillable = ['supplier_id', 'part_id', 'purchase_price'];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public static function priceFor(int $supplierId, int $partId): ?float
    {
        $price = static::query()
            ->where('supplier_id', $supplierId)
            ->where('part_id', $partId)
            ->value('purchase_price');

        return $price !== null ? (float) $price : null;
    }
}
