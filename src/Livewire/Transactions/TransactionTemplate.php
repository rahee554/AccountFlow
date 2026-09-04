<?php

namespace ArtflowStudio\AccountFlow\Livewire\Transactions;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use Livewire\Component;

class TransactionTemplate extends Component
{
    use AuthorizesAccountFlow;

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewTemplates);
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.transactions.transaction-template')->extends($layout)->section('content');
    }
}
