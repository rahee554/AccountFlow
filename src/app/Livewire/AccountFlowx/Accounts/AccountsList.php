<?php

namespace App\Livewire\Accountflow\Accounts;

use Livewire\Component;

class AccountsList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.accounts.accounts-list')->extends($layout)->section('content');
    }
}

