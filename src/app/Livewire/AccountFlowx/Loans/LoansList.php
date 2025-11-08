<?php

namespace App\Livewire\Accountflow\Loans;

use Livewire\Component;

class LoansList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.loans.loans-list')->extends($layout)->section('content');
    }
}

