<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InfrastructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Помещения
        $warehouse = \App\Models\Room::create(['name' => 'Склад запчастей', 'address' => 'Бокс 0']);
        $workshop = \App\Models\Room::create(['name' => 'Основной цех', 'address' => 'Бокс 1-3']);
        \App\Models\Room::create(['name' => 'Офис', 'address' => 'Вход А']);

        // Ячейка склада по умолчанию (остатки из закупок)
        \App\Models\StorageLocation::create([
            'room_id' => $warehouse->id,
            'name' => 'Зона приёмки',
            'rack' => 'A',
            'shelf' => '1',
        ]);

        // 2. Рабочие места (Боксы)
        \App\Models\Workplace::create(['room_id' => $workshop->id, 'name' => 'Бокс №1']);
        \App\Models\Workplace::create(['room_id' => $workshop->id, 'name' => 'Бокс №2']);
        \App\Models\Workplace::create(['room_id' => $workshop->id, 'name' => 'Бокс №3']);

        // Поставщики для закупок
        \App\Models\Supplier::create(['name' => 'ООО «АвтоДеталь»', 'address' => 'г. Москва', 'phone' => '+7 (495) 000-00-01']);
        \App\Models\Supplier::create(['name' => 'ИП Иванов', 'address' => 'г. Санкт-Петербург', 'phone' => '+7 (812) 000-00-02']);
    }
}
