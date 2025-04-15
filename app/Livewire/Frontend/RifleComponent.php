<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class RifleComponent extends Component
{
    public $ruleSection=true;
    public $paymentMethodSection=false;
    public $submitSection=false;
    public $paymentMethod='';


    public function nextToPaymentMethod()
    {
        $this->ruleSection=false;
        $this->paymentMethodSection=true;
    }
    public function paymentBikash()
    {
        $this->paymentMethod='Bikash';
        $this->paymentMethodSection=false;
        $this->submitSection=true;
    }
    public function paymentNagad()
    {
        $this->paymentMethod='Nagad';
        $this->paymentMethodSection=false;
        $this->submitSection=true;
    }
    public function paymentRoket()
    {
        $this->paymentMethod='Roket';
        $this->paymentMethodSection=false;
        $this->submitSection=true;
    }
    public function paymentUpay()
    {
        $this->paymentMethod='Upay';
        $this->paymentMethodSection=false;
        $this->submitSection=true;
    }
    public function render()
    {
        return view('livewire.frontend.rifle-component')->layout('livewire.layout.frontend.base');
    }
}
