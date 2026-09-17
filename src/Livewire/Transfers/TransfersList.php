<?php

namespace ArtflowStudio\AccountFlow\Livewire\Transfers;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\Transfer;
use Livewire\Component;

class TransfersList extends Component
{
    use AuthorizesAccountFlow;

    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewTransfers);
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path').'livewire.transfers.transfers-list';
        $layout = config('accountflow.layout');
        $title = 'Transfers | '.config('accountflow.business_name');

        $view = view($viewpath, [
            'canManage' => $this->canAccountFlow(Ability::ManageTransfers),
            'totalTransfers' => Transfer::count(),
            'totalAmount' => (float) Transfer::sum('amount'),
            'transfersThisMonth' => Transfer::whereMonth('date', now()->month)->whereYear('date', now()->year)->count(),
        ]);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
