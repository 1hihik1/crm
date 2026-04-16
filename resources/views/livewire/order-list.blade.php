<?php

use App\Models\Order;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = ''; // Пустая строка = все заказы

    // Сброс пагинации при вводе поиска
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Установка фильтра по статусу
    public function setFilter(string $status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    // Получение данных для таблицы
    public function with(): array
    {
        // Подгружаем связи, чтобы не было N+1 проблемы
        $query = Order::with(['client', 'car', 'employee']);

        //клиент видит только свои заказы
        if (Auth::user()?->isClient()) {
            $query->where('user_id', Auth::id());
        }

        // Фильтр по статусу
        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        // Поиск по ID, имени клиента или госномеру
        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('id', 'like', "%{$this->search}%")
                    ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('car', fn ($q) => $q->where('license_plate', 'like', "%{$this->search}%"));
            });
        }

        return [
            'orders' => $query->latest('ordered_at')->paginate(10),
            // Массив статусов для красивого вывода и фильтров
            'statuses' => [
                'new' => ['label' => 'Новые', 'color' => 'bg-blue-100 text-blue-800'],
                'in_progress' => ['label' => 'В работе', 'color' => 'bg-yellow-100 text-yellow-800'],
                'ready' => ['label' => 'Готовы', 'color' => 'bg-purple-100 text-purple-800'],
                'completed' => ['label' => 'Завершены', 'color' => 'bg-green-100 text-green-800'],
                'cancelled' => ['label' => 'Отменены', 'color' => 'bg-red-100 text-red-800'],
            ],
        ];
    }

    // БЫСТРЫЕ ДЕЙСТВИЯ (Quick Actions)
    public function changeStatus(int $id, string $newStatus)
    {
        // Запрещаем клиентам менять статусы
        if (Auth::user()?->isClient()) {
            abort(403);
        }

        $order = Order::findOrFail($id);
        $data = ['status' => $newStatus];

        // Если заказ завершается, автоматически ставим дату завершения
        if ($newStatus === 'completed') {
            $data['completed_at'] = now();
        }

        $order->update($data);
    }

    // Удаление
    public function deleteOrder(int $id)
    {
        if (Auth::user()?->isClient()) {
            abort(403);
        }
        Order::findOrFail($id)->delete();
    }
};
?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <!-- Шапка: Заголовок и кнопка создания -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Управление заказами</h2>
            
            @if (!Auth::user()->isClient())
                <!-- Перенаправляет на страницу создания (которую мы сделаем следующей) -->
                <a href="{{ route('orders.detail', ['id' => 'new']) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow">
                    + Создать заказ
                </a>
            @endif
        </div>

        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            
            <!-- Панель управления: Фильтры и Поиск -->
            <div class="p-4 border-b bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
                
                <!-- Вкладки фильтров (Кнопки) -->
                <div class="flex space-x-2 overflow-x-auto pb-2 md:pb-0 w-full md:w-auto">
                    <button wire:click="setFilter('')" 
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors 
                            {{ $filterStatus === '' ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 hover:bg-gray-200 border' }}">
                        Все
                    </button>
                    @foreach($statuses as $key => $status)
                        <button wire:click="setFilter('{{ $key }}')" 
                                class="px-4 py-2 rounded-full text-sm font-medium transition-colors border
                                {{ $filterStatus === $key ? $status['color'] . ' border-transparent ring-2 ring-offset-1 ring-gray-400' : 'bg-white text-gray-600 hover:bg-gray-100' }}">
                            {{ $status['label'] }}
                        </button>
                    @endforeach
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
                        @forelse($orders as $order)
                            <tr class="hover:bg-gray-50 transition-colors">
                                
                                <!-- ID и Дата -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900">#{{ $order->id }}</div>
                                    <div class="text-sm text-gray-500">{{ $order->ordered_at?->format('d.m.Y H:i') }}</div>
                                </td>

                                <!-- Клиент и Авто -->
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $order->client?->name ?? 'Неизвестен' }}</div>
                                    <div class="text-sm text-gray-500">{{ $order->car?->brand }} {{ $order->car?->model }} ({{ $order->car?->license_plate ?? 'Б/Н' }})</div>
                                </td>

                                <!-- Статус (Цветной бейдж) -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClass = $statuses[$order->status]['color'] ?? 'bg-gray-100 text-gray-800';
                                        $statusLabel = $statuses[$order->status]['label'] ?? $order->status;
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <!-- Сумма -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ number_format($order->total_amount, 2, '.', ' ') }} ₽
                                </td>

                                <!-- Действия (Умные кнопки) -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-3">
                                        
                                        <!-- Ссылка внутрь заказа (Детали) -->
                                        <a href="{{ route('orders.detail', $order->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded transition-colors">
                                            Открыть
                                        </a>

                                        <!-- Быстрые действия только для персонала -->
@if (!Auth::user()?->isClient())
                                            <!-- Если новый -> В работу -->
                                            @if($order->status === 'new')
                                                <button wire:click="changeStatus({{ $order->id }}, 'in_progress')" class="text-yellow-600 hover:text-yellow-900 font-bold" title="Взять в работу">
                                                    ▶ В работу
                                                </button>
                                            @endif

                                            <!-- Если в работе -> Готов -->
                                            @if($order->status === 'in_progress')
                                                <button wire:click="changeStatus({{ $order->id }}, 'ready')" class="text-purple-600 hover:text-purple-900 font-bold" title="Отметить готовым">
                                                    ★ Готов
                                                </button>
                                            @endif

                                            <!-- Если готов -> Завершить (выдать клиенту) -->
                                            @if($order->status === 'ready')
                                                <button wire:click="changeStatus({{ $order->id }}, 'completed')" wire:confirm="Завершить заказ и передать авто клиенту?" class="text-green-600 hover:text-green-900 font-bold" title="Завершить заказ">
                                                    ✔ Завершить
                                                </button>
                                            @endif
                                            
                                            <!-- Удалить -->
                                            <button wire:click="deleteOrder({{ $order->id }})" wire:confirm="Точно удалить заказ?" class="text-red-400 hover:text-red-600 ml-2" title="Удалить">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Заказов пока нет или они не найдены.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="p-4 border-t bg-gray-50">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>