<?php

namespace App\Livewire\Concerns;

trait ScrollsToCrudForm
{
    protected function scrollToCrudForm(): void
    {
        $this->js("document.getElementById('crud-form')?.scrollIntoView({behavior: 'smooth', block: 'start'})");
    }
}
