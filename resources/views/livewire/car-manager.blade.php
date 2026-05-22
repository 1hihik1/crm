<?php

use App\Models\Car;
use App\Models\User;
use App\Livewire\Concerns\ScrollsToCrudForm;
use App\Livewire\Concerns\WithDeleteConfirmation;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use ScrollsToCrudForm;
    use WithDeleteConfirmation;
    use WithPagination;

    public string $search = '';
    public ?int $user_id = null;
    public string $brand = '';
    public string $model = '';
    public string $year = '';
    public string $vin = '';
    public string $license_plate = '';
    public ?int $car_id = null;
    public bool $isEditMode = false;

    // правила валидации
    // Правила валидации
    protected function rules()
    {
        return [
            'user_id'       => 'required|exists:users,id',
            'brand'         => 'required|string|max:255',
            'model'         => 'required|string|max:255',
            'year'          => 'required|integer|min:1900|max:' . date('Y'),
            
            // VIN: ровно 17 символов + исключение текущей записи при проверке уникальности
            'vin'           => 'required|string|size:17|unique:cars,vin,' . $this->car_id,
            
            //госномер- используется массив для регулярок, чтобы избежать проблем с экранированием
            'license_plate' => [
                'nullable', 
                'string', 
                'regex:/^[ABCEHKMOPTXY]\d{3}[ABCEHKMOPTXY]{2}\s\d{2,3}$/'
            ],
        ];
    }

    //кастомные сообщения об ошибках у полей формы
    protected function messages()
    {
        return [
            'vin.size' => 'VIN-номер должен состоять ровно из 17 символов.',
            'vin.unique' => 'Автомобиль с таким VIN уже зарегистрирован в системе.',
            'year.max' => 'Год выпуска не может быть в будущем.',
            'license_plate.regex' => 'Госномер введен неверно. Формат: А123АА 77 (или 777).',
        ];
    }

    // сброс пагинации при поиске
    public function updatingSearch()
    {
        $this->resetPage();
    }

    //данные для шаблона (список авто и список пользователей)
    public function with(): array
    {
        $query = Car::query();

        // БЕЗОПАСНОСТЬ: Если это клиент, жестко фильтруем только его машины
        if (Auth::user()?->isClient()) {
            $query->where('user_id', Auth::id());
        }

        return [
            'cars' => $query->where(function ($q) {
                    $q->where('brand', 'like', "%{$this->search}%")
                      ->orWhere('model', 'like', "%{$this->search}%")
                      ->orWhere('vin', 'like', "%{$this->search}%")
                      ->orWhere('license_plate', 'like', "%{$this->search}%");
                })
                ->latest()
                ->paginate(10),

            'users' => User::all()->filter(fn($user) => $user->isClient())->values(),
        ];
    }

    // Сохранение / Обновление
    public function store()
    {
        $this->validate();

        Car::updateOrCreate(['id' => $this->car_id], [
            'user_id'       => $this->user_id,
            'brand'         => $this->brand,
            'model'         => $this->model,
            'year'          => (int) $this->year,
            'vin'           => $this->vin,
            'license_plate' => $this->license_plate,
        ]);

        $this->reset(['user_id', 'brand', 'model', 'year', 'vin', 'license_plate', 'car_id', 'isEditMode']);
        session()->flash('message', 'Автомобиль успешно сохранён.');
    }

    // Редактирование
    public function edit(int $id)
    {
        $car = Car::findOrFail($id);
        $this->car_id        = $id;
        $this->user_id       = $car->user_id;
        $this->brand         = $car->brand;
        $this->model         = $car->model;
        $this->year          = (string) $car->year;
        $this->vin           = $car->vin;
        $this->license_plate = (string) ($car->license_plate ?? '');
        $this->isEditMode    = true;
        $this->resetValidation();
        $this->scrollToCrudForm();
    }

    //уддаление
    public function delete(int $id)
    {
        Car::findOrFail($id)->delete();
        session()->flash('message', 'Автомобиль удалён.');
    }

    // отмена редактирования
    public function cancel()
    {
        $this->reset(['user_id', 'brand', 'model', 'year', 'vin', 'license_plate', 'car_id', 'isEditMode']);
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

            @include('livewire.partials.delete-modal')

            <!-- Форма -->
            @if (!Auth::user()->isClient())
            <div id="crud-form" wire:key="car-form-{{ $isEditMode ? 'edit-'.$car_id : 'new' }}" class="mb-8 p-4 bg-gray-50 rounded border ring-2 {{ $isEditMode ? 'ring-indigo-200' : 'ring-transparent' }}">
                <h3 class="text-lg font-semibold mb-4">{{ $isEditMode ? 'Изменить автомобиль' : 'Новый автомобиль' }}</h3>
                @if($isEditMode)
                    <p class="text-sm text-indigo-600 mb-3">Редактирование #{{ $car_id }}</p>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Владелец" />
                        <select wire:model.live="user_id" class="w-full mt-1 rounded border-gray-300">
                            <option value="">-- Выберите клиента --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Марка" />
                        <x-text-input wire:model.live="brand" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('brand')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Модель" />
                        <x-text-input wire:model.live="model" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('model')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Год выпуска" />
                        <x-text-input wire:model.live="year" type="number" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('year')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="VIN" />
                        <x-text-input wire:model.live="vin" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('vin')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Госномер" />
                        <x-text-input wire:model.live="license_plate" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('license_plate')" class="mt-1" />
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
                <x-text-input wire:model.live="search" placeholder="Поиск по марке, модели, VIN или госномеру..." class="w-full" />
            </div>

            <!-- Таблица автомобилей -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">Владелец</th>
                            <th class="p-2 border">Марка</th>
                            <th class="p-2 border">Модель</th>
                            <th class="p-2 border">Год</th>
                            <th class="p-2 border">VIN</th>
                            <th class="p-2 border">Госномер</th>
                            <th class="p-2 border">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cars as $car)
                        <tr>
                            <td class="p-2 border">{{ $car->owner->name ?? '—' }}</td>
                            <td class="p-2 border">{{ $car->brand }}</td>
                            <td class="p-2 border">{{ $car->model }}</td>
                            <td class="p-2 border">{{ $car->year }}</td>
                            <td class="p-2 border">{{ $car->vin }}</td>
                            <td class="p-2 border">{{ $car->license_plate ?? '—' }}</td>
                            <td class="p-2 border">
                                @if (!Auth::user()->isClient())
                                    <button type="button" wire:click="edit({{ $car->id }})" class="text-indigo-600">Ред.</button>
                                    <button type="button" wire:click="askDelete({{ $car->id }}, @js($car->brand.' '.$car->model))" class="text-red-600 ml-2">Удалить</button>
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
                {{ $cars->links() }}
            </div>
        </div>
    </div>
</div>