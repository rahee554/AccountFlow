<?php

namespace ArtflowStudio\AccountFlow\Livewire\PaymentMethod;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Services\PaymentMethodService;
use Livewire\Attributes\On;
use Livewire\Component;

class PaymentMethods extends Component
{
    use AuthorizesAccountFlow;

    public $paymentMethods = [];

    /** When true renders only the list (no layout/header) — for embedding. */
    public bool $standalone = false;

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewPaymentMethods);
        $this->loadPaymentMethods();
    }

    public function toggleStatus(int $id, PaymentMethodService $methods): void
    {
        $this->authorizeAccountFlow(Ability::ManagePaymentMethods);

        $method = PaymentMethod::findOrFail($id);

        $method = $method->isActive() ? $methods->deactivate($method) : $methods->activate($method);

        $this->loadPaymentMethods();

        session()->flash('success', $method->name.($method->isActive() ? ' activated.' : ' deactivated.'));
    }

    #[On('payment-method-saved')]
    public function refreshList(): void
    {
        $this->loadPaymentMethods();
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $view = view($viewpath.'livewire.payment-method.payment-methods', [
            'paymentMethods' => $this->paymentMethods,
            'canManage' => $this->canAccountFlow(Ability::ManagePaymentMethods),
            'activeCount' => collect($this->paymentMethods)->filter(fn ($m) => $m->isActive())->count(),
        ]);

        if (! $this->standalone) {
            return $view->extends(config('accountflow.layout'))
                ->section('content')
                ->title('Payment Methods | '.config('accountflow.business_name'));
        }

        return $view;
    }

    protected function loadPaymentMethods(): void
    {
        $this->paymentMethods = PaymentMethod::with('account')
            ->orderBy('name')
            ->get();
    }
}
