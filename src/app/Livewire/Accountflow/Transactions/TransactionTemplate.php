<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\Transactions;

use Livewire\Component;

class TransactionTemplate extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.transactions.transaction-template')->extends($layout)->section('content');
    }
}

