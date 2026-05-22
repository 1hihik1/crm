<?php

namespace Database\Seeders;

use App\Models\Part;
use App\Models\StorageLocation;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class PartSeeder extends Seeder
{
    public function run(): void
    {
        $locationId = StorageLocation::query()->value('id');
        if (! $locationId) {
            return;
        }

        $parts = [
            ['name' => 'Масляный фильтр Mann W712/95', 'manufacturer' => 'Mann', 'category' => 'Фильтры', 'retail_price' => 890, 'characteristics' => ['Тип' => 'масляный', 'Резьба' => 'M20x1.5'], 'qty' => 24],
            ['name' => 'Колодки тормозные передние ATE', 'manufacturer' => 'ATE', 'category' => 'Тормоза', 'retail_price' => 4200, 'characteristics' => ['Ось' => 'передняя'], 'qty' => 8],
            ['name' => 'Свеча зажигания NGK BKR6E', 'manufacturer' => 'NGK', 'category' => 'Электрика', 'retail_price' => 450, 'characteristics' => ['Зазор' => '1.0 мм'], 'qty' => 40],
            ['name' => 'Ремень ГРМ Gates', 'manufacturer' => 'Gates', 'category' => 'Ремни', 'retail_price' => 3100, 'characteristics' => ['Зубья' => '137'], 'qty' => 5],
            ['name' => 'Антифриз G12 5л', 'manufacturer' => 'Febi', 'category' => 'Жидкости', 'retail_price' => 1200, 'characteristics' => ['Объём' => '5 л'], 'qty' => 2],
            ['name' => 'Диск тормозной передний Zimmermann', 'manufacturer' => 'Zimmermann', 'category' => 'Тормоза', 'retail_price' => 5800, 'characteristics' => ['Диаметр' => '312 мм'], 'qty' => 4],
            ['name' => 'Воздушный фильтр Bosch', 'manufacturer' => 'Bosch', 'category' => 'Фильтры', 'retail_price' => 750, 'characteristics' => [], 'qty' => 15],
            ['name' => 'Щётки стеклоочистителя 650/400', 'manufacturer' => 'Bosch', 'category' => 'Кузов', 'retail_price' => 1100, 'characteristics' => ['Комплект' => '2 шт'], 'qty' => 12],
        ];

        foreach ($parts as $row) {
            $qty = $row['qty'];
            unset($row['qty']);

            $part = Part::firstOrCreate(
                ['name' => $row['name']],
                array_merge($row, ['condition' => 'new', 'brand' => $row['manufacturer']])
            );

            $wh = Warehouse::firstOrNew([
                'part_id' => $part->id,
                'storage_location_id' => $locationId,
            ]);
            $wh->quantity = $qty;
            $wh->save();
        }
    }
}
