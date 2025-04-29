<?php

namespace App\Livewire\Accountflow;

use Livewire\Component;

class Categories extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');
        return view('livewire.accountflow.categories')->extends($layout)->section('content');
    }
}
