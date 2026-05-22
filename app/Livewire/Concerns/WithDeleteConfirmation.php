<?php

namespace App\Livewire\Concerns;

trait WithDeleteConfirmation
{
    public bool $showDeleteModal = false;

    public ?int $deleteTargetId = null;

    public string $deleteTargetLabel = '';

    public function askDelete(int $id, string $label = 'эту запись'): void
    {
        $this->deleteTargetId = $id;
        $this->deleteTargetLabel = $label;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetId = null;
        $this->deleteTargetLabel = '';
    }

    public function performDelete(): void
    {
        if ($this->deleteTargetId !== null) {
            $this->delete($this->deleteTargetId);
        }
        $this->cancelDelete();
    }
}
