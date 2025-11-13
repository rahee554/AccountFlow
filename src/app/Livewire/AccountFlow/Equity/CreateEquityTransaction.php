<?php

namespace App\Livewire\AccountFlow\Equity;

use Livewire\Component;

class CreateEquityTransaction extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath . 'livewire.equity.create-equity-transaction')->extends($layout)->section('content');
    }
}
