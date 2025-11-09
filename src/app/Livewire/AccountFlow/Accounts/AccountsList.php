<?php

namespace App\Livewire\Accountflow\Accounts;

use Livewire\Component;

class AccountsList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.accounts.accounts-list';
        $layout = config('accountflow.layout');
        $title = 'Accounts List | '.config('accountflow.business_name');

        return view($viewpath)->extends($layout)->section('content')->title($title);
    }
}
