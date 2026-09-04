<?php

namespace ArtflowStudio\AccountFlow\Livewire\Loans;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\LoanUser;
use Livewire\Component;

class CreateLoanPartner extends Component
{
    use AuthorizesAccountFlow;

    public ?int $partnerId = null;

    public bool $isEdit = false;

    public string $name = '';

    public string $contact = '';

    public string $cnic = '';

    public string $company = '';

    public string $note = '';

    public function mount(?int $id = null): void
    {
        $this->authorizeAccountFlow(Ability::ManageLoans);
        if ($id) {
            $this->isEdit = true;
            $this->partnerId = $id;
            $partner = LoanUser::findOrFail($id);

            $this->name = $partner->name;
            $this->contact = $partner->contact;
            $this->cnic = $partner->cnic;
            $this->company = $partner->company ?? '';
            $this->note = $partner->note ?? '';
        }
    }

    public function save(): void
    {
        $this->authorizeAccountFlow(Ability::ManageLoans);

        $validated = $this->validate();

        if ($this->isEdit) {
            LoanUser::findOrFail($this->partnerId)->update($validated);
            session()->flash('success', 'Loan partner updated successfully.');
        } else {
            LoanUser::create($validated);
            session()->flash('success', 'Loan partner created successfully.');
        }

        $this->redirect(route('accountflow::loans.partners'), navigate: true);
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path').'livewire.loans.create-loan-partner';
        $layout = config('accountflow.layout');
        $title = ($this->isEdit ? 'Edit' : 'Create').' Loan Partner | '.config('accountflow.business_name');

        return view($viewpath)
            ->extends($layout)
            ->section('content')
            ->title($title);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:50'],
            'cnic' => ['required', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ];
    }
}
