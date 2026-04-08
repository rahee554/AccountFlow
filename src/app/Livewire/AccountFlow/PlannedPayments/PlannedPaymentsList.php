<?php

namespace App\Livewire\AccountFlow\PlannedPayments;

use Livewire\Component;

class PlannedPaymentsList extends Component
{
    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path') . 'livewire.planned-payments.planned-payments-list';
        $layout   = config('accountflow.layout');
        $title    = 'Planned Payments | ' . config('accountflow.business_name');
        $view     = view($viewpath);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
