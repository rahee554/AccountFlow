<?php

namespace App\Livewire\AccountFlow\Loans;

use Livewire\Component;

class CreateLoan extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.loans.create-loan')->extends($layout)->section('content');
    }
}
