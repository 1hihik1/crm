<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\HasWalletFloat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements Wallet, WalletFloat
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, HasWalletFloat, Notifiable;

    const ROLE_ADMIN = 'аdmin';
    const ROLE_EMPLOYEE = 'employee';
    const ROLE_CLIENT = 'client';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'surname', 'name', 'patronymic', 'phone', 'email',
        'address', 'discount', 'position', 'salary', 'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return implode(' ', array_filter([
            $this->surname,
            $this->name,
            $this->patronymic,
        ]));
    }

    public function getBalance(): float
    {
        return (float) $this->balanceFloat;
    }

    public function depositAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Сумма пополнения должна быть больше нуля.');
        }

        $this->depositFloat($amount, ['type' => 'topup']);
    }

    public function withdrawAmount(float $amount): bool
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Сумма списания должна быть больше нуля.');
        }

        if (! $this->canAfford($amount)) {
            return false;
        }

        $this->withdrawFloat($amount, ['type' => 'payment']);

        return true;
    }

    public function canAfford(float $amount): bool
    {
        return $this->getBalance() >= $amount;
    }

    public function syncWalletBalance(float $target): void
    {
        $current = $this->getBalance();
        $diff = round($target - $current, 2);

        if ($diff > 0) {
            $this->depositFloat($diff, ['type' => 'admin_adjustment']);
        } elseif ($diff < 0) {
            $this->forceWithdrawFloat(abs($diff), ['type' => 'admin_adjustment']);
        }
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isEmployee()
    {
        return $this->hasRole('employee');
    }

    public function isClient()
    {
        return $this->hasRole('client');
    }

    public function isManager(): bool
    {
        return $this->isEmployee() && str_contains(mb_strtolower((string) $this->position), 'менедж');
    }

    public function isMechanic(): bool
    {
        return $this->isEmployee() && str_contains(mb_strtolower((string) $this->position), 'механ');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function ordersAsClient()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function ordersAsEmployee()
    {
        return $this->hasMany(Order::class, 'employee_id');
    }

    public function employments()
    {
        return $this->hasMany(Employment::class);
    }
}
