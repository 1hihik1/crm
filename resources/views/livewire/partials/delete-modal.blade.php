@if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-modal="true" role="dialog">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="cancelDelete"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 border border-red-100">
            <div class="flex items-start gap-4">
                <div class="shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900">Подтвердите удаление</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Вы действительно хотите удалить <strong class="text-gray-900">{{ $deleteTargetLabel }}</strong>?
                        Это действие нельзя отменить.
                    </p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" wire:click="cancelDelete">Отмена</x-secondary-button>
                <button type="button" wire:click="performDelete"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition">
                    Удалить
                </button>
            </div>
        </div>
    </div>
@endif
