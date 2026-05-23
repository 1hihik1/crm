<?php

use App\Models\Order;
use App\Models\Payment;
use App\Livewire\Concerns\WithPaymentConfirmation;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    use WithPaymentConfirmation;

    public function with(): array
    {
        $user = Auth::user();
        $orders = collect();

        if ($user?->isClient()) {
            $orders = Order::with(['car', 'payments'])
                ->where('user_id', $user->id)
                ->latest('ordered_at')
                ->get()
                ->filter(fn (Order $o) => $o->due_amount > 0 && $o->status !== 'cancelled');
        }

        return [
            'unpaidOrders' => $orders,
            'statusLabels' => collect(Order::statusLabels())->mapWithKeys(fn ($v, $k) => [$k => $v['label']])->all(),
        ];
    }

    public function payOrder(int $orderId): void
    {
        $user = Auth::user();
        if (! $user?->isClient()) {
            abort(403);
        }

        $order = Order::with(['payments', 'client'])
            ->where('user_id', $user->id)
            ->findOrFail($orderId);

        $due = $order->due_amount;

        if ($due <= 0) {
            session()->flash('payment_message', 'Заказ #'.$order->id.' уже оплачен.');

            return;
        }

        if (! $user->canAfford($due)) {
            session()->flash('payment_message', 'Недостаточно средств. Нужно '.number_format($due, 2, '.', ' ').' ₽. Пополните баланс.');

            return;
        }

        DB::transaction(function () use ($user, $order, $due) {
            if (! $user->withdrawAmount($due)) {
                throw new \RuntimeException('Не удалось списать средства.');
            }

            Payment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'paid_at' => now(),
                'amount' => $due,
                'method' => 'wallet',
            ]);
        });

        session()->flash('payment_message', 'Заказ #'.$order->id.' оплачен: '.number_format($due, 2, '.', ' ').' ₽.');
    }
}; ?>

<div>
    @include('livewire.partials.payment-modal')

    @if (session()->has('payment_message'))
        <div class="mb-4 p-4 rounded-lg text-sm font-medium {{ str_contains(session('payment_message'), 'оплачен') ? 'bg-green-50 text-green-800' : 'bg-amber-50 text-amber-900' }}">
            {{ session('payment_message') }}
        </div>
    @endif

    @if($unpaidOrders->isNotEmpty())
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-amber-500 mb-6">
            <h3 class="font-bold text-amber-900 mb-3">Заказы к оплате</h3>
            <div class="space-y-3">
                @foreach($unpaidOrders as $order)
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-amber-50 rounded-lg border border-amber-100">
                        <div>
                            <a href="{{ route('orders.detail', $order->id) }}" class="font-bold text-indigo-700 hover:underline">Заказ #{{ $order->id }}</a>
                            <div class="text-sm text-gray-600 mt-1">
                                {{ $order->car?->brand }} {{ $order->car?->model }}
                                · {{ $statusLabels[$order->status] ?? $order->status }}
                            </div>
                            <div class="text-sm font-semibold text-amber-800 mt-1">
                                К оплате: {{ number_format($order->due_amount, 2, '.', ' ') }} ₽
                                @if($order->discount_amount > 0)
                                    <span class="text-indigo-600 font-normal">(скидка {{ $order->discount_percent }}%)</span>
                                @endif
                            </div>
                        </div>
                        <button type="button"
                                wire:click="askPayOrder({{ $order->id }}, {{ $order->due_amount }}, @js('заказ #'.$order->id))"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">
                            Оплатить с баланса
                        </button>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-500 mt-3">
                <a href="{{ route('wallet.topup') }}" class="text-indigo-600 hover:underline">Пополнить баланс</a>
            </p>
        </div>
    @endif
</div>
