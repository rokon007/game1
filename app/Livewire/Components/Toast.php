<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\On;

class Toast extends Component
{
    public $message = '';
    public $type = 'success'; // success, error, info, warning
    public $visible = false;

    protected $listeners = ['showToast'];


    #[On('showToast')]
    public function showToast($message, $type = 'success')
    {
       // dd($message);
        $this->message = $message;
        $this->type = $type;
        $this->visible = true;

        // Hide automatically after 5 seconds
        $this->dispatch('auto-hide-toast')->self();
    }

    public function getClasses()
    {
        return match ($this->type) {
            'success' => 'toast-success',
            'error' => 'toast-error',
            'info' => 'toast-info',
            'warning' => 'toast-warning',
            default => 'toast-default',
        };
    }

    public function getIcon()
    {
        return match ($this->type) {
            'success' => 'fas fa-check-circle text-green-500',
            'error' => 'fas fa-times-circle text-red-500',
            'info' => 'fas fa-info-circle text-blue-500',
            'warning' => 'fas fa-exclamation-triangle text-yellow-500',
            default => 'fas fa-info-circle text-gray-500',
        };
    }

    public function render()
    {
        return view('livewire.components.toast');
    }
}
