<?php

namespace ArtflowStudio\AccountFlow\Livewire\Assets;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\Asset;
use Livewire\Component;

class AssetsList extends Component
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
        $viewpath = config('accountflow.view_path').'livewire.assets.assets-list';
        $layout = config('accountflow.layout');
        $title = 'Assets List | '.config('accountflow.business_name');

        $view = view($viewpath, [
            'canManage' => $this->canAccountFlow(Ability::ManageAssets),
            'totalAssets' => Asset::count(),
            'activeAssets' => Asset::where('status', 1)->count(),
            'totalValue' => (float) Asset::sum('value'),
        ]);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
