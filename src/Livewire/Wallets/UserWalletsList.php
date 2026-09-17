<?php

namespace ArtflowStudio\AccountFlow\Livewire\Wallets;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\UserWallet;
use Livewire\Component;

class UserWalletsList extends Component
{
    use AuthorizesAccountFlow;

    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewWallets);
    }

    public function render()
    {
        // The old path concatenation produced
        // "livewire.wallets.user-walletslivewire.wallets.user-wallets-list",
        // a view that never existed, so this route 500'd on every visit.
        $viewpath = config('accountflow.view_path').'livewire.wallets.user-wallets';
        $layout = config('accountflow.layout');
        $title = 'User Wallets | '.config('accountflow.business_name');

        $view = view($viewpath, [
            'canManage' => $this->canAccountFlow(Ability::ManageWallets),
            'totalWallets' => UserWallet::count(),
            'activeWallets' => UserWallet::active()->count(),
            'totalOutstanding' => (float) UserWallet::sum('balance'),
        ]);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
