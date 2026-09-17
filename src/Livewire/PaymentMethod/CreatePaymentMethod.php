<?php

namespace ArtflowStudio\AccountFlow\Livewire\PaymentMethod;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Services\PaymentMethodService;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreatePaymentMethod extends Component
{
    use AuthorizesAccountFlow;
    use WithFileUploads;

    public $form = [
        'name' => null,
        'info' => null,
        'logo_icon' => null,
        'account_id' => null,
        'status' => 1, // 1 = active, 2 = inactive — matches PaymentMethodService/migration
    ];

    public $logoUpload;

    public $accounts = [];

    public ?int $methodId = null;

    public bool $isEdit = false;

    /** When true renders only the form (no layout/header) — for embedding. */
    public bool $standalone = false;

    protected $messages = [
        'form.name.required' => 'The payment method name is required.',
        'logoUpload.image' => 'The logo/icon must be an image file.',
        'form.account_id.exists' => 'Selected account is invalid.',
    ];

    public function mount($id = null): void
    {
        $this->authorizeAccountFlow(Ability::ManagePaymentMethods);
        $this->accounts = Account::orderBy('name')->get();

        if ($id) {
            $this->loadForEdit((int) $id);
        }
    }

    public function save(PaymentMethodService $methods): void
    {
        $this->authorizeAccountFlow(Ability::ManagePaymentMethods);

        $this->validate();

        if ($this->logoUpload) {
            $this->form['logo_icon'] = $this->logoUpload->store('payment-methods', 'public');
        }

        if ($this->isEdit) {
            $methods->update(PaymentMethod::findOrFail($this->methodId), $this->form);
            session()->flash('success', 'Payment method updated successfully.');
        } else {
            $methods->create($this->form);
            session()->flash('success', 'Payment method created successfully.');
        }

        if ($this->standalone) {
            $this->reset('form', 'logoUpload', 'isEdit', 'methodId');
            $this->dispatch('payment-method-saved');

            return;
        }

        redirect()->route('accountflow::payment-methods');
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $view = view($viewpath.'livewire.payment-method.create-payment-method', [
            'accounts' => $this->accounts,
        ]);

        if (! $this->standalone) {
            return $view->extends(config('accountflow.layout'))
                ->section('content')
                ->title(($this->isEdit ? 'Edit' : 'Add').' Payment Method | '.config('accountflow.business_name'));
        }

        return $view;
    }

    protected function rules(): array
    {
        return [
            'form.name' => ['required', 'string', 'max:191'],
            'form.info' => ['nullable', 'string'],
            'logoUpload' => ['nullable', 'image', 'max:2048'], // 2MB max
            'form.account_id' => ['nullable', 'exists:accounts,id'],
            'form.status' => ['required', 'in:1,2'],
        ];
    }

    private function loadForEdit(int $id): void
    {
        $method = PaymentMethod::findOrFail($id);

        $this->isEdit = true;
        $this->methodId = $method->id;
        $this->form = [
            'name' => $method->name,
            'info' => $method->info,
            'logo_icon' => $method->logo_icon,
            'account_id' => $method->account_id,
            'status' => $method->status,
        ];
    }
}
