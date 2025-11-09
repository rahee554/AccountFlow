<?php

namespace App\Livewire\Accountflow\Equity;

use Livewire\Component;

class CreateEquityPartner extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.equity.create-equity-partner';
        $layout = config('accountflow.layout');
        $title = 'Create Equity Partner | '.config('accountflow.business_name');

        return view($viewpath)->extends($layout)->section('content')->title($title);
    }
}
