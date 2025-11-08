<?php

namespace App\Livewire\Accountflow\AuditTrail;

use Livewire\Component;

class AuditTrailList extends Component
{
    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.audit-trail.audit-trail-list')->extends($layout)->section('content');
    }
}

