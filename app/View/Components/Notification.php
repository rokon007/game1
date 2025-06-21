<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Notification extends Component
{
    public string $type;
    public string $message;

    public function __construct(string $type, string $message)
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function getClasses(): string
    {
        return match($this->type) {
            'success' => 'bg-green-50 border-green-200 text-green-800',
            'error' => 'bg-red-50 border-red-200 text-red-800',
            'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
            'info' => 'bg-blue-50 border-blue-200 text-blue-800',
            default => 'bg-gray-50 border-gray-200 text-gray-800'
        };
    }

    public function getIcon(): string
    {
        return match($this->type) {
            'success' => 'fas fa-check-circle',
            'error' => 'fas fa-exclamation-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'info' => 'fas fa-info-circle',
            default => 'fas fa-bell'
        };
    }

    public function render()
    {
        return view('components.notification');
    }
}
