<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\PlannedPayments;

use Livewire\Component;

class PlannedPaymentsList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.planned-payments.planned-payments-list')->extends($layout)->section('content');
    }
}

