<?php

namespace App\Livewire\AccountFlow\Transfers;

use Livewire\Component;

class TransfersList extends Component
{
    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path') . 'livewire.transfers.transfers-list';
        $layout   = config('accountflow.layout');
        $title    = 'Transfers | ' . config('accountflow.business_name');
        $view     = view($viewpath);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
