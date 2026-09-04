<?php

namespace ArtflowStudio\AccountFlow\Livewire\Accounts;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\Account;
use Livewire\Component;

class CreateAccount extends Component
{
    use AuthorizesAccountFlow;

    public $name;

    public $description;

    public $active = 1;

    public $opening_balance = 0.00;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'active' => 'required|boolean',
        'opening_balance' => 'required|numeric',
    ];

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ManageAccounts);
    }

    public function save()
    {
        $this->authorizeAccountFlow(Ability::ManageAccounts);

        $this->validate();

        Account::create([
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
            'opening_balance' => $this->opening_balance,
            'balance' => $this->opening_balance,
        ]);

        session()->flash('message', 'Account created successfully.');

        return redirect()->route('accountflow::accounts');
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.accounts.create-account';
        $layout = config('accountflow.layout');
        $title = 'Create Account | '.config('accountflow.business_name');

        return view($viewpath)->extends($layout)->section('content')->title($title);
    }
}
