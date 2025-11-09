<?php

namespace App\Livewire\Accountflow\Assets;

use Livewire\Component;

class AssetTransactions extends Component
{
    public function render()
    {
          $viewpath = config('accountflow.view_path').'livewire.assets.asset-transactions';
        $layout = config('accountflow.layout');
        $title = 'Asset Transactions | '.config('accountflow.business_name');
        return view($viewpath)->extends($layout )->section('content')->title($title);
    }
}
