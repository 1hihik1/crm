<?php

namespace App\Livewire\Concerns;

trait WithPaymentConfirmation
{
    public bool $showPaymentModal = false;

    public ?int $paymentTargetId = null;

    public string $paymentTargetLabel = '';

    public float $paymentAmount = 0;

    public function askPayOrder(int $orderId, float $amount, string $label = ''): void
    {
        $this->paymentTargetId = $orderId;
        $this->paymentAmount = $amount;
        $this->paymentTargetLabel = $label !== '' ? $label : 'заказ #'.$orderId;
        $this->showPaymentModal = true;
    }

    public function askPayCurrentOrder(float $amount, string $label = ''): void
    {
        $this->paymentTargetId = 0;
        $this->paymentAmount = $amount;
        $this->paymentTargetLabel = $label !== '' ? $label : 'текущий заказ';
        $this->showPaymentModal = true;
    }

    public function cancelPayment(): void
    {
        $this->showPaymentModal = false;
        $this->paymentTargetId = null;
        $this->paymentTargetLabel = '';
        $this->paymentAmount = 0;
    }

    public function performPayment(): void
    {
        if ($this->paymentTargetId === 0 && method_exists($this, 'payFromWallet')) {
            $this->payFromWallet();
        } elseif ($this->paymentTargetId !== null && method_exists($this, 'payOrder')) {
            $this->payOrder($this->paymentTargetId);
        }

        $this->cancelPayment();
    }
}
