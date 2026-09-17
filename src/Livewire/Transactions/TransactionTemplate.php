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

        // The "Add Transaction Template" action writes data, so it's only
        // offered to users who can actually manage templates (mirrors
        // Transactions::render()'s $canManage gating).
        $canManage = $this->canAccountFlow(Ability::ManageTemplates);

        return view($viewpath.'livewire.transactions.transaction-template', [
            'canManage' => $canManage,
        ])->extends($layout)->section('content')
            ->title('Transaction Templates | '.config('accountflow.business_name'));
    }
}
