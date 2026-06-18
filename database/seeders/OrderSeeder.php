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
        $clients = User::role('client')->get()->keyBy('email');
        $manager = User::where('email', 'manager@mail.ru')->first();
        $petr = User::where('email', 'petr@mail.ru')->first();
        $sidor = User::where('email', 'sidor@mail.ru')->first();
        $fedor = User::where('email', 'fedor@mail.ru')->first();

        $workplaces = Workplace::orderBy('id')->get();
        $services = Service::all()->keyBy('name');
        $part = Part::first();

        if ($clients->isEmpty() || ! $manager || $workplaces->count() < 3) {
            return;
        }

        $vasily = $clients->get('client1@mail.ru');
        $petrClient = $clients->get('client2@mail.ru');
        $dmitry = $clients->get('client3@mail.ru');

        $orders = [
            [
                'client' => $vasily,
                'car_index' => 0,
                'workplace' => $workplaces[0],
                'employee' => $petr,
                'status' => 'in_progress',
                'ordered_at' => now()->subDays(2),
                'deadline' => now()->addDay(),
                'items' => [
                    ['type' => 'service', 'name' => 'Замена масла и фильтра', 'qty' => 1],
                    ['type' => 'part', 'part' => $part, 'qty' => 1],
                ],
            ],
            [
                'client' => $petrClient,
                'car_index' => 0,
                'workplace' => $workplaces[1],
                'employee' => $sidor,
                'status' => 'in_progress',
                'ordered_at' => now()->subDay(),
                'deadline' => now()->addDays(2),
                'items' => [
                    ['type' => 'service', 'name' => 'Компьютерная диагностика', 'qty' => 1],
                ],
            ],
            [
                'client' => $dmitry,
                'car_index' => 1,
                'workplace' => $workplaces[2],
                'employee' => $fedor,
                'status' => 'in_progress',
                'ordered_at' => now()->subHours(6),
                'deadline' => now()->addDays(3),
                'items' => [
                    ['type' => 'service', 'name' => 'Развал-схождение', 'qty' => 1],
                    ['type' => 'service', 'name' => 'Шиномонтаж (4 колеса)', 'qty' => 1],
                ],
            ],
            [
                'client' => $vasily,
                'car_index' => 1,
                'workplace' => null,
                'employee' => $manager,
                'status' => 'accepted',
                'ordered_at' => now()->subHours(3),
                'items' => [],
            ],
            [
                'client' => $petrClient,
                'car_index' => 1,
                'workplace' => null,
                'employee' => $manager,
                'status' => 'completed',
                'ordered_at' => now()->subDays(12),
                'completed_at' => now()->subDays(10),
                'items' => [
                    ['type' => 'service', 'name' => 'Замена тормозных колодок (перед)', 'qty' => 1],
                ],
                'payment' => true,
            ],
            [
                'client' => $dmitry,
                'car_index' => 0,
                'workplace' => null,
                'employee' => $petr,
                'status' => 'ready',
                'ordered_at' => now()->subDays(4),
                'items' => [
                    ['type' => 'service', 'name' => 'Замена свечей зажигания', 'qty' => 1],
                ],
            ],
        ];

        foreach ($orders as $data) {
            if (! $data['client']) {
                continue;
            }

            $car = Car::where('user_id', $data['client']->id)
                ->skip($data['car_index'])
                ->first()
                ?? Car::where('user_id', $data['client']->id)->first();

            if (! $car) {
                continue;
            }

            $order = Order::create([
                'user_id' => $data['client']->id,
                'car_id' => $car->id,
                'workplace_id' => $data['workplace']?->id,
                'employee_id' => ($data['employee'] ?? $manager)->id,
                'ordered_at' => $data['ordered_at'],
                'deadline' => $data['deadline'] ?? null,
                'completed_at' => $data['completed_at'] ?? null,
                'status' => $data['status'],
                'subtotal_amount' => 0,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
            ]);

            foreach ($data['items'] as $item) {
                if ($item['type'] === 'service') {
                    $service = $services->get($item['name']);
                    if (! $service) {
                        continue;
                    }
                    OrderItem::create([
                        'order_id' => $order->id,
                        'type' => 'service',
                        'service_id' => $service->id,
                        'quantity' => $item['qty'],
                        'price' => $service->price,
                        'employee_id' => $order->employee_id,
                    ]);
                } elseif ($item['type'] === 'part' && ($item['part'] ?? null)) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'type' => 'part',
                        'part_id' => $item['part']->id,
                        'quantity' => $item['qty'],
                        'price' => $item['part']->retail_price,
                    ]);
                }
            }

            $order->load('items', 'client');
            $order->applyClientDiscount();

            if (! empty($data['payment'])) {
                Payment::create([
                    'order_id' => $order->id,
                    'user_id' => $data['client']->id,
                    'paid_at' => $order->completed_at ?? now(),
                    'amount' => $order->total_amount,
                    'method' => 'wallet',
                ]);
            }
        }
    }
}
