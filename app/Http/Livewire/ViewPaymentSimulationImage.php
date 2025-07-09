<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ViewPaymentSimulationImage extends Component
{
    public $imageUrl;

    public function mount($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    public function render()
    {
        return view('livewire.view-payment-simulation-image');
    }
}