<?php

namespace App\Livewire\AccountFlow\Accounts;

use Livewire\Component;

class AccountsList extends Component
{
    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path') . 'livewire.accounts.accounts-list';
        $layout   = config('accountflow.layout');
        $title    = 'Accounts List | ' . config('accountflow.business_name');
        $view     = view($viewpath);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
