<?php

use App\Models\Part;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $name = '';
    public string $retail_price = '';
    public ?int $part_id = null;
    public bool $isEditMode = false;

    // Правила валидации
    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'retail_price' => 'required|numeric|min:0',
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
            'parts' => Part::where('name', 'like', "%{$this->search}%")
                ->latest()
                ->paginate(10),
        ];
    }

    // Сохранение / Обновление
    public function store()
    {
        $this->validate();

        Part::updateOrCreate(['id' => $this->part_id], [
            'name' => $this->name,
            'retail_price' => $this->retail_price,
        ]);

        $this->reset(['name', 'retail_price', 'part_id', 'isEditMode']);
        session()->flash('message', 'Запчасть успешно сохранена.');
    }

    // Редактирование
    public function edit(int $id)
    {
        $part = Part::findOrFail($id);
        $this->part_id = $id;
        $this->name = $part->name;
        $this->retail_price = $part->retail_price;
        $this->isEditMode = true;
    }

    // Удаление
    public function delete(int $id)
    {
        Part::findOrFail($id)->delete();
        session()->flash('message', 'Запчасть удалена.');
    }

    public function cancel()
    {
        $this->reset(['name', 'retail_price', 'part_id', 'isEditMode']);
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!--оберточка как в Breeze )-->
        <div class="bg-white p-6 shadow sm:rounded-lg">
            
            @if (session()->has('message'))
                <div class="mb-4 text-green-600 font-medium">
                    {{ session('message') }}
                </div>
            @endif

            <!-- Формаа -->
                    @if (!auth()->user()->isClient())
            <div class="mb-8 p-4 bg-gray-50 rounded border">
                <h3 class="text-lg font-semibold mb-4">{{ $isEditMode ? 'Изменить запчасть' : 'Новая запчасть' }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Название" />
                        <x-text-input wire:model="name" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Цена" />
                        <x-text-input wire:model="retail_price" type="number" step="0.01" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('retail_price')" class="mt-1" />
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
                <x-text-input wire:model.live="search" placeholder="Поиск по названию..." class="w-full" />
            </div>

            <!-- Таблица -->
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">Название</th>
                            <th class="p-2 border">Цена</th>
                            <th class="p-2 border">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parts as $part)
                            <tr>
                                <td class="p-2 border">{{ $part->name }}</td>
                                <td class="p-2 border">{{ $part->retail_price }} руб.</td>
                                <td class="p-2 border">
                                    @if (!auth()->user()->isClient())
                                        <button wire:click="edit({{ $part->id }})" class="text-indigo-600">Ред.</button>
                                        <button wire:click="delete({{ $part->id }})" wire:confirm="..." class="text-red-600 ml-2">Удалить</button>
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
                {{ $parts->links() }}
            </div>
        </div>
    </div>
</div>