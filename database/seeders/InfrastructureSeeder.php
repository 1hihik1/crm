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
    \App\Models\Room::create(['name' => 'Основной цех', 'address' => 'Бокс 1-3']);
    \App\Models\Room::create(['name' => 'Офис', 'address' => 'Вход А']);

    // 2. Рабочие места (Боксы)
    \App\Models\Workplace::create(['room_id' => 2, 'name' => 'Бокс №1']);
    \App\Models\Workplace::create(['room_id' => 2, 'name' => 'Бокс №2']);
    \App\Models\Workplace::create(['room_id' => 2, 'name' => 'Бокс №3']);
    }
}
