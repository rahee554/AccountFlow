<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\Accounts;

use ArtflowStudio\AccountFlow\App\Models\Account;
use Livewire\Component;

class CreateAccount extends Component
{
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

    public function save()
    {
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
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.accounts.create-account')->extends($layout)->section('content');
    }
}

