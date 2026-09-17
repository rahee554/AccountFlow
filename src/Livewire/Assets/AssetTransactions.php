<?php

namespace ArtflowStudio\AccountFlow\Livewire\Assets;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use Livewire\Component;

class AssetTransactions extends Component
{
    use AuthorizesAccountFlow;

    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewAssets);
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path').'livewire.assets.asset-transactions';
        $layout = config('accountflow.layout');
        $title = 'Asset Transactions | '.config('accountflow.business_name');

        $view = view($viewpath, [
            'canManage' => $this->canAccountFlow(Ability::ManageAssets),
        ]);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
