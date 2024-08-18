<?php

namespace ArtflowStudio\AccountFlow\App\Livewire\Accountflow\Categories;

use Livewire\Component;

class CategoriesList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.categories.categories-list')->extends($layout)->section('content');
    }
}

