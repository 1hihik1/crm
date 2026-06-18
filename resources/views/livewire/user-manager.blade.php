<?php

use App\Models\User;
use App\Livewire\Concerns\ScrollsToCrudForm;
use App\Livewire\Concerns\WithDeleteConfirmation;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use ScrollsToCrudForm;
    use WithDeleteConfirmation;
    use WithPagination;

    public string $search = '';

    public ?int $user_id = null;
    public bool $isEditMode = false;

    public string $surname = '';
    public string $name = '';
    public string $patronymic = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $position = '';
    public string $salary = '';
    public string $discount = '0';
    public string $balance = '0';
    public string $password = '';
    public string $role = 'client';

    protected function rules(): array
    {
        return [
            'surname' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'patronymic' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->user_id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'position' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'balance' => 'nullable|numeric|min:0',
            'role' => 'required|in:admin,employee,client',
            'password' => ($this->isEditMode ? 'nullable' : 'required').'|string|min:6',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'users' => User::query()
                ->when($this->search !== '', function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('name', 'like', "%{$this->search}%")
                            ->orWhere('surname', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%");
                    });
                })
                ->latest()
                ->paginate(12),
            'roles' => ['admin' => 'Администратор', 'employee' => 'Сотрудник', 'client' => 'Клиент'],
        ];
    }

    public function store(): void
    {
        $this->validate();

        $data = [
            'surname' => $this->surname ?: null,
            'name' => $this->name,
            'patronymic' => $this->patronymic ?: null,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'address' => $this->address ?: null,
            'position' => $this->position ?: null,
            'salary' => $this->salary === '' ? null : $this->salary,
            'discount' => $this->discount === '' ? 0 : $this->discount,
        ];

        if ($this->password !== '') {
            $data['password'] = bcrypt($this->password);
        }

        $user = User::updateOrCreate(['id' => $this->user_id], $data);
        $user->syncRoles([$this->role]);

        $this->resetForm();
        session()->flash('message', 'Пользователь сохранён.');
    }

    public function edit(int $id): void
    {
        $u = User::with('roles')->findOrFail($id);
        $this->user_id = $id;
        $this->surname = (string) ($u->surname ?? '');
        $this->name = $u->name;
        $this->patronymic = (string) ($u->patronymic ?? '');
        $this->email = $u->email;
        $this->phone = (string) ($u->phone ?? '');
        $this->address = (string) ($u->address ?? '');
        $this->position = (string) ($u->position ?? '');
        $this->salary = $u->salary !== null ? (string) $u->salary : '';
        $this->discount = (string) $u->discount;
        $this->balance = $u->hasRole('client') ? (string) $u->getBalance() : '0';
        $this->role = $u->roles->first()?->name ?? 'client';
        $this->password = '';
        $this->isEditMode = true;
        $this->resetValidation();
        $this->scrollToCrudForm();
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('message', 'Нельзя удалить свою учётную запись.');

            return;
        }
        User::findOrFail($id)->delete();
        $this->resetForm();
        session()->flash('message', 'Пользователь удалён.');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'user_id', 'isEditMode', 'surname', 'name', 'patronymic', 'email', 'phone',
            'address', 'position', 'salary', 'discount', 'balance', 'password', 'role',
        ]);
        $this->discount = '0';
        $this->balance = '0';
        $this->role = 'client';
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Управление пользователями</h2>

            @if (session()->has('message'))
                <div class="mb-4 text-green-600 font-medium">{{ session('message') }}</div>
            @endif

            @include('livewire.partials.delete-modal')

            <div id="crud-form" wire:key="user-form-{{ $isEditMode ? 'edit-'.$user_id : 'new' }}" class="mb-8 p-4 bg-gray-50 rounded border ring-2 {{ $isEditMode ? 'ring-indigo-200' : 'ring-transparent' }}">
                <h3 class="text-lg font-semibold mb-4">{{ $isEditMode ? 'Редактирование' : 'Новый пользователь' }}</h3>
                @if($isEditMode)
                    <p class="text-sm text-indigo-600 mb-3">Редактирование пользователя #{{ $user_id }}</p>
                @endif
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label value="Фамилия" />
                        <x-text-input wire:model.live="surname" class="w-full mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Имя *" />
                        <x-text-input wire:model.live="name" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Отчество" />
                        <x-text-input wire:model.live="patronymic" class="w-full mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Email *" />
                        <x-text-input wire:model.live="email" type="email" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Телефон" />
                        <x-text-input wire:model.live="phone" class="w-full mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Роль *" />
                        <select wire:model.live="role" class="w-full mt-1 rounded-md border-gray-300 shadow-sm">
                            @foreach($roles as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Должность" />
                        <x-text-input wire:model.live="position" class="w-full mt-1" placeholder="менеджер, механик..." />
                    </div>
                    <div>
                        <x-input-label value="Зарплата, ₽" />
                        <x-text-input wire:model.live="salary" type="number" step="0.01" class="w-full mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Скидка, %" />
                        <x-text-input wire:model.live="discount" type="number" step="0.01" class="w-full mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Баланс кошелька, ₽" />
                        <x-text-input wire:model.live="balance" type="number" step="0.01" class="w-full mt-1" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label value="Адрес" />
                        <x-text-input wire:model.live="address" class="w-full mt-1" />
                    </div>
                    <div>
                        <x-input-label value="{{ $isEditMode ? 'Новый пароль (необяз.)' : 'Пароль *' }}" />
                        <x-text-input wire:model="password" type="password" class="w-full mt-1" />
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <x-primary-button wire:click="store">{{ $isEditMode ? 'Обновить' : 'Создать' }}</x-primary-button>
                    @if($isEditMode)
                        <x-secondary-button wire:click="cancel">Отмена</x-secondary-button>
                    @endif
                </div>
            </div>

            <x-text-input wire:model.live.debounce.300ms="search" placeholder="Поиск по ФИО или email..." class="w-full mb-4" />

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">ФИО</th>
                            <th class="p-2 border">Email</th>
                            <th class="p-2 border">Роль</th>
                            <th class="p-2 border">Должность</th>
                            <th class="p-2 border">Скидка</th>
                            <th class="p-2 border">Баланс</th>
                            <th class="p-2 border">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td class="p-2 border">{{ $u->full_name }}</td>
                                <td class="p-2 border">{{ $u->email }}</td>
                                <td class="p-2 border">{{ $roles[$u->roles->first()?->name ?? ''] ?? '—' }}</td>
                                <td class="p-2 border">{{ $u->position ?? '—' }}</td>
                                <td class="p-2 border">{{ $u->discount }}%</td>
                                <td class="p-2 border">{{ $u->hasRole('client') ? number_format($u->getBalance(), 2, '.', ' ') : '—' }} ₽</td>
                                <td class="p-2 border whitespace-nowrap">
                                    <button type="button" wire:click="edit({{ $u->id }})" class="text-indigo-600">Ред.</button>
                                    <button type="button" wire:click="askDelete({{ $u->id }}, @js($u->full_name))" class="text-red-600 ml-2">Удалить</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
</div>
