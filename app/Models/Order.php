<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'car_id', 'workplace_id', 'employee_id',
        'ordered_at', 'deadline', 'completed_at', 'status',
        'subtotal_amount', 'discount_percent', 'discount_amount', 'total_amount',
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function workplace()
    {
        return $this->belongsTo(Workplace::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments->sum('amount');
    }

    public function getDueAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - $this->paid_amount);
    }

    public function isFullyPaid(): bool
    {
        return $this->due_amount <= 0 && (float) $this->total_amount > 0;
    }

    public function getPaymentStatusAttribute(): string
    {
        if ((float) $this->total_amount <= 0) {
            return 'no_amount';
        }

        if ($this->paid_amount <= 0) {
            return 'unpaid';
        }

        if ($this->isFullyPaid()) {
            return 'paid';
        }

        return 'partial';
    }

    public static function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Оплачен',
            'partial' => 'Частично оплачен',
            'unpaid' => 'Не оплачен',
            default => 'Без суммы',
        };
    }

    /** @return array<string, array{label: string, color: string}> */
    public static function statusLabels(): array
    {
        return [
            'new' => ['label' => 'Новый', 'color' => 'bg-blue-100 text-blue-800'],
            'accepted' => ['label' => 'Принят', 'color' => 'bg-sky-100 text-sky-800'],
            'in_progress' => ['label' => 'В работе', 'color' => 'bg-yellow-100 text-yellow-800'],
            'ready' => ['label' => 'Готов', 'color' => 'bg-purple-100 text-purple-800'],
            'completed' => ['label' => 'Завершён', 'color' => 'bg-green-100 text-green-800'],
            'cancelled' => ['label' => 'Отменён', 'color' => 'bg-red-100 text-red-800'],
        ];
    }

    public static function statusLabel(string $status): string
    {
        return self::statusLabels()[$status]['label'] ?? $status;
    }

    public static function statusColor(string $status): string
    {
        return self::statusLabels()[$status]['color'] ?? 'bg-gray-100 text-gray-800';
    }

    public function applyClientDiscount(): void
    {
        $this->loadMissing('client', 'items');

        $subtotal = (float) $this->items->sum(fn ($item) => $item->price * $item->quantity);
        $percent = (int) ($this->client?->discount ?? 0);
        $percent = max(0, min(100, $percent));
        $discountAmount = round($subtotal * $percent / 100, 2);
        $total = max(0, round($subtotal - $discountAmount, 2));

        $this->update([
            'subtotal_amount' => $subtotal,
            'discount_percent' => $percent,
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
        ]);
    }
}
