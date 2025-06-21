<?php

namespace App\View\Components;

use Illuminate\View\Component;

class EmptyState extends Component
{
    public string $title;
    public string $description;
    public bool $showCreateButton;

    public function __construct(string $title, string $description, bool $showCreateButton = true)
    {
        $this->title = $title;
        $this->description = $description;
        $this->showCreateButton = $showCreateButton;
    }

    public function render()
    {
        return view('components.empty-state');
    }
}
