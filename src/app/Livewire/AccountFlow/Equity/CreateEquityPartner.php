<?php

namespace App\Livewire\AccountFlow\Equity;

use App\Models\AccountFlow\EquityPartner;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateEquityPartner extends Component
{
    public ?int $partnerId = null;

    public bool $isEdit = false;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $national_id = '';

    public string $company = '';

    public string $ownership_percentage = '';

    public string $current_equity = '0';

    public string $joined_at = '';

    public string $notes = '';

    public bool $is_active = true;

    public function mount(int $id = null): void
    {
        if ($id) {
            $this->isEdit    = true;
            $this->partnerId = $id;
            $partner = EquityPartner::findOrFail($id);

            $this->name                 = $partner->name;
            $this->email                = $partner->email ?? '';
            $this->phone                = $partner->phone ?? '';
            $this->address              = $partner->address ?? '';
            $this->national_id          = $partner->national_id ?? '';
            $this->company              = $partner->company ?? '';
            $this->ownership_percentage = (string) ($partner->ownership_percentage ?? '');
            $this->current_equity       = (string) ($partner->current_equity ?? '0');
            $this->joined_at            = $partner->joined_at ? \Carbon\Carbon::parse($partner->joined_at)->format('Y-m-d') : '';
            $this->notes                = $partner->notes ?? '';
            $this->is_active            = (bool) $partner->is_active;
        } else {
            $this->joined_at = now()->format('Y-m-d');
        }
    }

    protected function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:255'],
            'email'                => ['nullable', 'email', 'max:255'],
            'phone'                => ['nullable', 'string', 'max:50'],
            'address'              => ['nullable', 'string', 'max:500'],
            'national_id'          => ['nullable', 'string', 'max:50'],
            'company'              => ['nullable', 'string', 'max:255'],
            'ownership_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'current_equity'       => ['nullable', 'numeric', 'min:0'],
            'joined_at'            => ['nullable', 'date'],
            'notes'                => ['nullable', 'string'],
            'is_active'            => ['boolean'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->isEdit) {
            EquityPartner::findOrFail($this->partnerId)->update($validated);
            session()->flash('success', 'Equity partner updated successfully.');
        } else {
            EquityPartner::create($validated);
            session()->flash('success', 'Equity partner created successfully.');
        }

        $this->redirect(route('accountflow::equity.partners'), navigate: true);
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path') . 'livewire.equity.create-equity-partner';
        $layout   = config('accountflow.layout');
        $title    = ($this->isEdit ? 'Edit' : 'Create') . ' Equity Partner | ' . config('accountflow.business_name');

        return view($viewpath)
            ->extends($layout)
            ->section('content')
            ->title($title);
    }
}

