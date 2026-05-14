<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CarModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
        ['brand' => 'Toyota', 'model' => 'Camry', 'year' => 2020],
        ['brand' => 'BMW', 'model' => 'X5', 'year' => 2022],
        ['brand' => 'Audi', 'model' => 'A6', 'year' => 2021],
        ['brand' => 'Mercedes-Benz', 'model' => 'E-Class', 'year' => 2023],
        ['brand' => 'Kia', 'model' => 'Rio', 'year' => 2019],
        ['brand' => 'Hyundai', 'model' => 'Solaris', 'year' => 2020],
        ['brand' => 'Volkswagen', 'model' => 'Polo', 'year' => 2018],
        ['brand' => 'Skoda', 'model' => 'Octavia', 'year' => 2021],
        ['brand' => 'Lada', 'model' => 'Vesta', 'year' => 2023],
        ['brand' => 'Ford', 'model' => 'Focus', 'year' => 2017],
    ];
    \Illuminate\Support\Facades\DB::table('car_models')->insert($models);
    }
}
