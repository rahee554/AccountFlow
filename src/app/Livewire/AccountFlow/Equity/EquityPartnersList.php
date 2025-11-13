<?php

namespace App\Livewire\AccountFlow\Equity;

use Livewire\Component;

class EquityPartnersList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath . 'livewire.equity.equity-partners-list')->extends($layout)->section('content');
    }
}
