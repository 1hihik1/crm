<?php

namespace App\Livewire\Concerns;

trait WithCompleteOrderConfirmation
{
    public bool $showCompleteModal = false;

    public ?int $completeTargetId = null;

    public string $completeTargetLabel = '';

    public function askCompleteOrder(int $orderId, string $label = ''): void
    {
        $this->completeTargetId = $orderId;
        $this->completeTargetLabel = $label !== '' ? $label : 'заказ #'.$orderId;
        $this->showCompleteModal = true;
    }

    public function cancelComplete(): void
    {
        $this->showCompleteModal = false;
        $this->completeTargetId = null;
        $this->completeTargetLabel = '';
    }

    public function performComplete(): void
    {
        if ($this->completeTargetId !== null && method_exists($this, 'changeStatus')) {
            $this->changeStatus($this->completeTargetId, 'completed');
        }

        $this->cancelComplete();
    }
}
