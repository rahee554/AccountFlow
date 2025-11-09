<?php

namespace App\Livewire\Accountflow\Transactions;

use Livewire\Component;

class Transactions extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.transactions.transactions';
        $layout = config('accountflow.layout');
        $title = 'Transactions | '.config('accountflow.business_name');


        return view($viewpath)->extends($layout)->section('content')->title($title);
    }
}
