<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasWallet;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasWallet, Notifiable;
    use HasRoles;

    const ROLE_ADMIN = 'аdmin';
    const ROLE_EMPLOYEE = 'employee';
    const ROLE_CLIENT = 'client';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'surname', 'name', 'patronymic', 'phone', 'email', 
        'address', 'discount', 'position', 'salary', 'balance', 'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
        ];
    }

    //аксессор чтобы типо склеить поля фио в одно виртуальное поле
    public function getFullNameAttribute(): string
{
    return implode(' ', array_filter([
        $this->surname,
        $this->name,
        $this->patronymic
    ]));
}

    public function isAdmin() {
        return $this->hasRole('admin');
    }

    public function isEmployee() {
        return $this->hasRole('employee');
    }

    public function isClient() {
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

    public function payments() { return $this->hasMany(Payment::class); }

    public function cars() { return $this->hasMany(Car::class); }
    public function ordersAsClient() { return $this->hasMany(Order::class, 'user_id'); }
    public function ordersAsEmployee() { return $this->hasMany(Order::class, 'employee_id'); }
    public function employments() { return $this->hasMany(Employment::class); }

}
