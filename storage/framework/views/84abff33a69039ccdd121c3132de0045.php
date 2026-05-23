<?php
    $statusLabels = [
        'new' => 'Новый',
        'accepted' => 'Принят',
        'in_progress' => 'В работе',
        'ready' => 'Готов',
        'completed' => 'Завершён',
        'cancelled' => 'Отменён',
    ];
?>
<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->isAdmin()): ?>
                Панель администратора
            <?php elseif($user->isClient()): ?>
                Личный кабинет
            <?php elseif($user->isManager()): ?>
                Панель менеджера
            <?php elseif($user->isMechanic()): ?>
                Рабочее место механика
            <?php else: ?>
                Рабочий стол
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('message')): ?>
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg font-medium"><?php echo e(session('message')); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600">Здравствуйте, <strong class="text-gray-900"><?php echo e($user->full_name); ?></strong></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->isClient()): ?>
                    <div class="mt-4 flex flex-wrap items-center gap-4">
                        <p class="text-sm">Скидка: <span class="font-bold text-indigo-600"><?php echo e($user->discount); ?>%</span>
                            · Баланс: <span class="font-bold text-green-600"><?php echo e(number_format($user->getBalance(), 2, '.', ' ')); ?> ₽</span></p>
                        <a href="<?php echo e(route('wallet.topup')); ?>" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 shadow">
                            Пополнить баланс
                        </a>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->isAdmin()): ?>
                <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-xl p-6 text-white shadow-lg">
                    <h3 class="text-xl font-bold mb-1">Обзор автосервиса</h3>
                    <p class="text-slate-300 text-sm">Сводная статистика по всей CRM</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    <div class="bg-white rounded-xl border p-4 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Пользователи</div>
                        <div class="text-2xl font-bold text-slate-800"><?php echo e($usersCount ?? 0); ?></div>
                        <div class="text-xs text-gray-400 mt-1"><?php echo e($clientsCount ?? 0); ?> клиентов · <?php echo e($employeesCount ?? 0); ?> сотрудников</div>
                    </div>
                    <div class="bg-white rounded-xl border p-4 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Заказы</div>
                        <div class="text-2xl font-bold text-indigo-600"><?php echo e($ordersCount ?? 0); ?></div>
                        <div class="text-xs text-gray-400 mt-1"><?php echo e($activeOrders ?? 0); ?> активных · <?php echo e($ordersToday ?? 0); ?> сегодня</div>
                    </div>
                    <div class="bg-white rounded-xl border p-4 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Выручка (месяц)</div>
                        <div class="text-xl font-bold text-emerald-600"><?php echo e(number_format($revenueMonth ?? 0, 0, '.', ' ')); ?> ₽</div>
                        <div class="text-xs text-gray-400 mt-1">всего <?php echo e(number_format($paymentsTotal ?? 0, 0, '.', ' ')); ?> ₽</div>
                    </div>
                    <div class="bg-white rounded-xl border p-4 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Запчасти</div>
                        <div class="text-2xl font-bold text-amber-600"><?php echo e($partsCount ?? 0); ?></div>
                        <div class="text-xs text-red-500 mt-1"><?php echo e($lowStockCount ?? 0); ?> с малым остатком</div>
                    </div>
                    <div class="bg-white rounded-xl border p-4 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Услуги</div>
                        <div class="text-2xl font-bold text-slate-700"><?php echo e($servicesCount ?? 0); ?></div>
                        <div class="text-xs text-gray-400 mt-1"><?php echo e($suppliersCount ?? 0); ?> поставщиков</div>
                    </div>
                    <div class="bg-white rounded-xl border p-4 shadow-sm">
                        <div class="text-xs text-gray-500 uppercase">Боксы</div>
                        <div class="text-2xl font-bold text-blue-600"><?php echo e($busyWorkplaces ?? 0); ?>/<?php echo e($totalWorkplaces ?? 0); ?></div>
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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentOrders ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <tr class="border-t">
                                            <td class="p-2"><a href="<?php echo e(route('orders.detail', $order->id)); ?>" class="text-indigo-600">#<?php echo e($order->id); ?></a></td>
                                            <td class="p-2"><?php echo e($order->client?->name); ?></td>
                                            <td class="p-2"><?php echo e($order->car?->brand); ?> <?php echo e($order->car?->model); ?></td>
                                            <td class="p-2"><?php echo e($statusLabels[$order->status] ?? $order->status); ?></td>
                                            <td class="p-2 font-medium"><?php echo e(number_format($order->total_amount, 0)); ?> ₽</td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <tr><td colspan="5" class="p-4 text-gray-500">Заказов нет</td></tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white rounded-xl shadow p-6">
                            <h3 class="font-bold text-gray-800 mb-3">Заказы по статусам</h3>
                            <ul class="space-y-2 text-sm">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $ordersByStatus ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $cnt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <li class="flex justify-between">
                                        <span><?php echo e($statusLabels[$status] ?? $status); ?></span>
                                        <span class="font-bold"><?php echo e($cnt); ?></span>
                                    </li>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <li class="text-gray-500">Нет данных</li>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </ul>
                            <p class="mt-3 text-xs text-gray-500">Ожидают поставки: <strong><?php echo e($purchasesPending ?? 0); ?></strong> закупок</p>
                        </div>

                        <div class="bg-white rounded-xl shadow p-6">
                            <h3 class="font-bold text-gray-800 mb-3">Управление</h3>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <a href="<?php echo e(route('users.index')); ?>" class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-center hover:bg-indigo-700">Пользователи</a>
                                <a href="<?php echo e(route('rooms.index')); ?>" class="px-3 py-2 bg-slate-800 text-white rounded-lg text-center hover:bg-slate-900">Помещения</a>
                                <a href="<?php echo e(route('parts.index')); ?>" class="px-3 py-2 border rounded-lg text-center hover:bg-gray-50">Запчасти</a>
                                <a href="<?php echo e(route('purchases.index')); ?>" class="px-3 py-2 border rounded-lg text-center hover:bg-gray-50">Закупки</a>
                                <a href="<?php echo e(route('orders.index')); ?>" class="px-3 py-2 border rounded-lg text-center hover:bg-gray-50 col-span-2">Все заказы</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->isEmployee() && ($user->isManager() || (!$user->isMechanic() && !$user->isAdmin()))): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-emerald-600 text-white rounded-lg p-5 shadow">
                        <div class="text-sm opacity-90">Выручка за сегодня</div>
                        <div class="text-2xl font-bold"><?php echo e(number_format($revenueDay ?? 0, 2, '.', ' ')); ?> ₽</div>
                    </div>
                    <div class="bg-emerald-700 text-white rounded-lg p-5 shadow">
                        <div class="text-sm opacity-90">За неделю</div>
                        <div class="text-2xl font-bold"><?php echo e(number_format($revenueWeek ?? 0, 2, '.', ' ')); ?> ₽</div>
                    </div>
                    <div class="bg-emerald-800 text-white rounded-lg p-5 shadow">
                        <div class="text-sm opacity-90">За месяц</div>
                        <div class="text-2xl font-bold"><?php echo e(number_format($revenueMonth ?? 0, 2, '.', ' ')); ?> ₽</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-800 mb-3">Заказы по статусам (всего <?php echo e($totalOrders ?? 0); ?>)</h3>
                        <ul class="space-y-2 text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $ordersByStatus ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $cnt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <li class="flex justify-between border-b pb-1">
                                    <span><?php echo e($statusLabels[$status] ?? $status); ?></span>
                                    <span class="font-semibold"><?php echo e($cnt); ?></span>
                                </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <li class="text-gray-500">Нет заказов</li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-800 mb-3">Занятость боксов</h3>
                        <ul class="space-y-2 text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $workplaceOccupancy ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <li class="flex justify-between items-center p-2 rounded <?php echo e($row['busy'] ? 'bg-red-50' : 'bg-green-50'); ?>">
                                    <span><?php echo e($row['workplace']->room?->name); ?> — <?php echo e($row['workplace']->name); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['busy']): ?>
                                        <span class="text-red-700 font-medium text-xs">Занят #<?php echo e($row['order']->id); ?></span>
                                    <?php else: ?>
                                        <span class="text-green-700 text-xs">Свободен</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                            <h3 class="font-bold text-gray-800">Сотрудники и прогресс</h3>
                            <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="flex flex-wrap gap-2 text-sm">
                                <select name="position" class="rounded border-gray-300 text-sm" onchange="this.form.submit()">
                                    <option value="all" <?php if(($employeePositionFilter ?? 'all') === 'all'): echo 'selected'; endif; ?>>Все должности</option>
                                    <option value="manager" <?php if(($employeePositionFilter ?? '') === 'manager'): echo 'selected'; endif; ?>>Менеджеры</option>
                                    <option value="mechanic" <?php if(($employeePositionFilter ?? '') === 'mechanic'): echo 'selected'; endif; ?>>Механики</option>
                                </select>
                                <select name="sort" class="rounded border-gray-300 text-sm" onchange="this.form.submit()">
                                    <option value="completed" <?php if(($employeeSort ?? 'completed') === 'completed'): echo 'selected'; endif; ?>>По завершённым</option>
                                    <option value="active" <?php if(($employeeSort ?? '') === 'active'): echo 'selected'; endif; ?>>По активным</option>
                                    <option value="revenue" <?php if(($employeeSort ?? '') === 'revenue'): echo 'selected'; endif; ?>>По выручке</option>
                                    <option value="name" <?php if(($employeeSort ?? '') === 'name'): echo 'selected'; endif; ?>>По имени</option>
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $employeeRows ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <tr>
                                        <td class="p-2 border"><?php echo e($row['employee']->full_name); ?></td>
                                        <td class="p-2 border text-gray-600"><?php echo e($row['employee']->position ?? '—'); ?></td>
                                        <td class="p-2 border text-center font-medium"><?php echo e($row['active_count']); ?></td>
                                        <td class="p-2 border text-center font-bold"><?php echo e($row['completed_count']); ?></td>
                                        <td class="p-2 border text-right"><?php echo e(number_format($row['revenue'], 0, '.', ' ')); ?> ₽</td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <tr><td colspan="5" class="p-4 text-gray-500">Сотрудников нет</td></tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-red-800 mb-3">Критический остаток запчастей (≤ 3 шт.)</h3>
                        <ul class="text-sm space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $lowStockParts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <li class="flex justify-between border-b pb-1">
                                    <span><?php echo e($part->name); ?></span>
                                    <span class="font-bold text-red-600"><?php echo e((int) ($part->stock_qty ?? 0)); ?> шт.</span>
                                </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <li class="text-gray-500">Все позиции в норме</li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                        <a href="<?php echo e(route('parts.index')); ?>" class="inline-block mt-4 text-indigo-600 text-sm hover:underline">Каталог запчастей →</a>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->isMechanic()): ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-yellow-800 mb-3">Мои заказы в работе</h3>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $mechanicActive ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <a href="<?php echo e(route('orders.detail', $order->id)); ?>" class="block p-3 mb-2 border rounded hover:bg-gray-50">
                                <span class="font-bold">#<?php echo e($order->id); ?></span>
                                <?php echo e($order->car?->brand); ?> <?php echo e($order->car?->model); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->workplace): ?><span class="text-xs text-gray-500">· <?php echo e($order->workplace->name); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <p class="text-gray-500 text-sm">Нет активных назначений</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-blue-800 mb-3">Очередь (статус «Принят»)</h3>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $mechanicQueue ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <a href="<?php echo e(route('orders.detail', $order->id)); ?>" class="block p-3 mb-2 border border-blue-100 rounded hover:bg-blue-50 text-sm">
                                #<?php echo e($order->id); ?> — <?php echo e($order->client?->name); ?> · <?php echo e($order->car?->license_plate); ?>

                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <p class="text-gray-500 text-sm">Очередь пуста</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-gray-800 mb-3">Недавно завершённые мной</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="bg-gray-100"><th class="p-2">№</th><th class="p-2">Авто</th><th class="p-2">Сумма</th></tr></thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $mechanicCompleted ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <tr>
                                        <td class="p-2 border"><a href="<?php echo e(route('orders.detail', $order->id)); ?>" class="text-indigo-600">#<?php echo e($order->id); ?></a></td>
                                        <td class="p-2 border"><?php echo e($order->car?->brand); ?> <?php echo e($order->car?->model); ?></td>
                                        <td class="p-2 border"><?php echo e($order->total_amount); ?> ₽</td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <tr><td colspan="3" class="p-4 text-gray-500">Пока нет завершённых</td></tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->isClient()): ?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('client-order-payments', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1411450695-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-gray-800 mb-3">Мои автомобили</h3>
                        <ul class="space-y-2 text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $clientCars ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $car): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <li class="p-3 bg-gray-50 rounded border">
                                    <strong><?php echo e($car->brand); ?> <?php echo e($car->model); ?></strong> (<?php echo e($car->year); ?>)
                                    <div class="text-gray-500"><?php echo e($car->license_plate ?? 'без номера'); ?></div>
                                </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <li class="text-gray-500">Автомобили не добавлены</li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                        <a href="<?php echo e(route('cars.index')); ?>" class="inline-block mt-3 text-indigo-600 text-sm">Все автомобили →</a>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-bold text-indigo-800 mb-3">Активные заказы</h3>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $clientActiveOrders ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <a href="<?php echo e(route('orders.detail', $order->id)); ?>" class="block p-3 mb-2 border-l-4 border-indigo-500 bg-indigo-50 rounded">
                                <span class="font-bold">Заказ #<?php echo e($order->id); ?></span>
                                <span class="text-xs ml-2 px-2 py-0.5 bg-white rounded"><?php echo e($statusLabels[$order->status] ?? $order->status); ?></span>
                                <div class="text-sm text-gray-600 mt-1"><?php echo e($order->car?->brand); ?> <?php echo e($order->car?->model); ?> · <?php echo e(number_format($order->total_amount, 2)); ?> ₽</div>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <p class="text-gray-500 text-sm">Нет активных заказов</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $clientOrders ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <tr>
                                        <td class="p-2 border"><a href="<?php echo e(route('orders.detail', $order->id)); ?>" class="text-indigo-600">#<?php echo e($order->id); ?></a></td>
                                        <td class="p-2 border"><?php echo e($order->ordered_at?->format('d.m.Y')); ?></td>
                                        <td class="p-2 border"><?php echo e($statusLabels[$order->status] ?? $order->status); ?></td>
                                        <td class="p-2 border"><?php echo e(\App\Models\Order::paymentStatusLabel($order->payment_status)); ?></td>
                                        <td class="p-2 border"><?php echo e(number_format($order->total_amount, 2)); ?> ₽</td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <tr><td colspan="5" class="p-4 text-gray-500">Заказов пока нет</td></tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\Users\1\php_project\crm\resources\views/dashboard.blade.php ENDPATH**/ ?>