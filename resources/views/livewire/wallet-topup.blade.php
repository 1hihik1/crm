<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public string $amount = '';

    public ?int $preset = null;

    protected function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:100|max:500000',
        ];
    }

    public function selectPreset(int $value): void
    {
        $this->preset = $value;
        $this->amount = (string) $value;
    }

    public function topUp(): void
    {
        $this->validate();

        $user = Auth::user();
        $user->depositAmount((float) $this->amount);

        session()->flash('message', 'Баланс пополнен на '.number_format((float) $this->amount, 2, '.', ' ').' ₽.');
        $this->reset(['amount', 'preset']);
    }

    public function with(): array
    {
        return [
            'balance' => Auth::user()->getBalance(),
            'presets' => [1000, 3000, 5000, 10000, 25000],
        ];
    }
}; ?>

<div class="py-12">
    <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">← Назад в кабинет</a>
        </div>

        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 px-6 py-8 text-white">
                <h2 class="text-2xl font-bold">Пополнение баланса</h2>
                <p class="text-indigo-100 text-sm mt-1">Средства используются для оплаты заказов и услуг</p>
                <p class="mt-4 text-3xl font-extrabold">{{ number_format($balance, 2, '.', ' ') }} ₽</p>
                <p class="text-xs text-indigo-200 mt-1">текущий баланс</p>
            </div>

            <div class="p-6">
                @if (session()->has('message'))
                    <div class="mb-4 p-4 bg-green-50 text-green-800 rounded-lg text-sm font-medium">
                        {{ session('message') }}
                    </div>
                @endif

                <p class="text-sm text-gray-600 mb-4">Выберите сумму или введите свою (демо-пополнение без реального платёжного шлюза):</p>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-6">
                    @foreach($presets as $sum)
                        <button type="button" wire:click="selectPreset({{ $sum }})"
                                class="py-3 rounded-lg border-2 font-semibold transition
                                {{ (int) $amount === $sum ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-gray-200 hover:border-indigo-300 text-gray-700' }}">
                            {{ number_format($sum, 0, '.', ' ') }} ₽
                        </button>
                    @endforeach
                </div>

                <div class="mb-6">
                    <x-input-label value="Сумма пополнения, ₽" />
                    <x-text-input wire:model.live="amount" type="number" min="100" step="100" class="w-full mt-1 text-lg" placeholder="1000" />
                    <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                </div>

                <x-primary-button type="button" wire:click="topUp" class="w-full justify-center py-3 text-base">
                    Пополнить баланс
                </x-primary-button>

                <p class="mt-4 text-xs text-gray-400 text-center">
                    После пополнения оплатить заказ можно в личном кабинете или в карточке заказа.
                </p>
            </div>
        </div>
    </div>
</div>
