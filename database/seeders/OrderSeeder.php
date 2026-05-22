<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Part;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\Workplace;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $client = User::role('client')->first();
        $manager = User::where('email', 'manager@mail.ru')->first();
        $mechanic = User::where('email', 'petr@mail.ru')->first();
        $car = $client ? Car::where('user_id', $client->id)->first() : null;
        $workplace = Workplace::first();
        $service = Service::first();
        $part = Part::first();

        if (! $client || ! $manager || ! $car || ! $service) {
            return;
        }

        $order1 = Order::create([
            'user_id' => $client->id,
            'car_id' => $car->id,
            'workplace_id' => $workplace?->id,
            'employee_id' => $mechanic?->id ?? $manager->id,
            'ordered_at' => now()->subDays(2),
            'deadline' => now()->addDay(),
            'status' => 'in_progress',
            'total_amount' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'type' => 'service',
            'service_id' => $service->id,
            'quantity' => 1,
            'price' => $service->price,
            'employee_id' => $mechanic?->id,
        ]);

        if ($part) {
            OrderItem::create([
                'order_id' => $order1->id,
                'type' => 'part',
                'part_id' => $part->id,
                'quantity' => 1,
                'price' => $part->retail_price,
            ]);
        }

        $total1 = $order1->items()->get()->sum(fn ($i) => $i->price * $i->quantity);
        $order1->update(['total_amount' => $total1]);

        $order2 = Order::create([
            'user_id' => $client->id,
            'car_id' => $car->id,
            'employee_id' => $manager->id,
            'ordered_at' => now()->subDays(10),
            'completed_at' => now()->subDays(8),
            'status' => 'completed',
            'total_amount' => 3500,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'type' => 'service',
            'service_id' => $service->id,
            'quantity' => 1,
            'price' => 3500,
            'employee_id' => $manager->id,
        ]);

        Payment::create([
            'order_id' => $order2->id,
            'user_id' => $manager->id,
            'paid_at' => now()->subDays(8),
            'amount' => 3500,
            'method' => 'card',
        ]);

        Order::create([
            'user_id' => $client->id,
            'car_id' => Car::where('user_id', $client->id)->skip(1)->value('id') ?? $car->id,
            'employee_id' => $manager->id,
            'ordered_at' => now()->subHours(3),
            'status' => 'accepted',
            'total_amount' => 0,
        ]);
    }
}
