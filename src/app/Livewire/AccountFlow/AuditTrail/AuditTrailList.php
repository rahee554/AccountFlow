<?php

namespace App\Livewire\AccountFlow\AuditTrail;

use Livewire\Component;

class AuditTrailList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.audit-trail.audit-trail-list';
        $layout = config('accountflow.layout');
        $title = 'Audit Trail List | '.config('accountflow.business_name');

        return view($viewpath)->extends($layout)->section('content')->title($title);
    }
}
