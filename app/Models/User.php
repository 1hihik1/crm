<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
        'address', 'discount', 'role', 'position', 'salary', 'password',
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
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEmployee() {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    public function isClient() {
        return $this->role === self::ROLE_CLIENT;
    }

    public function cars() { return $this->hasMany(Car::class); }
    public function ordersAsClient() { return $this->hasMany(Order::class, 'user_id'); }
    public function ordersAsEmployee() { return $this->hasMany(Order::class, 'employee_id'); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function employments() { return $this->hasMany(Employment::class); }

}
