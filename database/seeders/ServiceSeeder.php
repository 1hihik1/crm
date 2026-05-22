<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Замена масла и фильтра', 'description' => 'Моторное масло + масляный фильтр, проверка уровней.', 'price' => 3500],
            ['name' => 'Компьютерная диагностика', 'description' => 'Считывание ошибок, отчёт по системам автомобиля.', 'price' => 1500],
            ['name' => 'Замена тормозных колодок (перед)', 'description' => 'Колодки + осмотр дисков, тест тормозов.', 'price' => 4500],
            ['name' => 'Развал-схождение', 'description' => '3D-стенд, регулировка углов установки колёс.', 'price' => 3000],
            ['name' => 'Замена свечей зажигания', 'description' => 'Комплект свечей, проверка зазоров.', 'price' => 2800],
            ['name' => 'Шиномонтаж (4 колеса)', 'description' => 'Снятие/установка, балансировка, демонтаж.', 'price' => 2400],
        ];

        foreach ($services as $row) {
            Service::firstOrCreate(['name' => $row['name']], $row);
        }
    }
}
