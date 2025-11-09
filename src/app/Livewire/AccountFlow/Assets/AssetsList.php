<?php

namespace App\Livewire\Accountflow\Assets;

use Livewire\Component;

class AssetsList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.assets.assets-list';
        $layout = config('accountflow.layout');
        $title = 'Assets List | '.config('accountflow.business_name');

        return view($viewpath)->extends($layout)->section('content')->title($title);

    }
}
