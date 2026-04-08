<?php

namespace App\Livewire\AccountFlow\Assets;

use Livewire\Component;

class AssetTransactions extends Component
{
    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path') . 'livewire.assets.asset-transactions';
        $layout   = config('accountflow.layout');
        $title    = 'Asset Transactions | ' . config('accountflow.business_name');
        $view     = view($viewpath);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
