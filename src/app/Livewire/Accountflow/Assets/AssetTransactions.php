<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\Assets;

use Livewire\Component;

class AssetTransactions extends Component
{
    public function render()
    {
          $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');
        return view($viewpath . 'livewire.assets.asset-transactions')->extends($layout )->section('content');
    }
}

