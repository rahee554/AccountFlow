<?php

namespace App\Livewire\AccountFlow\Wallets;

use Livewire\Component;

class TransfersList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'transfers-list')->extends($layout);
    }
}
