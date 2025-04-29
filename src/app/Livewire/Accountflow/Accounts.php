<?php

namespace App\Livewire\Accountflow;

use Livewire\Component;

class Accounts extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');
        return view($viewpath . 'accounts')->extends($layout)->section('content');
    }
}
