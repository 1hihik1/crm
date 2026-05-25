@php
    $statusLabels = [
        'new' => 'Новый',
        'accepted' => 'Принят',
        'in_progress' => 'В работе',
        'ready' => 'Готов',
        'completed' => 'Завершён',
        'cancelled' => 'Отменён',
    ];
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if($user->isAdmin())
                Панель администратора
            @elseif($user->isClient())
                Личный кабинет
            @elseif($user->isManager())
                Панель менеджера
            @elseif($user->isMechanic())
                Рабочее место механика
            @else
                Рабочий стол
            @endif
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('message'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg font-medium">{{ session('message') }}</div>
            @endif

            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600">Здравствуйте, <strong class="text-gray-900">{{ $user->full_name }}</strong></p>
                @if($user->isClient())
                    <div class="mt-4 flex flex-wrap items-center gap-4">
                        <p class="text-sm">Скидка: <span class="font-bold text-indigo-600">{{ $user->discount }}%</span>
                            · Баланс: <span class="font-bold text-green-600">{{ number_format($user->getBalance(), 2, '.', ' ') }} ₽</span></p>
                        <a href="{{ route('wallet.topup') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 shadow">
                            Пополнить баланс
                        </a>
                    </div>
                @endif
            </div>

            {{-- АДМИН --}}
            @if($user->isAdmin())
                <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-xl p-6 text-white shadow-lg">
                    <h3 class="text-xl font-bold mb-1">Обзор автосервиса</h3>
                    <p class="text-slate-300 text-sm">Сводная статистика по всей CRM</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    <div class="bg-white rounded-xl border p-4 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Пользователи</div>
                        <div class="text-2xl font-bold text-slate-800">{{ $usersCount ?? 0 }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $clientsCount ?? 0 }} клиентов · {{ $employeesCount ?? 0 }} сотрудников</div>
                    </div>
                    <div class="bg-white rounded-xl border p-4 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Заказы</div>
                        <div class="text-2xl font-bold text-indigo-600">{{ $ordersCount ?? 0 }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $activeOrders ?? 0 }} активных · {{ $ordersToday ?? 0 }} сегодня</div>
                    </div>
                    <div class="bg-white rounded-xl border p-4 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Выручка (месяц)</div>
                        <div class="text-xl font-bold text-emerald-600">{{ number_format($revenueMonth ?? 0, 0, '.', ' ') }} ₽</div>
                        <div class="text-xs text-gray-400 mt-1">всего {{ number_format($paymentsTotal ?? 0, 0, '.', ' ') }} ₽</div>
                    </div>
                    <div class="bg-white rounded-xl border p-4 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Запчасти</div>
                        <div class="text-2xl font-bold text-amber-600">{{ $partsCount ?? 0 }}</div>
                        <div class="text-xs text-red-500 mt-1">{{ $lowStockCount ?? 0 }} с малым остатком</div>
                    </div>
                    <div class="bg-white rounded-xl border p-4 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Услуги</div>
                        <div class="text-2xl font-bold text-slate-700">{{ $servicesCount ?? 0 }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $suppliersCount ?? 0 }} поставщиков</div>
                    </div>
                    <div class="bg-white rounded-xl border p-4 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Боксы</div>
                        <div class="text-2xl font-bold text-blue-600">{{ $busyWorkplaces ?? 0 }}/{{ $totalWorkplaces ?? 0 }}</div>
                        <div class="text-xs text-gray-400 mt-1">занято / всего</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white rounded-xl shadow p-6">
                        <h3 class="font-bold text-gray-800 mb-4">Последние заказы</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead><tr class="bg-gray-50 text-gray-600"><th class="p-2">№</th><th class="p-2">Клиент</th><th class="p-2">Авто</th><th class="p-2">Статус</th><th class="p-2">Сумма</th></tr></thead>
                                <tbody>
                                    @forelse($recentOrders ?? [] as $order)
                                        <tr class="border-t">
                                            <td class="p-2"><a href="{{ route('orders.detail', $order->id) }}" class="text-indigo-600">#{{ $order->id }}</a></td>
                                            <td class="p-2">{{ $order->client?->name }}</td>
                                            <td class="p-2">{{ $order->car?->brand }} {{ $order->car?->model }}</td>
                                            <td class="p-2">{{ $statusLabels[$order->status] ?? $order->status }}</td>
                                            <td class="p-2 font-medium">{{ number_format($order->total_amount, 0) }} ₽</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="p-4 text-gray-500">Заказов нет</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow p-6">
                            <h3 class="font-bold text-gray-800 mb-3">Заказы по статусам</h3>
                            <ul class="space-y-2 text-sm">
                                @forelse($ordersByStatus ?? [] as $status => $cnt)
                                    <li class="flex justify-between">
                                        <span>{{ $statusLabels[$status] ?? $status }}</span>
                                        <span class="font-bold">{{ $cnt }}</span>
                                    </li>
                                @empty
                                    <li class="text-gray-500">Нет данных</li>
                                @endforelse
                            </ul>
                            <p class="mt-3 text-xs text-gray-500">Ожидают поставки: <strong>{{ $purchasesPending ?? 0 }}</strong> закупок</p>
                        </div>

                        <div class="bg-white rounded-xl shadow p-6">
                            <h3 class="font-bold text-gray-800 mb-3">Управление</h3>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <a href="{{ route('users.index') }}" class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-center hover:bg-indigo-700">Пользователи</a>
                                <a href="{{ route('rooms.index') }}" class="px-3 py-2 bg-slate-800 text-white rounded-lg text-center hover:bg-slate-900">Помещения</a>
                                <a href="{{ route('parts.index') }}" class="px-3 py-2 border rounded-lg text-center hover:bg-gray-50">Запчасти</a>
                                <a href="{{ route('purchases.index') }}" class="px-3 py-2 border rounded-lg text-center hover:bg-gray-50">Закупки</a>
                                <a href="{{ route('orders.index') }}" class="px-3 py-2 border rounded-lg text-center hover:bg-gray-50 col-span-2">Все заказы</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                </div>
            @endif

            {{-- МЕНЕДЖЕР (и прочие сотрудники без роли механика) --}}
            @if($user->isEmployee() && ($user->isManager() || (!$user->isMechanic() && !$user->isAdmin())))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-emerald-600 text-white rounded-lg p-5 shadow">
                        <div class="text-sm opacity-90">Выручка за сегодня</div>
                        <div class="text-2xl font-bold">{{ number_format($revenueDay ?? 0, 2, '.', ' ') }} ₽</div>
                    </div>
                    <div class="bg-emerald-700 text-white rounded-lg p-5 shadow">
                        <div class="text-sm opacity-90">За неделю</div>
                        <div class="text-2xl font-bold">{{ number_format($revenueWeek ?? 0, 2, '.', ' ') }} ₽</div>
                    </div>
                    <div class="bg-emerald-800 text-white rounded-lg p-5 shadow">
                        <div class="text-sm opacity-90">За месяц</div>
                        <div class="text-2xl font-bold">{{ number_format($revenueMonth ?? 0, 2, '.', ' ') }} ₽</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-800 mb-3">Заказы по статусам (всего {{ $totalOrders ?? 0 }})</h3>
                        <ul class="space-y-2 text-sm">
                            @forelse($ordersByStatus ?? [] as $status => $cnt)
                                <li class="flex justify-between border-b pb-1">
                                    <span>{{ $statusLabels[$status] ?? $status }}</span>
                                    <span class="font-semibold">{{ $cnt }}</span>
                                </li>
                            @empty
                                <li class="text-gray-500">Нет заказов</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-800 mb-3">Занятость боксов</h3>
                        <ul class="space-y-2 text-sm">
                            @foreach($workplaceOccupancy ?? [] as $row)
                                <li class="flex justify-between items-center p-2 rounded {{ $row['busy'] ? 'bg-red-50' : 'bg-green-50' }}">
                                    <span>{{ $row['workplace']->room?->name }} — {{ $row['workplace']->name }}</span>
                                    @if($row['busy'])
                                        <span class="text-red-700 font-medium text-xs">Занят #{{ $row['order']->id }}</span>
                                    @else
                                        <span class="text-green-700 text-xs">Свободен</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                            <h3 class="font-bold text-gray-800">Сотрудники и прогресс</h3>
                            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-2 text-sm">
                                <select name="position" class="rounded border-gray-300 text-sm" onchange="this.form.submit()">
                                    <option value="all" @selected(($employeePositionFilter ?? 'all') === 'all')>Все должности</option>
                                    <option value="manager" @selected(($employeePositionFilter ?? '') === 'manager')>Менеджеры</option>
                                    <option value="mechanic" @selected(($employeePositionFilter ?? '') === 'mechanic')>Механики</option>
                                </select>
                                <select name="sort" class="rounded border-gray-300 text-sm" onchange="this.form.submit()">
                                    <option value="completed" @selected(($employeeSort ?? 'completed') === 'completed')>По завершённым</option>
                                    <option value="active" @selected(($employeeSort ?? '') === 'active')>По активным</option>
                                    <option value="revenue" @selected(($employeeSort ?? '') === 'revenue')>По выручке</option>
                                    <option value="name" @selected(($employeeSort ?? '') === 'name')>По имени</option>
                                </select>
                            </form>
                        </div>
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2">Сотрудник</th>
                                    <th class="p-2">Должность</th>
                                    <th class="p-2 text-center">Активных</th>
                                    <th class="p-2 text-center">Завершено</th>
                                    <th class="p-2 text-right">Выручка</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employeeRows ?? [] as $row)
                                    <tr>
                                        <td class="p-2 border">{{ $row['employee']->full_name }}</td>
                                        <td class="p-2 border text-gray-600">{{ $row['employee']->position ?? '—' }}</td>
                                        <td class="p-2 border text-center font-medium">{{ $row['active_count'] }}</td>
                                        <td class="p-2 border text-center font-bold">{{ $row['completed_count'] }}</td>
                                        <td class="p-2 border text-right">{{ number_format($row['revenue'], 0, '.', ' ') }} ₽</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="p-4 text-gray-500">Сотрудников нет</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-red-800 mb-3">Критический остаток запчастей (≤ 3 шт.)</h3>
                        <ul class="text-sm space-y-2">
                            @forelse($lowStockParts ?? [] as $part)
                                <li class="flex justify-between border-b pb-1">
                                    <span>{{ $part->name }}</span>
                                    <span class="font-bold text-red-600">{{ (int) ($part->stock_qty ?? 0) }} шт.</span>
                                </li>
                            @empty
                                <li class="text-gray-500">Все позиции в норме</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('parts.index') }}" class="inline-block mt-4 text-indigo-600 text-sm hover:underline">Каталог запчастей →</a>
                    </div>
                </div>
            @endif

            {{-- МЕХАНИК --}}
            @if($user->isMechanic())
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-yellow-800 mb-3">Мои заказы в работе</h3>
                        @forelse($mechanicActive ?? [] as $order)
                            <a href="{{ route('orders.detail', $order->id) }}" class="block p-3 mb-2 border rounded hover:bg-gray-50">
                                <span class="font-bold">#{{ $order->id }}</span>
                                {{ $order->car?->brand }} {{ $order->car?->model }}
                                @if($order->workplace)<span class="text-xs text-gray-500">· {{ $order->workplace->name }}</span>@endif
                            </a>
                        @empty
                            <p class="text-gray-500 text-sm">Нет активных назначений</p>
                        @endforelse
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-blue-800 mb-3">Очередь (статус «Принят»)</h3>
                        @forelse($mechanicQueue ?? [] as $order)
                            <a href="{{ route('orders.detail', $order->id) }}" class="block p-3 mb-2 border border-blue-100 rounded hover:bg-blue-50 text-sm">
                                #{{ $order->id }} — {{ $order->client?->name }} · {{ $order->car?->license_plate }}
                            </a>
                        @empty
                            <p class="text-gray-500 text-sm">Очередь пуста</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-gray-800 mb-3">Недавно завершённые мной</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="bg-gray-100"><th class="p-2">№</th><th class="p-2">Авто</th><th class="p-2">Сумма</th></tr></thead>
                            <tbody>
                                @forelse($mechanicCompleted ?? [] as $order)
                                    <tr>
                                        <td class="p-2 border"><a href="{{ route('orders.detail', $order->id) }}" class="text-indigo-600">#{{ $order->id }}</a></td>
                                        <td class="p-2 border">{{ $order->car?->brand }} {{ $order->car?->model }}</td>
                                        <td class="p-2 border">{{ $order->total_amount }} ₽</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="p-4 text-gray-500">Пока нет завершённых</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- КЛИЕНТ --}}
            @if($user->isClient())
                <livewire:client-order-payments />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-800 mb-3">Мои автомобили</h3>
                        <ul class="space-y-2 text-sm">
                            @forelse($clientCars ?? [] as $car)
                                <li class="p-3 bg-gray-50 rounded border">
                                    <strong>{{ $car->brand }} {{ $car->model }}</strong> ({{ $car->year }})
                                    <div class="text-gray-500">{{ $car->license_plate ?? 'без номера' }}</div>
                                </li>
                            @empty
                                <li class="text-gray-500">Автомобили не добавлены</li>
                            @endforelse
                        </ul>
                        <a href="{{ route('cars.index') }}" class="inline-block mt-3 text-indigo-600 text-sm">Все автомобили →</a>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-indigo-800 mb-3">Активные заказы</h3>
                        @forelse($clientActiveOrders ?? [] as $order)
                            <a href="{{ route('orders.detail', $order->id) }}" class="block p-3 mb-2 border-l-4 border-indigo-500 bg-indigo-50 rounded">
                                <span class="font-bold">Заказ #{{ $order->id }}</span>
                                <span class="text-xs ml-2 px-2 py-0.5 bg-white rounded">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                                <div class="text-sm text-gray-600 mt-1">{{ $order->car?->brand }} {{ $order->car?->model }} · {{ number_format($order->total_amount, 2) }} ₽</div>
                            </a>
                        @empty
                            <p class="text-gray-500 text-sm">Нет активных заказов</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-gray-800 mb-3">История заказов</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-2">№</th>
                                    <th class="p-2">Дата</th>
                                    <th class="p-2">Статус</th>
                                    <th class="p-2">Оплата</th>
                                    <th class="p-2">Сумма</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clientOrders ?? [] as $order)
                                    <tr>
                                        <td class="p-2 border"><a href="{{ route('orders.detail', $order->id) }}" class="text-indigo-600">#{{ $order->id }}</a></td>
                                        <td class="p-2 border">{{ $order->ordered_at?->format('d.m.Y') }}</td>
                                        <td class="p-2 border">{{ $statusLabels[$order->status] ?? $order->status }}</td>
                                        <td class="p-2 border">{{ \App\Models\Order::paymentStatusLabel($order->payment_status) }}</td>
                                        <td class="p-2 border">{{ number_format($order->total_amount, 2) }} ₽</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="p-4 text-gray-500">Заказов пока нет</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
