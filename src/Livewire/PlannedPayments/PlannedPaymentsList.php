<?php

namespace ArtflowStudio\AccountFlow\Livewire\PlannedPayments;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\PlannedPayment;
use Livewire\Component;

class PlannedPaymentsList extends Component
{
    use AuthorizesAccountFlow;

    /** When true renders only the table (no layout/header). */
    public bool $standalone = false;

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewPlannedPayments);
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path').'livewire.planned-payments.planned-payments-list';
        $layout = config('accountflow.layout');
        $title = 'Planned Payments | '.config('accountflow.business_name');

        $view = view($viewpath, [
            'canManage' => $this->canAccountFlow(Ability::ManagePlannedPayments),
            'totalPlanned' => (float) PlannedPayment::sum('amount'),
            'upcomingCount' => PlannedPayment::whereBetween('next_run_date', [now()->toDateString(), now()->addDays(7)->toDateString()])->count(),
            'autoPostCount' => PlannedPayment::where('auto_post', true)->count(),
        ]);

        if (! $this->standalone) {
            return $view->extends($layout)->section('content')->title($title);
        }

        return $view;
    }
}
