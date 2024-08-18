<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\Wallets;

use Livewire\Component;

class UserWallets extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.wallets.user-wallets';
        $layout = config('accountflow.layout');

        return view($viewpath)->extends($layout);
    }
}

