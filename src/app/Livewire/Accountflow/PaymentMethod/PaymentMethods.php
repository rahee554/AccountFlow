<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\PaymentMethod;

use Livewire\Component;

class PaymentMethods extends Component
{
    public $paymentMethods = [];

    public function mount(): void
    {
        $this->loadPaymentMethods();
    }

    protected function loadPaymentMethods(): void
    {
        $this->paymentMethods = \App\Models\AccountFlow\PaymentMethod::with('account')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.payment-method.payment-methods', [
            'paymentMethods' => $this->paymentMethods,
        ])->extends($layout);
    }
}

