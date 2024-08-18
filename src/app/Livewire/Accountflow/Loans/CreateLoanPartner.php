<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\Loans;

use Livewire\Component;

class CreateLoanPartner extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath . 'livewire.loans.create-loan-partner')->extends($layout)->section('content');
    }
}

