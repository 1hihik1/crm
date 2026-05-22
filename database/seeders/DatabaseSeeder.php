<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    //вызов справочников
    $this->call([
        RoleSeeder::class,
        CarModelSeeder::class,
        InfrastructureSeeder::class,
        ServiceSeeder::class,
        PartSeeder::class,
    ]);

    //вау это же админ
    $admin = \App\Models\User::create([
        'surname' => 'Гослинг', 'name' => 'Админ', 'email' => 'admin@mail.ru', 'password' => bcrypt('password'),
        'position' => 'администратор'
    ]);
    $admin->assignRole('admin');

    //созданиее менеджеров
    $manager = \App\Models\User::create([
        'surname' => 'Иванов', 'name' => 'Иван', 'email' => 'manager@mail.ru', 'password' => bcrypt('password'),
        'position' => 'менеджер'
    ]);
    $manager->assignRole('employee');

    // создание механиков
    $mechanics = [
        ['name' => 'Петр',  'email_name' => 'petr'],
        ['name' => 'Сидор', 'email_name' => 'sidor'],
        ['name' => 'Федор', 'email_name' => 'fedor'],
    ];

    foreach ($mechanics as $mechanicData) {
        $mechanic = \App\Models\User::create([
            'surname'  => 'Мастер',
            'name'     => $mechanicData['name'],
            'email'    => $mechanicData['email_name'] . '@mail.ru',
            'password' => bcrypt('password'),
            'position' => 'механик'
        ]);
        $mechanic->assignRole('employee');
    }
    
    //создание клиентов
    $clients = [
        ['surname' => 'Васильев', 'name' => 'Василий', 'email' => 'client1@mail.ru'],
        ['surname' => 'Петров', 'name' => 'Петр', 'email' => 'client2@mail.ru'],
        ['surname' => 'Смирнов', 'name' => 'Дмитрий', 'email' => 'client3@mail.ru'],
    ];

    foreach ($clients as $clientData) {
        $client = \App\Models\User::create(array_merge($clientData, [
            'password' => bcrypt('password')
        ]));
        $client->assignRole('client');
        $client->update(['balance' => 25000, 'discount' => 5]);

        //создание каждому по 2 автомобиля
        \App\Models\Car::create([
            'user_id' => $client->id,
            'brand' => 'Toyota', 'model' => 'Camry', 'year' => 2020, 
            'vin' => 'VIN-' . uniqid(), 'license_plate' => 'А' . rand(100,999) . 'АА'
        ]);
        \App\Models\Car::create([
            'user_id' => $client->id,
            'brand' => 'BMW', 'model' => 'X5', 'year' => 2022, 
            'vin' => 'VIN-' . uniqid(), 'license_plate' => 'В' . rand(100,999) . 'ВВ'
        ]);
    }

    $this->call(OrderSeeder::class);
}
}
