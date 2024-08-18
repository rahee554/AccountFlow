<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\Wallets;

use Livewire\Component;

class UserWalletsList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.wallets.user-wallets';
        $layout = config('accountflow.layout');

        return view($viewpath . 'livewire.wallets.user-wallets-list')->extends($layout);
    }
}

