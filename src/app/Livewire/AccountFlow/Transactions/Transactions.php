<?php

namespace App\Livewire\AccountFlow\Transactions;

use Illuminate\View\View;
use Livewire\Component;

class Transactions extends Component
{
    /**
     * When true the component renders only the table (no layout/header).
     * Usage: <livewire:account-flow.transactions.transactions :standalone="true" />
     */
    public bool $standalone = false;

    public function render(): View
    {
        $viewpath = config('accountflow.view_path') . 'livewire.transactions.transactions';
        $layout = config('accountflow.layout');
        $title = 'Transactions | ' . config('accountflow.business_name');

        $view = view($viewpath);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
