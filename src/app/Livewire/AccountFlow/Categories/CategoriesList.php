<?php

namespace App\Livewire\AccountFlow\Categories;

use Livewire\Component;

class CategoriesList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.categories.categories-list';
        $layout = config('accountflow.layout');
        $title = 'Categories List | '.config('accountflow.business_name');

        return view($viewpath)->extends($layout)->section('content')->title($title);
    }
}
