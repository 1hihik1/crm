<?php

use App\Models\Order;
use App\Livewire\Concerns\WithCompleteOrderConfirmation;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <?php echo $__env->make('livewire.partials.complete-order-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        
        <!-- Шапка: Заголовок и кнопка создания -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Управление заказами</h2>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!Auth::user()->isClient()): ?>
                <!-- Перенаправляет на страницу создания (которую мы сделаем следующей) -->
                <a href="<?php echo e(route('orders.detail', ['id' => 'new'])); ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow">
                    + Создать заказ
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            
            <!-- Панель управления: Фильтры и Поиск -->
            <div class="p-4 border-b bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
                
                <!-- Вкладки фильтров (Кнопки) -->
                <div class="flex space-x-2 overflow-x-auto pb-2 md:pb-0 w-full md:w-auto">
                    <button wire:click="setFilter('')" 
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors 
                            <?php echo e($filterStatus === '' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 hover:bg-gray-200 border'); ?>">
                        Все
                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <button wire:click="setFilter('<?php echo e($key); ?>')" 
                                class="px-4 py-2 rounded-full text-sm font-medium transition-colors border
                                <?php echo e($filterStatus === $key ? $status['color'] . ' border-transparent ring-2 ring-offset-1 ring-gray-400' : 'bg-white text-gray-600 hover:bg-gray-100'); ?>">
                            <?php echo e($status['label']); ?>

                        </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>

                <!-- Поиск -->
                <div class="w-full md:w-1/3 relative">
                    <input wire:model.live="search" type="text" placeholder="Поиск (№, Клиент, Госномер)..." 
                           class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Таблица -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">№ / Дата</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Клиент / Авто</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Итого</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                
                                <!-- ID и Дата -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900">#<?php echo e($order->id); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($order->ordered_at?->format('d.m.Y H:i')); ?></div>
                                </td>

                                <!-- Клиент и Авто -->
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($order->client?->name ?? 'Неизвестен'); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($order->car?->brand); ?> <?php echo e($order->car?->model); ?> (<?php echo e($order->car?->license_plate ?? 'Б/Н'); ?>)</div>
                                </td>

                                <!-- Статус (Цветной бейдж) -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                        $statusClass = $statuses[$order->status]['color'] ?? 'bg-gray-100 text-gray-800';
                                        $statusLabel = $statuses[$order->status]['label'] ?? $order->status;
                                    ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo e($statusClass); ?>">
                                        <?php echo e($statusLabel); ?>

                                    </span>
                                </td>

                                <!-- Сумма -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    <?php echo e(number_format($order->total_amount, 2, '.', ' ')); ?> ₽
                                </td>

                                <!-- Действия (Умные кнопки) -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-3">
                                        
                                        <!-- Ссылка внутрь заказа (Детали) -->
                                        <a href="<?php echo e(route('orders.detail', $order->id)); ?>" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded transition-colors">
                                            Открыть
                                        </a>

                                        <!-- Быстрые действия только для персонала -->
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!Auth::user()?->isClient()): ?>
                                            <!-- Если новый -> В работу -->
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === 'new'): ?>
                                                <button wire:click="changeStatus(<?php echo e($order->id); ?>, 'in_progress')" class="text-yellow-600 hover:text-yellow-900 font-bold" title="Взять в работу">
                                                    В работу
                                                </button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                            <!-- Если в работе -> Готов -->
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === 'in_progress'): ?>
                                                <button wire:click="changeStatus(<?php echo e($order->id); ?>, 'ready')" class="text-purple-600 hover:text-purple-900 font-bold" title="Отметить готовым">
                                                    Готов
                                                </button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                            <!-- Если готов -> Завершить (выдать клиенту) -->
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === 'ready'): ?>
                                                <button type="button"
                                                        wire:click="askCompleteOrder(<?php echo e($order->id); ?>, <?php echo \Illuminate\Support\Js::from('заказ #'.$order->id)->toHtml() ?>)"
                                                        class="text-green-600 hover:text-green-900 font-bold"
                                                        title="Завершить заказ">
                                                    Завершить
                                                </button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            
                                            <!-- Удалить -->
                                            <button wire:click="deleteOrder(<?php echo e($order->id); ?>)" wire:confirm="Точно удалить заказ?" class="text-red-400 hover:text-red-600 ml-2" title="Удалить">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Заказов пока нет или они не найдены.
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="p-4 border-t bg-gray-50">
                <?php echo e($orders->links()); ?>

            </div>
        </div>
    </div>
</div><?php /**PATH C:\Users\1\php_project\crm\resources\views\livewire/order-list.blade.php ENDPATH**/ ?>