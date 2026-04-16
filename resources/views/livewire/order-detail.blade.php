<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Part;
use App\Models\Service;
use App\Models\User;
use App\Models\Car;
use App\Models\Workplace;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    // Текущий заказ (null, если создаем новый)
    public ?Order $order = null;
    public bool $isNew = false;

    // Поля формы заказа (Шапка)
    public ?int $user_id = null;
    public ?int $car_id = null;
    public ?int $workplace_id = null;
    public ?int $employee_id = null;
    public ?string $ordered_at = null;
    public ?string $deadline = null;
    public ?string $completed_at = null;
    public string $status = 'new';
    public float $total_amount = 0;

    // Поля для добавления новой позиции (запчасть или услуга)
    public string $newItemType = 'part'; // 'part' или 'service'
    public ?int $newItemId = null;
    public int $newItemQty = 1;

    // Данные для выпадающих списков
    public $clients = [];
    public $cars = [];
    public $employees = [];
    public $workplaces = [];
    public $availableParts = [];
    public $availableServices = [];

    public function mount(string $id)
    {
        // Загружаем справочники
        $this->clients = User::where('role', User::ROLE_CLIENT)->get();
        $this->employees = User::whereIn('role', [User::ROLE_EMPLOYEE, User::ROLE_ADMIN])->get();
        $this->cars = []; // изначально машин нет, пока не выбран клиент
        $this->workplaces = Workplace::all();
        $this->availableParts = Part::all();
        $this->availableServices = Service::all();

        if ($id === 'new') {
            $this->isNew = true;
            $this->ordered_at = now()->format('Y-m-d\TH:i'); // Текущая дата по умолчанию
            $this->employee_id = Auth::id(); // По умолчанию ответственный - тот, кто создает
        } else {
            $this->loadOrder(intval($id));
        }
    }

    public function updatedUserId($value)
    {
        // Сбрасываем выбранную машину
        $this->car_id = null; 
        
        // Загружаем машины только выбранного клиента
        if ($value) {
            $this->cars = Car::where('user_id', $value)->get();
        } else {
            $this->cars = [];
        }
    }


    // Загрузка существующего заказа
    private function loadOrder(int $id)
    {
        $this->order = Order::with(['items.part', 'items.service'])->findOrFail($id);
        $this->isNew = false;

        $this->user_id = $this->order->user_id;
        $this->car_id = $this->order->car_id;
        $this->workplace_id = $this->order->workplace_id;
        $this->employee_id = $this->order->employee_id;
        $this->ordered_at = $this->order->ordered_at?->format('Y-m-d\TH:i');
        $this->deadline = $this->order->deadline?->format('Y-m-d\TH:i');
        $this->completed_at = $this->order->completed_at?->format('Y-m-d\TH:i');
        $this->status = $this->order->status;
        $this->total_amount = (float) $this->order->total_amount;

        // загрузка машин клиента
        $this->cars = Car::where('user_id', $this->user_id)->get();
    }

    // Сохранение "Шапки" заказа
    public function saveOrder()
    {
        if (Auth::user()?->isClient()) abort(403);

        $validated = $this->validate([
            'user_id'      => 'required|exists:users,id',
            'car_id'       => 'required|exists:cars,id',
            'workplace_id' => 'nullable|exists:workplaces,id',
            'employee_id'  => 'required|exists:users,id',
            'ordered_at'   => 'required|date',
            'deadline'     => 'nullable|date',
            'completed_at' => 'nullable|date',
            'status'       => 'required|string',
        ]);

        if ($this->isNew) {
            $validated['total_amount'] = 0; // Новый заказ всегда с нулем
            $this->order = Order::create($validated);
            // После создания перенаправляем на этот же заказ, чтобы можно было добавить запчасти
            return redirect()->route('orders.detail', $this->order->id)->with('message', 'Заказ создан. Теперь добавьте запчасти и услуги.');
        } else {
            $this->order->update($validated);
            session()->flash('message', 'Данные заказа обновлены.');
        }
    }

    // Добавление позиции в чек
    public function addItem()
    {   
        if (Auth::user()?->isClient() || $this->isNew) abort(403);

        $this->validate([
            'newItemType' => 'required|in:part,service',
            'newItemId'   => 'required|integer',
            'newItemQty'  => 'required|integer|min:1',
        ]);

        $price = 0;

        // Фиксируем цену на момент добавления (Snapshot)
        if ($this->newItemType === 'part') {
            $part = Part::findOrFail($this->newItemId);
            $price = $part->retail_price;
        } else {
            $service = Service::findOrFail($this->newItemId);
            $price = $service->price;
        }

        // Создаем позицию в базе
        OrderItem::create([
            'order_id'   => $this->order->id,
            'type'       => $this->newItemType,
            'part_id'    => $this->newItemType === 'part' ? $this->newItemId : null,
            'service_id' => $this->newItemType === 'service' ? $this->newItemId : null,
            'quantity'   => $this->newItemQty,
            'price'      => $price, // Записали текущую цену!
        ]);

        $this->resetItemForm();
        $this->recalculateTotal();
        session()->flash('item_message', 'Позиция добавлена в заказ.');
    }

    // Удаление позиции из чека
    public function removeItem(int $itemId)
    {
        if (Auth::user()?->isClient()) abort(403);

        OrderItem::where('id', $itemId)->where('order_id', $this->order->id)->delete();
        $this->recalculateTotal();
        session()->flash('item_message', 'Позиция удалена.');
    }

    // АВТОМАТИЧЕСКИЙ пересчет суммы заказа
    private function recalculateTotal()
    {
        // Перезагружаем заказ с новыми позициями
        $this->order->load('items');
        
        $total = 0;
        foreach ($this->order->items as $item) {
            $total += ($item->price * $item->quantity);
        }

        // Обновляем общую сумму в заказе
        $this->order->update(['total_amount' => $total]);
        $this->total_amount = $total; // Обновляем свойство для вывода на экран
    }

    private function resetItemForm()
    {
        $this->newItemId = null;
        $this->newItemQty = 1;
    }
};
?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('orders.index') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                &larr; Назад к списку
            </a>
            @if(!$isNew)
                <h2 class="text-2xl font-bold text-gray-800">Заказ #{{ $order->id }}</h2>
            @else
                <h2 class="text-2xl font-bold text-gray-800">Создание нового заказа</h2>
            @endif
        </div>

        @if (session()->has('message'))
            <div class="mb-4 bg-green-100 text-green-800 p-4 rounded shadow-sm font-medium">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- ЛЕВАЯ КОЛОНКА: Основные данные заказа (Шапка) -->
            <div class="lg:col-span-1 bg-white p-6 shadow sm:rounded-lg h-fit">
                <h3 class="text-lg font-bold border-b pb-2 mb-4">Информация о заказе</h3>
                
                <fieldset @disabled(Auth::user()?->isClient()) class="space-y-4">
                    <div>
                        <x-input-label value="Клиент" />
                        <select wire:model.live="user_id" class="w-full mt-1 rounded border-gray-300 text-sm">
                            <option value="">-- Выберите клиента --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} {{ $client->surname }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label value="Автомобиль" />
                        <select wire:model="car_id" class="w-full mt-1 rounded border-gray-300 text-sm">
                            <option value="">-- Выберите авто --</option>
                            @foreach($cars as $car)
                                <option value="{{ $car->id }}">{{ $car->brand }} {{ $car->model }} ({{ $car->license_plate }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('car_id')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label value="Статус" />
                        <select wire:model="status" class="w-full mt-1 rounded border-gray-300 text-sm font-bold text-indigo-700">
                            <option value="new">Новый</option>
                            <option value="accepted">Принят</option>
                            <option value="in_progress">В работе</option>
                            <option value="ready">Готов</option>
                            <option value="completed">Завершен</option>
                            <option value="cancelled">Отменен</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label value="Ответственный" />
                        <select wire:model="employee_id" class="w-full mt-1 rounded border-gray-300 text-sm">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label value="Дата заезда" />
                        <x-text-input wire:model="ordered_at" type="datetime-local" class="w-full mt-1 text-sm" />
                    </div>

                    @if(!Auth::user()?->isClient())
                        <div class="pt-4">
                            <x-primary-button wire:click="saveOrder" class="w-full justify-center">
                                {{ $isNew ? 'Создать и перейти к запчастям' : 'Сохранить изменения' }}
                            </x-primary-button>
                        </div>
                    @endif
                </fieldset>
            </div>

            <!-- ПРАВАЯ КОЛОНКА: Состав заказа (Позиции) -->
            <div class="lg:col-span-2 space-y-6">
                
                @if($isNew)
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-6 rounded-lg text-center shadow-sm">
                        <svg class="mx-auto h-12 w-12 text-yellow-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="text-lg font-bold">Заказ еще не создан</h3>
                        <p class="mt-1 text-sm">Заполните данные слева и нажмите «Создать», чтобы начать добавлять запчасти и услуги.</p>
                    </div>
                @else
                    <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                        <div class="p-6 border-b bg-gray-50 flex justify-between items-center">
                            <h3 class="text-xl font-bold text-gray-800">Состав заказа (Чек)</h3>
                            <div class="text-2xl font-black text-green-600">
                                Итого: {{ number_format($total_amount, 2, '.', ' ') }} ₽
                            </div>
                        </div>

                        <!-- Вывод сообщений позиций -->
                        @if (session()->has('item_message'))
                            <div class="bg-blue-100 text-blue-800 px-6 py-2 text-sm">
                                {{ session('item_message') }}
                            </div>
                        @endif

                        <!-- Таблица позиций -->
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Наименование</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Тип</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Кол-во</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Цена</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Сумма</th>
                                    @if(!Auth::user()?->isClient())
                                        <th class="px-6 py-3"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($order->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                            {{ $item->type === 'part' ? $item->part?->name : $item->service?->name }}
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-500">
                                            @if($item->type === 'part')
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">Запчасть</span>
                                            @else
                                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">Услуга</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm">{{ $item->quantity }} шт.</td>
                                        <td class="px-6 py-4 text-right text-sm text-gray-500">{{ number_format($item->price, 2) }} ₽</td>
                                        <td class="px-6 py-4 text-right text-sm font-bold">{{ number_format($item->price * $item->quantity, 2) }} ₽</td>
                                        
                                        @if(!Auth::user()?->isClient())
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <button wire:click="removeItem({{ $item->id }})" class="text-red-500 hover:text-red-700" title="Удалить">
                                                ✕
                                            </button>
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">
                                            В заказе пока нет ни одной позиции
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <!-- Форма добавления новой позиции (только для персонала) -->
                        @if(!Auth::user()?->isClient())
                            <div class="p-6 bg-gray-50 border-t">
                                <h4 class="text-sm font-bold text-gray-700 mb-3">Добавить позицию в чек</h4>
                                <div class="flex flex-wrap gap-4 items-end">
                                    
                                    <!-- Выбор: Услуга или Запчасть -->
                                    <div>
                                        <x-input-label value="Что добавляем?" />
                                        <select wire:model.live="newItemType" class="mt-1 rounded border-gray-300 text-sm">
                                            <option value="part">Запчасть со склада</option>
                                            <option value="service">Выполненная услуга</option>
                                        </select>
                                    </div>

                                    <!-- Выбор конкретной запчасти/услуги -->
                                    <div class="flex-grow">
                                        <x-input-label value="Наименование" />
                                        <select wire:model="newItemId" class="mt-1 w-full rounded border-gray-300 text-sm">
                                            <option value="">-- Выберите из списка --</option>
                                            @if($newItemType === 'part')
                                                @foreach($availableParts as $part)
                                                    <option value="{{ $part->id }}">{{ $part->name }} ({{ $part->retail_price }} ₽)</option>
                                                @endforeach
                                            @else
                                                @foreach($availableServices as $service)
                                                    <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->price }} ₽)</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <x-input-error :messages="$errors->get('newItemId')" class="mt-1" />
                                    </div>

                                    <!-- Количество -->
                                    <div class="w-24">
                                        <x-input-label value="Кол-во" />
                                        <x-text-input wire:model="newItemQty" type="number" min="1" class="w-full mt-1 text-sm text-center" />
                                    </div>

                                    <!-- Кнопка добавить -->
                                    <div>
                                        <button wire:click="addItem" class="bg-gray-800 hover:bg-black text-white font-bold py-2 px-4 rounded shadow">
                                            + В чек
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                @endif
            </div>
        </div>
    </div>
</div>