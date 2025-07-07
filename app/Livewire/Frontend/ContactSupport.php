<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;

class ContactSupport extends Component
{
    public $name, $email, $message;

    public function mount()
    {
        $this->name = auth()->user()->name ?? '';
        $this->email = auth()->user()->email ?? '';
    }

    public function send()
    {
        $this->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'message' => 'required|string|max:1000',
        ]);

        Mail::raw("Message from: {$this->name} ({$this->email})\n\n{$this->message}", function ($mail) {
            $mail->to('info@maxdeposit.my') // <-- আপনার ইমেইল এখানে বসান
                ->subject('Support Request');
        });

        session()->flash('success', 'Your message has been sent successfully!');
        $this->reset('message');
    }

    public function render()
    {
        return view('livewire.frontend.contact-support')->layout('livewire.layout.frontend.base');
    }
}
