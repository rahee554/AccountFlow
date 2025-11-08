<?php

namespace App\Livewire\Accountflow\Equity;

use Livewire\Component;

class CreateEquityPartner extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.equity.create-equity-partner')->extends($layout)->section('content');
    }
}

