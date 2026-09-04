<?php

namespace ArtflowStudio\AccountFlow\Livewire\PaymentMethod;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use Livewire\Component;

class PaymentMethods extends Component
{
    use AuthorizesAccountFlow;

    public $paymentMethods = [];

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewPaymentMethods);
        $this->loadPaymentMethods();
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.payment-method.payment-methods', [
            'paymentMethods' => $this->paymentMethods,
        ])->extends($layout);
    }

    protected function loadPaymentMethods(): void
    {
        $this->paymentMethods = \ArtflowStudio\AccountFlow\Models\PaymentMethod::with('account')
            ->orderBy('name')
            ->get();
    }
}
