<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\Supplier;
use App\Models\SupplierPartPrice;
use Illuminate\Database\Seeder;

class SupplierPartPriceSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::orderBy('id')->get();
        $parts = Part::orderBy('id')->get();

        if ($suppliers->isEmpty() || $parts->isEmpty()) {
            return;
        }

        $discounts = [0.92, 0.88];

        foreach ($suppliers as $index => $supplier) {
            $factor = $discounts[$index % count($discounts)];

            foreach ($parts as $part) {
                $purchasePrice = round((float) $part->retail_price * $factor * 0.65, 2);

                SupplierPartPrice::updateOrCreate(
                    [
                        'supplier_id' => $supplier->id,
                        'part_id' => $part->id,
                    ],
                    ['purchase_price' => max($purchasePrice, 1)]
                );
            }
        }
    }
}
