<?php

namespace App\Livewire\Accountflow\Assets;

use Livewire\Component;

class AssetsList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.assets.assets-list')->extends($layout)->section('content');

    }
}

