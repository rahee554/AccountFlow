<?php

namespace App\Livewire\AccountFlow\Loans;

use App\Models\AccountFlow\Loan;
use App\Models\AccountFlow\LoanUser;
use Carbon\Carbon;
use Livewire\Component;

class CreateLoan extends Component
{
    public ?int $loanId = null;

    public bool $isEdit = false;

    public string $name = '';

    public string $description = '';

    public string $amount = '';

    public int $loan_type = 1;

    public string $loan_partner_id = '';

    public string $roi = '';

    public string $installments = '';

    public string $installment_type = '';

    public string $date = '';

    public string $due_date = '';

    public int $status = 1;

    public array $loanPartners = [];

    public function mount(int $id = null): void
    {
        $this->loanPartners = LoanUser::orderBy('name')->get(['id', 'name'])->toArray();
        $this->date = now()->format('Y-m-d');

        if ($id) {
            $this->isEdit  = true;
            $this->loanId  = $id;
            $loan = Loan::findOrFail($id);

            $this->name             = $loan->name;
            $this->description      = $loan->description ?? '';
            $this->amount           = (string) $loan->amount;
            $this->loan_type        = (int) $loan->loan_type;
            $this->loan_partner_id  = (string) ($loan->loan_partner_id ?? '');
            $this->roi              = (string) ($loan->roi ?? '');
            $this->installments     = (string) ($loan->installments ?? '');
            $this->installment_type = (string) ($loan->installment_type ?? '');
            $this->date             = $loan->date ? Carbon::parse($loan->date)->format('Y-m-d') : '';
            $this->due_date         = $loan->due_date ? Carbon::parse($loan->due_date)->format('Y-m-d') : '';
            $this->status           = (int) ($loan->status ?? 1);
        }
    }

    protected function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'loan_type'        => ['required', 'in:1,2'],
            'loan_partner_id'  => ['required', 'exists:ac_loan_partners,id'],
            'roi'              => ['nullable', 'integer', 'min:0', 'max:100'],
            'installments'     => ['nullable', 'integer', 'min:1'],
            'installment_type' => ['nullable', 'integer'],
            'date'             => ['required', 'date'],
            'due_date'         => ['nullable', 'date', 'after_or_equal:date'],
            'status'           => ['integer'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->isEdit) {
            Loan::findOrFail($this->loanId)->update($validated);
            session()->flash('success', 'Loan updated successfully.');
        } else {
            Loan::create($validated);
            session()->flash('success', 'Loan created successfully.');
        }

        $this->redirect(route('accountflow::loans'), navigate: true);
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path') . 'livewire.loans.create-loan';
        $layout   = config('accountflow.layout');
        $title    = ($this->isEdit ? 'Edit' : 'Create') . ' Loan | ' . config('accountflow.business_name');

        return view($viewpath)
            ->extends($layout)
            ->section('content')
            ->title($title);
    }
}