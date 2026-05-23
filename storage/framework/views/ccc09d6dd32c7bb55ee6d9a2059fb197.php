<?php

use App\Models\Order;
use App\Models\Payment;
use App\Livewire\Concerns\WithPaymentConfirmation;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

?>

<div>
    <?php echo $__env->make('livewire.partials.payment-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('payment_message')): ?>
        <div class="mb-4 p-4 rounded-lg text-sm font-medium <?php echo e(str_contains(session('payment_message'), 'оплачен') ? 'bg-green-50 text-green-800' : 'bg-amber-50 text-amber-900'); ?>">
            <?php echo e(session('payment_message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unpaidOrders->isNotEmpty()): ?>
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-amber-500 mb-6">
            <h3 class="font-bold text-amber-900 mb-3">Заказы к оплате</h3>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $unpaidOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-amber-50 rounded-lg border border-amber-100">
                        <div>
                            <a href="<?php echo e(route('orders.detail', $order->id)); ?>" class="font-bold text-indigo-700 hover:underline">Заказ #<?php echo e($order->id); ?></a>
                            <div class="text-sm text-gray-600 mt-1">
                                <?php echo e($order->car?->brand); ?> <?php echo e($order->car?->model); ?>

                                · <?php echo e($statusLabels[$order->status] ?? $order->status); ?>

                            </div>
                            <div class="text-sm font-semibold text-amber-800 mt-1">
                                К оплате: <?php echo e(number_format($order->due_amount, 2, '.', ' ')); ?> ₽
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->discount_amount > 0): ?>
                                    <span class="text-indigo-600 font-normal">(скидка <?php echo e($order->discount_percent); ?>%)</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <button type="button"
                                wire:click="askPayOrder(<?php echo e($order->id); ?>, <?php echo e($order->due_amount); ?>, <?php echo \Illuminate\Support\Js::from('заказ #'.$order->id)->toHtml() ?>)"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">
                            Оплатить с баланса
                        </button>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <p class="text-xs text-gray-500 mt-3">
                <a href="<?php echo e(route('wallet.topup')); ?>" class="text-indigo-600 hover:underline">Пополнить баланс</a>
            </p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\Users\1\php_project\crm\resources\views\livewire/client-order-payments.blade.php ENDPATH**/ ?>