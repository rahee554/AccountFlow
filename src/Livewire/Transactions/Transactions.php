<?php

namespace ArtflowStudio\AccountFlow\Livewire\Transactions;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use Illuminate\View\View;
use Livewire\Component;

class Transactions extends Component
{
    use AuthorizesAccountFlow;

    /**
     * Render only the table, with no layout or page header.
     *
     * Usage: <livewire:accountflow.transactions.transactions :standalone="true" />
     */
    public bool $standalone = false;

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewTransactions);
    }

    public function render(): View
    {
        $canManage = $this->canAccountFlow(Ability::ManageTransactions);

        $view = view(config('accountflow.view_path').'livewire.transactions.transactions', [
            'canManage' => $canManage,
            // The edit link is only offered to users who may actually edit.
            'actions' => $canManage
                ? ['raw' => '<a href="{{ route(\'accountflow::transactions.edit\', $row->id) }}" class="btn btn-sm btn-light-info">Edit</a>']
                : [],
        ]);

        if ($this->standalone) {
            return $view;
        }

        return $view
            ->extends(config('accountflow.layout'))
            ->section('content')
            ->title('Transactions | '.config('accountflow.business_name'));
    }
}
