<?php

use App\Models\Room;
use App\Models\Workplace;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithPagination;

    public string $room_search = '';
    public string $wp_search = '';

    public ?int $room_id = null;
    public bool $room_edit = false;
    public string $r_name = '';
    public string $r_address = '';
    public string $r_area = '';
    public string $r_purpose = '';

    public ?int $workplace_id = null;
    public bool $wp_edit = false;
    public ?int $wp_room_id = null;
    public string $wp_name = '';

    public function updatingWpSearch(): void
    {
        $this->resetPage();
    }

    protected function roomRules(): array
    {
        return [
            'r_name' => 'required|string|max:255',
            'r_address' => 'nullable|string|max:255',
            'r_area' => 'nullable|numeric|min:0',
            'r_purpose' => 'nullable|string|max:255',
        ];
    }

    protected function workplaceRules(): array
    {
        return [
            'wp_room_id' => 'required|exists:rooms,id',
            'wp_name' => 'required|string|max:255',
        ];
    }

    public function with(): array
    {
        return [
            'rooms' => Room::query()
                ->when($this->room_search !== '', fn ($q) => $q->where('name', 'like', "%{$this->room_search}%"))
                ->withCount('workplaces')
                ->latest()
                ->limit(100)
                ->get(),
            'workplaces' => Workplace::query()
                ->with('room')
                ->when($this->wp_search !== '', function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('name', 'like', "%{$this->wp_search}%")
                            ->orWhereHas('room', fn ($r) => $r->where('name', 'like', "%{$this->wp_search}%"));
                    });
                })
                ->orderBy('room_id')
                ->latest()
                ->paginate(10),
        ];
    }

    public function storeRoom(): void
    {
        $this->validate($this->roomRules());

        Room::updateOrCreate(
            ['id' => $this->room_id],
            [
                'name' => $this->r_name,
                'address' => $this->r_address ?: null,
                'area' => $this->r_area === '' ? null : $this->r_area,
                'purpose' => $this->r_purpose ?: null,
            ]
        );

        $this->resetRoomForm();
        session()->flash('message', 'Помещение сохранено.');
    }

    public function editRoom(int $id): void
    {
        $room = Room::findOrFail($id);
        $this->room_id = $id;
        $this->r_name = $room->name;
        $this->r_address = (string) ($room->address ?? '');
        $this->r_area = $room->area !== null ? (string) $room->area : '';
        $this->r_purpose = (string) ($room->purpose ?? '');
        $this->room_edit = true;
    }

    public function deleteRoom(int $id): void
    {
        Room::findOrFail($id)->delete();
        $this->resetRoomForm();
        session()->flash('message', 'Помещение удалено.');
    }

    public function cancelRoom(): void
    {
        $this->resetRoomForm();
    }

    public function resetRoomForm(): void
    {
        $this->reset(['room_id', 'room_edit', 'r_name', 'r_address', 'r_area', 'r_purpose']);
    }

    public function storeWorkplace(): void
    {
        $this->validate($this->workplaceRules());

        Workplace::updateOrCreate(
            ['id' => $this->workplace_id],
            [
                'room_id' => $this->wp_room_id,
                'name' => $this->wp_name,
            ]
        );

        $this->resetWorkplaceForm();
        session()->flash('message', 'Рабочее место сохранено.');
    }

    public function editWorkplace(int $id): void
    {
        $wp = Workplace::findOrFail($id);
        $this->workplace_id = $id;
        $this->wp_room_id = $wp->room_id;
        $this->wp_name = $wp->name;
        $this->wp_edit = true;
    }

    public function deleteWorkplace(int $id): void
    {
        Workplace::findOrFail($id)->delete();
        $this->resetWorkplaceForm();
        session()->flash('message', 'Рабочее место удалено.');
    }

    public function cancelWorkplace(): void
    {
        $this->resetWorkplaceForm();
    }

    public function resetWorkplaceForm(): void
    {
        $this->reset(['workplace_id', 'wp_edit', 'wp_room_id', 'wp_name']);
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white p-6 shadow sm:rounded-lg">
            @if (session()->has('message'))
                <div class="mb-4 text-green-600 font-medium">
                    {{ session('message') }}
                </div>
            @endif

            @if (!Auth::user()->isClient())
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    <!-- Помещения -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Помещения</h3>
                        <div class="mb-4 p-4 bg-gray-50 rounded border">
                            <h4 class="font-medium mb-3">{{ $room_edit ? 'Изменить помещение' : 'Новое помещение' }}</h4>
                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <x-input-label value="Название" />
                                    <x-text-input wire:model="r_name" class="w-full mt-1" />
                                    <x-input-error :messages="$errors->get('r_name')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label value="Адрес / ориентир" />
                                    <x-text-input wire:model="r_address" class="w-full mt-1" />
                                    <x-input-error :messages="$errors->get('r_address')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label value="Площадь, м²" />
                                    <x-text-input wire:model="r_area" type="number" step="0.01" class="w-full mt-1" />
                                    <x-input-error :messages="$errors->get('r_area')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label value="Назначение" />
                                    <x-text-input wire:model="r_purpose" class="w-full mt-1" />
                                    <x-input-error :messages="$errors->get('r_purpose')" class="mt-1" />
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <x-primary-button wire:click="storeRoom">{{ $room_edit ? 'Обновить' : 'Создать' }}</x-primary-button>
                                @if($room_edit)
                                    <x-secondary-button wire:click="cancelRoom">Отмена</x-secondary-button>
                                @endif
                            </div>
                        </div>
                        <x-text-input wire:model.live.debounce.300ms="room_search" placeholder="Поиск помещений..." class="w-full mb-3" />
                        <div class="overflow-x-auto border rounded">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-2 border">Название</th>
                                        <th class="p-2 border">Адрес</th>
                                        <th class="p-2 border">Боксы</th>
                                        <th class="p-2 border">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rooms as $room)
                                        <tr>
                                            <td class="p-2 border font-medium">{{ $room->name }}</td>
                                            <td class="p-2 border">{{ $room->address ?? '—' }}</td>
                                            <td class="p-2 border">{{ $room->workplaces_count }}</td>
                                            <td class="p-2 border whitespace-nowrap">
                                                <button type="button" wire:click="editRoom({{ $room->id }})" class="text-indigo-600">Ред.</button>
                                                <button type="button" wire:click="deleteRoom({{ $room->id }})" wire:confirm="Удалить помещение и связанные рабочие места?" class="text-red-600 ml-2">Удалить</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Рабочие места -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Рабочие места (боксы)</h3>
                        <div class="mb-4 p-4 bg-gray-50 rounded border">
                            <h4 class="font-medium mb-3">{{ $wp_edit ? 'Изменить место' : 'Новое рабочее место' }}</h4>
                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <x-input-label value="Помещение" />
                                    <select wire:model="wp_room_id" class="w-full mt-1 rounded-md border-gray-300 shadow-sm">
                                        <option value="">— выберите —</option>
                                        @foreach(\App\Models\Room::orderBy('name')->get() as $r)
                                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('wp_room_id')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label value="Название (например, Бокс №1)" />
                                    <x-text-input wire:model="wp_name" class="w-full mt-1" />
                                    <x-input-error :messages="$errors->get('wp_name')" class="mt-1" />
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <x-primary-button wire:click="storeWorkplace">{{ $wp_edit ? 'Обновить' : 'Создать' }}</x-primary-button>
                                @if($wp_edit)
                                    <x-secondary-button wire:click="cancelWorkplace">Отмена</x-secondary-button>
                                @endif
                            </div>
                        </div>
                        <x-text-input wire:model.live.debounce.300ms="wp_search" placeholder="Поиск по месту или помещению..." class="w-full mb-3" />
                        <div class="overflow-x-auto border rounded">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-2 border">Название</th>
                                        <th class="p-2 border">Помещение</th>
                                        <th class="p-2 border">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workplaces as $wp)
                                        <tr>
                                            <td class="p-2 border">{{ $wp->name }}</td>
                                            <td class="p-2 border">{{ $wp->room?->name ?? '—' }}</td>
                                            <td class="p-2 border whitespace-nowrap">
                                                <button type="button" wire:click="editWorkplace({{ $wp->id }})" class="text-indigo-600">Ред.</button>
                                                <button type="button" wire:click="deleteWorkplace({{ $wp->id }})" wire:confirm="Удалить это рабочее место?" class="text-red-600 ml-2">Удалить</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $workplaces->links() }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
