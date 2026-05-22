<?php

namespace App\Traits;

use InvalidArgumentException;

trait HasWallet
{
    public function getBalance(): float
    {
        return (float) ($this->balance ?? 0);
    }

    public function deposit(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Сумма пополнения должна быть больше нуля.');
        }

        $this->increment('balance', $amount);
        $this->refresh();
    }

    public function withdraw(float $amount): bool
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Сумма списания должна быть больше нуля.');
        }

        if ($this->getBalance() < $amount) {
            return false;
        }

        $this->decrement('balance', $amount);
        $this->refresh();

        return true;
    }

    public function canAfford(float $amount): bool
    {
        return $this->getBalance() >= $amount;
    }
}
