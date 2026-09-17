<?php

namespace ArtflowStudio\AccountFlow\Livewire\Accounts;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Services\AccountService;
use Livewire\Component;

class CreateAccount extends Component
{
    use AuthorizesAccountFlow;

    public $name;

    public $description;

    public $active = 1;

    public $opening_balance = 0.00;

    public ?int $accountId = null;

    public bool $isEdit = false;

    /** When true renders only the form (no layout/header) — for embedding. */
    public bool $standalone = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'active' => 'required|boolean',
        'opening_balance' => 'required|numeric',
    ];

    public function mount($id = null): void
    {
        $this->authorizeAccountFlow(Ability::ManageAccounts);

        if ($id) {
            $account = Account::findOrFail($id);

            $this->isEdit = true;
            $this->accountId = $account->id;
            $this->name = $account->name;
            $this->description = $account->description;
            $this->active = (int) $account->active;
            $this->opening_balance = $account->opening_balance;
        }
    }

    public function save(AccountService $accounts)
    {
        $this->authorizeAccountFlow(Ability::ManageAccounts);

        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
            'opening_balance' => $this->opening_balance,
        ];

        if ($this->isEdit) {
            $accounts->update(Account::findOrFail($this->accountId), $data);
            session()->flash('success', 'Account updated successfully.');
        } else {
            $accounts->create($data);
            session()->flash('message', 'Account created successfully.');
        }

        if ($this->standalone) {
            $this->reset('name', 'description', 'opening_balance', 'isEdit', 'accountId');
            $this->active = 1;
            $this->dispatch('refreshTable');

            return null;
        }

        return redirect()->route('accountflow::accounts');
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.accounts.create-account';
        $layout = config('accountflow.layout');
        $title = ($this->isEdit ? 'Edit' : 'Create').' Account | '.config('accountflow.business_name');
        $view = view($viewpath);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
