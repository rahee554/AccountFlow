<?php

namespace App\Livewire\AccountFlow\Categories;

use Livewire\Component;

class CategoriesList extends Component
{
    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path') . 'livewire.categories.categories-list';
        $layout   = config('accountflow.layout');
        $title    = 'Categories | ' . config('accountflow.business_name');
        $view     = view($viewpath);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
