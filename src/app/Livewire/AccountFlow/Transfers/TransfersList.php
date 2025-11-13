<?php

namespace App\Livewire\AccountFlow\Transfers;

use Livewire\Component;

class TransfersList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.transfers.transfers-list')->extends($layout)->section('content');
    }
}
