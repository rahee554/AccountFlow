<?php

namespace ArtflowStudio\AccountFlow\Livewire\Equity;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use Livewire\Component;

class CreateEquityTransaction extends Component
{
    use AuthorizesAccountFlow;

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ManageEquity);
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.equity.create-equity-transaction')->extends($layout)->section('content');
    }
}
