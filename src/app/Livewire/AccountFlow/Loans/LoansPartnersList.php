<?php

namespace App\Livewire\Accountflow\Loans;

use Livewire\Component;

class LoansPartnersList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.loans.loans-partners-list')->extends($layout)->section('content');
    }
}

