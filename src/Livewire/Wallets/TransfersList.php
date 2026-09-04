<?php

namespace ArtflowStudio\AccountFlow\Livewire\Wallets;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use Livewire\Component;

class TransfersList extends Component
{
    use AuthorizesAccountFlow;

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewWallets);
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'transfers-list')->extends($layout);
    }
}
