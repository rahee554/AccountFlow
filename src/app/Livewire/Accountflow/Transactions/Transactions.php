<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\Transactions;

use Livewire\Component;

class Transactions extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');
        $businessName = " | " . value(config('accountflow.business_name'));


        return view($viewpath . 'livewire.transactions.transactions')->extends($layout)->section('content')->title('Transactions' . $businessName);
    }
}

