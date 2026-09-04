<?php

namespace ArtflowStudio\AccountFlow\Livewire\Transfers;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
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
        $view = view($viewpath);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
