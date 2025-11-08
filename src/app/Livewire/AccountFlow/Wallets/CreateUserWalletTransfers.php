<?php

namespace App\Livewire\Accountflow\Wallets;

use Livewire\Component;

class CreateUserWalletTransfers extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.wallets.create-user-wallet-transfers')->extends($layout);
    }
}

