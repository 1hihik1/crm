@if($showCompleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-modal="true" role="dialog">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="cancelComplete"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-green-100">
            <div class="flex items-start gap-4">
                <div class="shrink-0 w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900">Завершить заказ?</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Вы действительно хотите завершить <strong class="text-gray-900">{{ $completeTargetLabel }}</strong>
                        и передать автомобиль клиенту?
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Статус заказа будет изменён на «Завершён», будет зафиксирована дата завершения.</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" wire:click="cancelComplete">Отмена</x-secondary-button>
                <button type="button" wire:click="performComplete"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
                    Завершить заказ
                </button>
            </div>
        </div>
    </div>
@endif
