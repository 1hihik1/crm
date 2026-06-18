<?php

/**
 * Управление закупками. Логика приёма на склад: метод updatedStatus() (хук Livewire при смене статуса)
 * и дублирующий вызов после сохранения формы в store(), если статус «Прибыло».
 */
use App\Models\Part;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Models\SupplierPartPrice;
use App\Models\User;
use App\Models\Warehouse;
use App\Livewire\Concerns\ScrollsToCrudForm;
use App\Livewire\Concerns\WithDeleteConfirmation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use ScrollsToCrudForm;
    use WithDeleteConfirmation;
    use WithPagination;

    public function canManage(): bool
    {
        $user = Auth::user();

        return $user && ($user->isAdmin() || $user->isManager());
    }

    public string $search = '';

    public ?int $purchase_id = null;
    public bool $isEditMode = false;

    public ?int $supplier_id = null;
    public ?int $user_id = null;
    public string $purchased_at = '';
    public string $status = 'pending';
    public string $comment = '';

    /** @var array<int, array{part_id: ?int, quantity: int|string, purchase_price: string, storage_location_id: ?int}> */
    public array $itemLines = [];

    public static function purchaseStatuses(): array
    {
        return [
            'pending' => 'Ожидает',
            'ordered' => 'Заказано',
            'in_transit' => 'В пути',
            'arrived' => 'Прибыло',
            'cancelled' => 'Отменено',
        ];
    }

    public function mount(): void
    {
        $this->user_id = Auth::id();
        $this->purchased_at = now()->format('Y-m-d\TH:i');
        $this->itemLines = [$this->emptyItemLine()];
    }

    protected function emptyItemLine(): array
    {
        return [
            'part_id' => null,
            'quantity' => 1,
            'purchase_price' => '',
            'storage_location_id' => null,
        ];
    }

    public function addItemLine(): void
    {
        $this->itemLines[] = $this->emptyItemLine();
    }

    public function removeItemLine(int $index): void
    {
        unset($this->itemLines[$index]);
        $this->itemLines = array_values($this->itemLines);
        if ($this->itemLines === []) {
            $this->itemLines = [$this->emptyItemLine()];
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updated($property): void
    {
        if ($property === 'supplier_id') {
            $this->applySupplierPricesToLines();

            return;
        }

        if (preg_match('/^itemLines\.(\d+)\.part_id$/', $property, $matches)) {
            $this->fillLinePrice((int) $matches[1]);
        }
    }

    protected function fillLinePrice(int $index): void
    {
        if (! isset($this->itemLines[$index])) {
            return;
        }

        $partId = $this->itemLines[$index]['part_id'] ?? null;

        if (! $this->supplier_id || ! $partId) {
            $this->itemLines[$index]['purchase_price'] = '';

            return;
        }

        $price = SupplierPartPrice::priceFor((int) $this->supplier_id, (int) $partId);
        $this->itemLines[$index]['purchase_price'] = $price !== null ? (string) $price : '';
    }

    protected function applySupplierPricesToLines(): void
    {
        foreach (array_keys($this->itemLines) as $index) {
            $this->fillLinePrice($index);
        }
    }

    protected function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'user_id' => 'required|exists:users,id',
            'purchased_at' => 'required|date',
            'status' => 'required|string|in:'.implode(',', array_keys(self::purchaseStatuses())),
            'comment' => 'nullable|string|max:5000',
            'itemLines' => 'required|array|min:1',
            'itemLines.*.part_id' => 'required|exists:parts,id',
            'itemLines.*.quantity' => 'required|integer|min:1',
            'itemLines.*.purchase_price' => 'required|numeric|min:0',
            'itemLines.*.storage_location_id' => 'nullable|exists:storage_locations,id',
        ];
    }

    public function with(): array
    {
        return [
            'purchases' => Purchase::query()
                ->with(['supplier', 'employee'])
                ->when($this->search !== '', function ($q) {
                    $q->where(function ($q2) {
                        $q2->whereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$this->search}%"));
                        if (is_numeric($this->search)) {
                            $q2->orWhere('id', (int) $this->search);
                        }
                    });
                })
                ->latest('purchased_at')
                ->paginate(10),
            'suppliers' => Supplier::orderBy('name')->get(),
            'employees' => User::query()
                ->whereHas('roles', fn ($r) => $r->whereIn('name', ['admin', 'employee']))
                ->orderBy('surname')
                ->orderBy('name')
                ->get(),
            'parts' => $this->supplier_id
                ? Part::query()
                    ->whereHas('supplierPrices', fn ($q) => $q->where('supplier_id', $this->supplier_id))
                    ->orderBy('name')
                    ->get()
                : collect(),
            'storageLocations' => StorageLocation::with('room')->orderBy('id')->get(),
            'statuses' => self::purchaseStatuses(),
        ];
    }

    public function updatedStatus(string $value): void
    {
        if ($value !== 'arrived' || ! $this->purchase_id) {
            return;
        }

        $purchase = Purchase::with('items')->find($this->purchase_id);
        if (! $purchase || $purchase->inventory_applied_at) {
            return;
        }

        $this->applyArrivedInventory($purchase);
    }

    protected function applyArrivedInventory(Purchase $purchase): void
    {
        if ($purchase->inventory_applied_at) {
            return;
        }

        $purchase->load('items');
        if ($purchase->items->isEmpty()) {
            session()->flash('message', 'Добавьте позиции закупки перед приёмом на склад.');
            $this->status = $purchase->status;

            return;
        }

        $defaultLocationId = StorageLocation::query()->orderBy('id')->value('id');
        if (! $defaultLocationId) {
            session()->flash('message', 'В системе нет ячеек склада (storage_locations). Создайте помещение и ячейку.');
            $this->status = $purchase->status;

            return;
        }

        try {
            DB::transaction(function () use ($purchase, $defaultLocationId) {
                foreach ($purchase->items as $item) {
                    $locId = $item->storage_location_id ?: $defaultLocationId;
                    $row = Warehouse::firstOrNew([
                        'part_id' => $item->part_id,
                        'storage_location_id' => $locId,
                    ]);
                    $row->quantity = (int) $row->quantity + (int) $item->quantity;
                    $row->save();
                }

                $purchase->update([
                    'status' => 'arrived',
                    'inventory_applied_at' => now(),
                ]);
            });
            session()->flash('message', 'Статус «Прибыло»: остатки на складе обновлены.');
        } catch (\Throwable $e) {
            session()->flash('message', 'Ошибка при обновлении склада: '.$e->getMessage());
            $purchase->refresh();
            $this->status = $purchase->status;
        }
    }

    protected function syncItems(Purchase $purchase): void
    {
        $purchase->items()->delete();
        foreach ($this->itemLines as $line) {
            if (empty($line['part_id'])) {
                continue;
            }
            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'part_id' => (int) $line['part_id'],
                'storage_location_id' => $line['storage_location_id'] ?: null,
                'quantity' => (int) $line['quantity'],
                'purchase_price' => $line['purchase_price'],
            ]);
        }
    }

    protected function totalFromLines(): string
    {
        $sum = 0;
        foreach ($this->itemLines as $line) {
            if (empty($line['part_id']) || $line['purchase_price'] === '' || $line['quantity'] === '') {
                continue;
            }
            $sum += (float) $line['purchase_price'] * (int) $line['quantity'];
        }

        return number_format($sum, 2, '.', '');
    }

    public function store(): void
    {
        if (! $this->canManage()) {
            abort(403);
        }

        $this->validate();

        foreach ($this->itemLines as $index => $line) {
            if (empty($line['part_id'])) {
                continue;
            }

            if ($line['purchase_price'] === '' || ! SupplierPartPrice::priceFor((int) $this->supplier_id, (int) $line['part_id'])) {
                $this->addError('itemLines.'.$index.'.part_id', 'Запчасть отсутствует в прайс-листе выбранного поставщика.');

                return;
            }
        }

        $total = $this->totalFromLines();
        if ((float) $total <= 0) {
            $this->addError('itemLines', 'Укажите хотя бы одну позицию с количеством и ценой.');

            return;
        }

        $wasArrivedInDb = false;
        if ($this->purchase_id) {
            $wasArrivedInDb = (bool) Purchase::whereKey($this->purchase_id)->value('inventory_applied_at');
        }

        $purchase = Purchase::updateOrCreate(
            ['id' => $this->purchase_id],
            [
                'supplier_id' => $this->supplier_id,
                'user_id' => $this->user_id,
                'purchased_at' => $this->purchased_at,
                'status' => $this->status,
                'total_amount' => $total,
                'comment' => $this->comment ?: null,
            ]
        );

        $this->syncItems($purchase);
        $purchase->refresh();

        if ($this->status === 'arrived' && ! $wasArrivedInDb && ! $purchase->inventory_applied_at) {
            $this->applyArrivedInventory($purchase);
        }

        $this->resetPurchaseForm();
        session()->flash('message', 'Закупка сохранена.');
    }

    public function edit(int $id): void
    {
        $p = Purchase::with('items')->findOrFail($id);
        $this->purchase_id = $id;
        $this->supplier_id = $p->supplier_id;
        $this->user_id = $p->user_id;
        $this->purchased_at = $p->purchased_at->format('Y-m-d\TH:i');
        $this->status = $p->status;
        $this->comment = (string) ($p->comment ?? '');
        $this->itemLines = $p->items->map(fn ($i) => [
            'part_id' => $i->part_id,
            'quantity' => $i->quantity,
            'purchase_price' => (string) $i->purchase_price,
            'storage_location_id' => $i->storage_location_id,
        ])->all();
        if ($this->itemLines === []) {
            $this->itemLines = [$this->emptyItemLine()];
        }
        $this->isEditMode = true;
        $this->resetValidation();
        $this->scrollToCrudForm();
    }

    public function delete(int $id): void
    {
        if (! $this->canManage()) {
            abort(403);
        }

        Purchase::findOrFail($id)->delete();
        $this->resetPurchaseForm();
        session()->flash('message', 'Закупка удалена.');
    }

    public function cancel(): void
    {
        $this->resetPurchaseForm();
    }

    public function resetPurchaseForm(): void
    {
        $this->reset(['purchase_id', 'isEditMode', 'supplier_id', 'comment']);
        $this->user_id = Auth::id();
        $this->purchased_at = now()->format('Y-m-d\TH:i');
        $this->status = 'pending';
        $this->itemLines = [$this->emptyItemLine()];
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
                <div id="crud-form" wire:key="purchase-form-{{ $isEditMode ? 'edit-'.$purchase_id : 'new' }}" class="mb-8 p-4 bg-gray-50 rounded border space-y-4 ring-2 {{ $isEditMode ? 'ring-indigo-200' : 'ring-transparent' }}">
                    <h3 class="text-lg font-semibold">{{ $isEditMode ? 'Изменить закупку' : 'Новая закупка' }}</h3>
                    @if($isEditMode)
                        <p class="text-sm text-indigo-600">Редактирование закупки #{{ $purchase_id }}</p>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Поставщик" />
                            <select wire:model.live="supplier_id" class="w-full mt-1 rounded-md border-gray-300 shadow-sm">
                                <option value="">— выберите —</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('supplier_id')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label value="Ответственный" />
                            <select wire:model.live="user_id" class="w-full mt-1 rounded-md border-gray-300 shadow-sm">
                                @foreach($employees as $e)
                                    <option value="{{ $e->id }}">{{ $e->full_name ?? $e->name }} ({{ $e->email }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('user_id')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label value="Дата закупки" />
                            <x-text-input wire:model.live="purchased_at" type="datetime-local" class="w-full mt-1" />
                            <x-input-error :messages="$errors->get('purchased_at')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label value="Статус" />
                            <select wire:model.live="status" class="w-full mt-1 rounded-md border-gray-300 shadow-sm">
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-1" />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label value="Комментарий" />
                            <textarea wire:model.live="comment" rows="2" class="w-full mt-1 rounded-md border-gray-300 shadow-sm"></textarea>
                            <x-input-error :messages="$errors->get('comment')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-medium">Позиции (запчасти)</span>
                            <x-secondary-button type="button" wire:click="addItemLine" :disabled="! $supplier_id">+ Строка</x-secondary-button>
                        </div>
                        @if(! $supplier_id)
                            <p class="text-sm text-amber-700 mb-2">Сначала выберите поставщика — цены подставятся из его прайс-листа автоматически.</p>
                        @else
                            <p class="text-sm text-gray-500 mb-2">Цена закупки берётся из прайс-листа поставщика и не редактируется вручную.</p>
                        @endif
                        <x-input-error :messages="$errors->get('itemLines')" class="mb-2" />
                        <div class="overflow-x-auto border rounded">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-2 border">Запчасть</th>
                                        <th class="p-2 border">Ячейка склада</th>
                                        <th class="p-2 border">Кол-во</th>
                                        <th class="p-2 border">Цена закупки</th>
                                        <th class="p-2 border"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itemLines as $idx => $line)
                                        <tr wire:key="line-{{ $idx }}">
                                            <td class="p-2 border align-top">
                                                <select wire:model.live="itemLines.{{ $idx }}.part_id" @disabled(! $supplier_id) class="w-full rounded-md border-gray-300 shadow-sm min-w-[180px]">
                                                    <option value="">—</option>
                                                    @foreach($parts as $part)
                                                        <option value="{{ $part->id }}">{{ $part->name }}</option>
                                                    @endforeach
                                                </select>
                                                <x-input-error :messages="$errors->get('itemLines.'.$idx.'.part_id')" class="mt-1" />
                                            </td>
                                            <td class="p-2 border align-top">
                                                <select wire:model="itemLines.{{ $idx }}.storage_location_id" class="w-full rounded-md border-gray-300 shadow-sm min-w-[160px]">
                                                    <option value="">По умолчанию</option>
                                                    @foreach($storageLocations as $loc)
                                                        <option value="{{ $loc->id }}">{{ $loc->room?->name }} — {{ $loc->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="p-2 border align-top w-24">
                                                <x-text-input type="number" min="1" wire:model="itemLines.{{ $idx }}.quantity" class="w-full" />
                                                <x-input-error :messages="$errors->get('itemLines.'.$idx.'.quantity')" class="mt-1" />
                                            </td>
                                            <td class="p-2 border align-top w-32">
                                                @if($line['purchase_price'] !== '')
                                                    <span class="font-semibold text-gray-800">{{ number_format((float) $line['purchase_price'], 2, '.', ' ') }} ₽</span>
                                                @else
                                                    <span class="text-xs text-red-500">Нет в прайсе</span>
                                                @endif
                                                <x-input-error :messages="$errors->get('itemLines.'.$idx.'.purchase_price')" class="mt-1" />
                                            </td>
                                            <td class="p-2 border align-top">
                                                <button type="button" wire:click="removeItemLine({{ $idx }})" class="text-red-600 text-xs">Удалить</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">При статусе «Прибыло» количество по строкам добавляется в склад (таблица warehouses). Повторный приём для той же закупки заблокирован.</p>
                    </div>

                    <div class="flex gap-2">
                        <x-primary-button type="button" wire:click="store">{{ $isEditMode ? 'Обновить закупку' : 'Создать закупку' }}</x-primary-button>
                        @if($isEditMode)
                            <x-secondary-button type="button" wire:click="cancel">Отмена</x-secondary-button>
                        @endif
                    </div>
                </div>
            @endif

            <div class="mb-4">
                <x-text-input wire:model.live.debounce.300ms="search" placeholder="Поиск по поставщику или № закупки..." class="w-full" />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 border">№</th>
                            <th class="p-2 border">Поставщик</th>
                            <th class="p-2 border">Дата</th>
                            <th class="p-2 border">Статус</th>
                            <th class="p-2 border">Сумма</th>
                            <th class="p-2 border">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $pur)
                            <tr>
                                <td class="p-2 border">{{ $pur->id }}</td>
                                <td class="p-2 border">{{ $pur->supplier?->name }}</td>
                                <td class="p-2 border">{{ $pur->purchased_at->format('d.m.Y H:i') }}</td>
                                <td class="p-2 border">{{ $statuses[$pur->status] ?? $pur->status }}</td>
                                <td class="p-2 border">{{ $pur->total_amount }} ₽</td>
                                <td class="p-2 border whitespace-nowrap">
                                    @if ($this->canManage())
                                        <button type="button" wire:click="edit({{ $pur->id }})" class="text-indigo-600">Ред.</button>
                                        <button type="button" wire:click="askDelete({{ $pur->id }}, @js('закупку #'.$pur->id))" class="text-red-600 ml-2">Удалить</button>
                                    @else
                                        <span class="text-gray-400 text-sm">Только просмотр</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $purchases->links() }}</div>
        </div>
    </div>
</div>
