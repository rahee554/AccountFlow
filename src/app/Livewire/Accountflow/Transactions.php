<?php

namespace App\Livewire\Accountflow;

use Livewire\Component;

class Transactions extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');
        return view($viewpath . 'transactions')->extends($layout)->section('content');
    }
}
