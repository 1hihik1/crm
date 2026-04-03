<?php

use App\Models\Order;
use App\Models\Car;
use App\Models\User;
use App\Models\Workplace;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    // Поля формы
    public ?int $user_id = null;
    public ?int $car_id = null;
    public ?int $workplace_id = null;
    public ?int $employee_id = null;
    public string $ordered_at = '';
    public string $deadline = '';
    public string $completed_at = '';
    public string $status = 'new';
    public string $total_amount = '';

    // Поиск и режим редактирования
    public string $search = '';
    public ?int $order_id = null;
    public bool $isEditMode = false;

    // Правила валидации
    protected function rules()
    {
        return [
            'user_id'      => 'required|exists:users,id',
            'car_id'       => 'required|exists:cars,id',
            'workplace_id' => 'nullable|exists:workplaces,id',
            'employee_id'  => 'required|exists:users,id',
            'ordered_at'   => 'required|date',
            'deadline'     => 'nullable|date|after_or_equal:ordered_at',
            'completed_at' => 'nullable|date|after_or_equal:ordered_at',
            'status'       => 'required|string|in:new,accepted,in_progress,ready,completed,cancelled',
            'total_amount' => 'required|numeric|min:0',
        ];
    }

    // Сброс пагинации при поиске
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Данные для шаблона
    public function with(): array
{
    return [
        'orders' => Order::with(['client', 'car', 'employee', 'workplace'])
            ->where(function ($query) {
                $search = $this->search;
                $query->where('status', 'like', "%{$search}%")
                      ->orWhereHas('client', fn($q) => $q->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('car', fn($q) => $q->where('brand', 'like', "%{$search}%")->orWhere('model', 'like', "%{$search}%"))//условие на существование связанной модели client. Если у заказа есть клиент, у которого в поле name (имя) встречается $search, то такой заказ тоже подходит.
                      ->orWhereHas('employee', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->latest('ordered_at')
            ->paginate(10),

        'clients' => User::all()->filter(fn($user) => $user->isClient())->values(),

       
        'employees' => User::all()->filter(fn($user) => $user->isEmployee())->values(),

        'cars' => Car::with('owner')->orderBy('brand')->get(),
        'workplaces' => Workplace::orderBy('name')->get(),
        'statuses' => [
            'new'         => 'Новый',
            'accepted'    => 'Принят',
            'in_progress' => 'В работе',
            'ready'       => 'Готов',
            'completed'   => 'Завершён',
            'cancelled'   => 'Отменён',
        ],
    ];
}

    // Сохранение / Обновление
    public function store()
    {
        $this->validate();

        Order::updateOrCreate(['id' => $this->order_id], [
            'user_id'      => $this->user_id,
            'car_id'       => $this->car_id,
            'workplace_id' => $this->workplace_id ?: null,
            'employee_id'  => $this->employee_id,
            'ordered_at'   => $this->ordered_at,
            'deadline'     => $this->deadline ?: null,
            'completed_at' => $this->completed_at ?: null,
            'status'       => $this->status,
            'total_amount' => $this->total_amount,
        ]);

        $this->reset(['user_id', 'car_id', 'workplace_id', 'employee_id', 'ordered_at', 'deadline', 'completed_at', 'status', 'total_amount', 'order_id', 'isEditMode']);
        session()->flash('message', 'Заказ успешно сохранён.');
    }

    // Редактирование
    public function edit(int $id)
    {
        $order = Order::findOrFail($id);
        $this->order_id     = $id;
        $this->user_id      = $order->user_id;
        $this->car_id       = $order->car_id;
        $this->workplace_id = $order->workplace_id;
        $this->employee_id  = $order->employee_id;
        $this->ordered_at   = $order->ordered_at->format('Y-m-d\TH:i');
        $this->deadline     = $order->deadline?->format('Y-m-d\TH:i');
        $this->completed_at = $order->completed_at?->format('Y-m-d\TH:i');
        $this->status       = $order->status;
        $this->total_amount = (string) $order->total_amount;
        $this->isEditMode   = true;
    }

    // Удаление
    public function delete(int $id)
    {
        Order::findOrFail($id)->delete();
        session()->flash('message', 'Заказ удалён.');
    }

    // Отмена редактирования
    public function cancel()
    {
        $this->reset(['user_id', 'car_id', 'workplace_id', 'employee_id', 'ordered_at', 'deadline', 'completed_at', 'status', 'total_amount', 'order_id', 'isEditMode']);
    }
};
?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg">

            @if (session()->has('message'))
                <div class="mb-4 text-green-600 font-medium">
                    {{ session('message') }}
                </div>
            @endif

            <!-- Форма создания/редактирования -->
            @if (!auth()->user()->isClient())
            <div class="mb-8 p-4 bg-gray-50 rounded border">
                <h3 class="text-lg font-semibold mb-4">{{ $isEditMode ? 'Изменить заказ' : 'Новый заказ' }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Клиент" />
                        <select wire:model="user_id" class="w-full mt-1 rounded border-gray-300">
                            <option value="">-- Выберите клиента --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Автомобиль" />
                        <select wire:model="car_id" class="w-full mt-1 rounded border-gray-300">
                            <option value="">-- Выберите автомобиль --</option>
                            @foreach($cars as $car)
                                <option value="{{ $car->id }}">{{ $car->brand }} {{ $car->model }} ({{ $car->owner->name ?? 'нет владельца' }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('car_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Рабочее место" />
                        <select wire:model="workplace_id" class="w-full mt-1 rounded border-gray-300">
                            <option value="">-- Не выбрано --</option>
                            @foreach($workplaces as $workplace)
                                <option value="{{ $workplace->id }}">{{ $workplace->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('workplace_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Ответственный сотрудник" />
                        <select wire:model="employee_id" class="w-full mt-1 rounded border-gray-300">
                            <option value="">-- Выберите сотрудника --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('employee_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Дата заказа" />
                        <x-text-input wire:model="ordered_at" type="datetime-local" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('ordered_at')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Дедлайн" />
                        <x-text-input wire:model="deadline" type="datetime-local" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('deadline')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Дата завершения" />
                        <x-text-input wire:model="completed_at" type="datetime-local" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('completed_at')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Статус" />
                        <select wire:model="status" class="w-full mt-1 rounded border-gray-300">
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Итоговая сумма" />
                        <x-text-input wire:model="total_amount" type="number" step="0.01" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('total_amount')" class="mt-1" />
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <x-primary-button wire:click="store">
                        {{ $isEditMode ? 'Обновить' : 'Создать' }}
                    </x-primary-button>
                    @if($isEditMode)
                        <x-secondary-button wire:click="cancel">Отмена</x-secondary-button>
                    @endif
                </div>
            </div>
            @endif

            <!-- Поиск -->
            <div class="mb-4">
                <x-text-input wire:model.live="search" placeholder="Поиск по статусу, клиенту, автомобилю или сотруднику..." class="w-full" />
            </div>

            <!-- Таблица заказов -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">ID</th>
                            <th class="p-2 border">Клиент</th>
                            <th class="p-2 border">Автомобиль</th>
                            <th class="p-2 border">Сотрудник</th>
                            <th class="p-2 border">Дата заказа</th>
                            <th class="p-2 border">Дедлайн</th>
                            <th class="p-2 border">Статус</th>
                            <th class="p-2 border">Сумма</th>
                            <th class="p-2 border">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td class="p-2 border">{{ $order->id }}</td>
                            <td class="p-2 border">{{ $order->client?->name ?? '—' }}</td>
                            <td class="p-2 border">{{ $order->car?->brand }} {{ $order->car?->model }}</td>
                            <td class="p-2 border">{{ $order->employee?->name ?? '—' }}</td>
                            <td class="p-2 border">{{ $order->ordered_at?->format('d.m.Y H:i') }}</td>
                            <td class="p-2 border">{{ $order->deadline?->format('d.m.Y H:i') ?? '—' }}</td>
                            <td class="p-2 border">
                                <span class="px-2 py-1 rounded text-xs
                                    @switch($order->status)
                                        @case('new') bg-blue-100 text-blue-800 @break
                                        @case('accepted') bg-cyan-100 text-cyan-800 @break
                                        @case('in_progress') bg-yellow-100 text-yellow-800 @break
                                        @case('ready') bg-purple-100 text-purple-800 @break
                                        @case('completed') bg-green-100 text-green-800 @break
                                        @case('cancelled') bg-red-100 text-red-800 @break
                                        @default bg-gray-100
                                    @endswitch
                                ">
                                    {{ $statuses[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td class="p-2 border">{{ number_format($order->total_amount, 2) }} руб.</td>
                            <td class="p-2 border">
                                @if (!auth()->user()->isClient())
                                    <button wire:click="edit({{ $order->id }})" class="text-indigo-600">Ред.</button>
                                    <button wire:click="delete({{ $order->id }})" wire:confirm="Удалить заказ? Это действие необратимо." class="text-red-600 ml-2">Удалить</button>
                                @else
                                    <span class="text-gray-400 text-sm">Только просмотр</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>