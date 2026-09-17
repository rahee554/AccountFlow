<?php

namespace ArtflowStudio\AccountFlow\Livewire\Wallets;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\UserTransfer;
use Livewire\Component;

class TransfersList extends Component
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
        $layout = config('accountflow.layout');
        $title = 'Wallet Transfers | '.config('accountflow.business_name');

        // The old path, "accountflow::transfers-list", pointed at a view that
        // doesn't exist — this page's own view lives under livewire.wallets.
        $view = view(config('accountflow.view_path').'livewire.wallets.transfers-list', [
            'canManage' => $this->canAccountFlow(Ability::ManageWallets),
            'totalTransfers' => UserTransfer::count(),
            'totalAmount' => (float) UserTransfer::sum('amount'),
        ]);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
