@if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-modal="true" role="dialog">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="cancelPayment"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-indigo-100">
            <div class="flex items-start gap-4">
                <div class="shrink-0 w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900">Подтвердите оплату</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Списать <strong class="text-indigo-700">{{ number_format($paymentAmount, 2, '.', ' ') }} ₽</strong>
                        с баланса за <strong class="text-gray-900">{{ $paymentTargetLabel }}</strong>?
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Средства будут списаны с вашего кошелька. Отменить операцию после подтверждения нельзя.</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" wire:click="cancelPayment">Отмена</x-secondary-button>
                <button type="button" wire:click="performPayment"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    Оплатить
                </button>
            </div>
        </div>
    </div>
@endif
