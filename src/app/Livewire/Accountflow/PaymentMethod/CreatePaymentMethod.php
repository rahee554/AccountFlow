<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\PaymentMethod;

use Livewire\Component;
use Livewire\WithFileUploads;

class CreatePaymentMethod extends Component
{
    use WithFileUploads;

    public $form = [
        'name' => null,
        'info' => null,
        'logo_icon' => null,
        'account_id' => null,
        'status' => 1, // 1 = active, 0 = inactive
    ];

    public $logoUpload;

    public $accounts = [];

    protected function rules(): array
    {
        return [
            'form.name' => ['required', 'string', 'max:191'],
            'form.info' => ['nullable', 'string'],
            'logoUpload' => ['nullable', 'image', 'max:2048'], // 2MB max
            'form.account_id' => ['nullable', 'exists:accounts,id'],
            'form.status' => ['required', 'boolean'],
        ];
    }

    protected $messages = [
        'form.name.required' => 'The payment method name is required.',
        'logoUpload.image' => 'The logo/icon must be an image file.',
        'form.account_id.exists' => 'Selected account is invalid.',
    ];

    public function mount(): void
    {
        $this->accounts = \App\Models\AccountFlow\Account::orderBy('name')->get();
    }

    public function save(): void
    {
        $this->validate();

        if ($this->logoUpload) {
            $path = $this->logoUpload->store('payment-methods', 'public');
            $this->form['logo_icon'] = $path;
        }

        $model = new \App\Models\AccountFlow\PaymentMethod;
        $model->name = $this->form['name'];
        $model->info = $this->form['info'];
        $model->logo_icon = $this->form['logo_icon'];
        $model->account_id = $this->form['account_id'];
        $model->status = $this->form['status'] ?? 1; // default active
        $model->save();

        session()->flash('success', 'Payment method created successfully.');

        redirect()->route('accountflow::payment-methods');
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.payment-method.create-payment-method', [
            'accounts' => $this->accounts,
        ])->extends($layout);
    }
}

