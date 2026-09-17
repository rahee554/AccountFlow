<?php

namespace ArtflowStudio\AccountFlow\Livewire\Accounts;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\Account;
use Livewire\Component;

class AccountsList extends Component
{
    use AuthorizesAccountFlow;

    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewAccounts);
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path').'livewire.accounts.accounts-list';
        $layout = config('accountflow.layout');
        $title = 'Accounts | '.config('accountflow.business_name');

        $view = view($viewpath, [
            'canManage' => $this->canAccountFlow(Ability::ManageAccounts),
            'canTransfer' => $this->canAccountFlow(Ability::ManageTransfers),
            'totalAccounts' => Account::count(),
            'activeAccounts' => Account::active()->count(),
            'totalBalance' => (float) Account::sum('balance'),
        ]);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
