<?php

namespace App\Livewire\Accountflow\Equity;

use Livewire\Component;

class EquityTransactionsList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath . 'livewire.equity.equity-transactions-list')->extends($layout)->section('content');
    }
}

