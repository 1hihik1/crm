<?php

use App\Models\Part;
use App\Livewire\Concerns\ScrollsToCrudForm;
use App\Livewire\Concerns\WithDeleteConfirmation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new class extends Component
{
    public function canManage(): bool
    {
        $user = Auth::user();

        return $user && ($user->isAdmin() || $user->isManager());
    }
    use ScrollsToCrudForm;
    use WithDeleteConfirmation;
    use WithFileUploads;
    use WithPagination;

    public string $search = '';
    public string $name = '';
    public string $retail_price = '';
    public ?int $part_id = null;
    public bool $isEditMode = false;

    public $photo = null;

    /** @var array<int, array{key: string, val: string}> */
    public array $characteristicRows = [];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'retail_price' => 'required|numeric|min:0',
            'photo' => 'nullable|image|max:5120',
            'characteristicRows' => 'array',
            'characteristicRows.*.key' => 'nullable|string|max:255',
            'characteristicRows.*.val' => 'nullable|string|max:2000',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function addCharacteristicRow(): void
    {
        $this->characteristicRows[] = ['key' => '', 'val' => ''];
    }

    public function removeCharacteristicRow(int $index): void
    {
        unset($this->characteristicRows[$index]);
        $this->characteristicRows = array_values($this->characteristicRows);
        if ($this->characteristicRows === []) {
            $this->characteristicRows[] = ['key' => '', 'val' => ''];
        }
    }

    protected function characteristicsFromRows(): ?array
    {
        $out = [];
        foreach ($this->characteristicRows as $row) {
            $k = trim($row['key'] ?? '');
            if ($k === '') {
                continue;
            }
            $out[$k] = trim($row['val'] ?? '');
        }

        return $out === [] ? null : $out;
    }

    protected function loadCharacteristicRows(?array $characteristics): void
    {
        $this->characteristicRows = [];
        if (! is_array($characteristics) || $characteristics === []) {
            $this->characteristicRows[] = ['key' => '', 'val' => ''];

            return;
        }
        foreach ($characteristics as $k => $v) {
            if (! is_string($k)) {
                continue;
            }
            $this->characteristicRows[] = [
                'key' => $k,
                'val' => is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE),
            ];
        }
        if ($this->characteristicRows === []) {
            $this->characteristicRows[] = ['key' => '', 'val' => ''];
        }
    }

    public function with(): array
    {
        return [
            'parts' => Part::where('name', 'like', "%{$this->search}%")
                ->latest()
                ->paginate(10),
        ];
    }

    public function store(): void
    {
        if (! $this->canManage()) {
            abort(403);
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'retail_price' => $this->retail_price,
            'characteristics' => $this->characteristicsFromRows(),
        ];

        if ($this->photo) {
            $path = $this->photo->store('parts', 'public');
            $existing = $this->part_id ? Part::find($this->part_id) : null;
            if ($existing?->image_path) {
                Storage::disk('public')->delete($existing->image_path);
            }
            $data['image_path'] = $path;
        }

        Part::updateOrCreate(['id' => $this->part_id], $data);

        $this->photo = null;
        $this->reset(['name', 'retail_price', 'part_id', 'isEditMode', 'characteristicRows']);
        $this->characteristicRows[] = ['key' => '', 'val' => ''];
        session()->flash('message', 'Запчасть успешно сохранена.');
    }

    public function edit(int $id): void
    {
        $part = Part::findOrFail($id);
        $this->part_id = $id;
        $this->name = $part->name;
        $this->retail_price = (string) $part->retail_price;
        $this->loadCharacteristicRows($part->characteristics);
        $this->photo = null;
        $this->isEditMode = true;
        $this->resetValidation();
        $this->scrollToCrudForm();
    }

    public function delete(int $id): void
    {
        if (! $this->canManage()) {
            abort(403);
        }

        $part = Part::findOrFail($id);
        if ($part->image_path) {
            Storage::disk('public')->delete($part->image_path);
        }
        $part->delete();
        session()->flash('message', 'Запчасть удалена.');
    }

    public function cancel(): void
    {
        $this->photo = null;
        $this->reset(['name', 'retail_price', 'part_id', 'isEditMode', 'characteristicRows']);
        $this->characteristicRows[] = ['key' => '', 'val' => ''];
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

            @if ($this->canManage())
            <div id="crud-form" wire:key="part-form-{{ $isEditMode ? 'edit-'.$part_id : 'new' }}" class="mb-8 p-4 bg-gray-50 rounded border ring-2 {{ $isEditMode ? 'ring-indigo-200' : 'ring-transparent' }}">
                <h3 class="text-lg font-semibold mb-4">{{ $isEditMode ? 'Изменить запчасть' : 'Новая запчасть' }}</h3>
                @if($isEditMode)
                    <p class="text-sm text-indigo-600 mb-3">Редактирование #{{ $part_id }} — поля заполнены текущими значениями</p>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Название" />
                        <x-text-input wire:model.live="name" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Цена" />
                        <x-text-input wire:model.live="retail_price" type="number" step="0.01" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('retail_price')" class="mt-1" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label value="Фото" />
                        <input type="file" wire:model="photo" accept="image/*" class="mt-1 block w-full text-sm text-gray-600" />
                        <div wire:loading wire:target="photo" class="text-sm text-gray-500 mt-1">Загрузка…</div>
                        <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                        @if ($photo)
                            <p class="text-xs text-gray-500 mt-1">Предпросмотр: {{ $photo->getClientOriginalName() }}</p>
                        @endif
                    </div>
                    <div class="md:col-span-2">
                        <div class="flex justify-between items-center mb-2">
                            <x-input-label value="Характеристики (JSON как пары ключ — значение)" />
                            <x-secondary-button type="button" wire:click="addCharacteristicRow">+ Строка</x-secondary-button>
                        </div>
                        <div class="space-y-2 border rounded p-3 bg-white">
                            @foreach($characteristicRows as $idx => $row)
                                <div class="flex flex-wrap gap-2 items-start" wire:key="char-{{ $idx }}">
                                    <x-text-input wire:model="characteristicRows.{{ $idx }}.key" placeholder="Название" class="flex-1 min-w-[120px]" />
                                    <x-text-input wire:model="characteristicRows.{{ $idx }}.val" placeholder="Значение" class="flex-1 min-w-[120px]" />
                                    <button type="button" wire:click="removeCharacteristicRow({{ $idx }})" class="text-red-600 text-sm px-2">Удалить</button>
                                </div>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('characteristicRows.*.key')" class="mt-1" />
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

            <div class="mb-4">
                <x-text-input wire:model.live="search" placeholder="Поиск по названию..." class="w-full" />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border w-20">Фото</th>
                            <th class="p-2 border">Название</th>
                            <th class="p-2 border">Цена</th>
                            <th class="p-2 border">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parts as $part)
                            <tr>
                                <td class="p-2 border">
                                    @if($part->image_path)
                                        <img src="{{ Storage::url($part->image_path) }}" alt="" class="h-12 w-12 object-cover rounded border" />
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="p-2 border">{{ $part->name }}</td>
                                <td class="p-2 border">{{ $part->retail_price }} руб.</td>
                                <td class="p-2 border">
                                    @if ($this->canManage())
                                        <button type="button" wire:click="edit({{ $part->id }})" class="text-indigo-600">Ред.</button>
                                        <button type="button" wire:click="askDelete({{ $part->id }}, @js($part->name))" class="text-red-600 ml-2">Удалить</button>
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
