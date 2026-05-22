<?php

use App\Models\Service;
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
    public string $name = '';
    public string $description = '';
    public string $price = '';
    public ?int $service_id = null;
    public bool $isEditMode = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'services' => Service::query()
                ->when($this->search !== '', function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('name', 'like', "%{$this->search}%")
                            ->orWhere('description', 'like', "%{$this->search}%");
                    });
                })
                ->latest()
                ->paginate(10),
        ];
    }

    public function store(): void
    {
        $this->validate();

        Service::updateOrCreate(
            ['id' => $this->service_id],
            [
                'name' => $this->name,
                'description' => $this->description ?: null,
                'price' => $this->price,
            ]
        );

        $this->reset(['name', 'description', 'price', 'service_id', 'isEditMode']);
        session()->flash('message', 'Услуга сохранена.');
    }

    public function edit(int $id): void
    {
        $s = Service::findOrFail($id);
        $this->service_id = $id;
        $this->name = $s->name;
        $this->description = (string) ($s->description ?? '');
        $this->price = (string) $s->price;
        $this->isEditMode = true;
        $this->resetValidation();
        $this->scrollToCrudForm();
    }

    public function delete(int $id): void
    {
        Service::findOrFail($id)->delete();
        session()->flash('message', 'Услуга удалена.');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'description', 'price', 'service_id', 'isEditMode']);
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg">
            @if (session()->has('message'))
                <div class="mb-4 text-green-600 font-medium">
                    {{ session('message') }}
                </div>
            @endif

            @include('livewire.partials.delete-modal')

            @if (!Auth::user()->isClient())
                <div id="crud-form" wire:key="service-form-{{ $isEditMode ? 'edit-'.$service_id : 'new' }}" class="mb-8 p-4 bg-gray-50 rounded border ring-2 {{ $isEditMode ? 'ring-indigo-200' : 'ring-transparent' }}">
                    <h3 class="text-lg font-semibold mb-4">{{ $isEditMode ? 'Изменить услугу' : 'Новая услуга' }}</h3>
                    @if($isEditMode)
                        <p class="text-sm text-indigo-600 mb-3">Редактирование #{{ $service_id }}</p>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <x-input-label value="Название" />
                            <x-text-input wire:model.live="name" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label value="Описание" />
                            <textarea wire:model.live="description" rows="3" class="w-full mt-1 rounded-md border-gray-300 shadow-sm"></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label value="Цена, ₽" />
                            <x-text-input wire:model.live="price" type="number" step="0.01" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('price')" class="mt-1" />
                        </div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <x-primary-button wire:click="store">{{ $isEditMode ? 'Обновить' : 'Создать' }}</x-primary-button>
                        @if($isEditMode)
                            <x-secondary-button wire:click="cancel">Отмена</x-secondary-button>
                        @endif
                    </div>
                </div>
            @endif

            <div class="mb-4">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Поиск по названию или описанию..." class="w-full" />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">Название</th>
                            <th class="p-2 border">Описание</th>
                            <th class="p-2 border">Цена</th>
                            <th class="p-2 border">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $service)
                            <tr>
                                <td class="p-2 border font-medium">{{ $service->name }}</td>
                                <td class="p-2 border text-sm text-gray-600 max-w-md truncate">{{ $service->description ?? '—' }}</td>
                                <td class="p-2 border">{{ number_format((float) $service->price, 2, '.', ' ') }} ₽</td>
                                <td class="p-2 border">
                                    @if (!Auth::user()->isClient())
                                        <button type="button" wire:click="edit({{ $service->id }})" class="text-indigo-600">Ред.</button>
                                        <button type="button" wire:click="askDelete({{ $service->id }}, @js($service->name))" class="text-red-600 ml-2">Удалить</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $services->links() }}
            </div>
        </div>
    </div>
</div>
