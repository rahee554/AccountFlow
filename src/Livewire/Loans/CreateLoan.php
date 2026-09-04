<?php

namespace ArtflowStudio\AccountFlow\Livewire\Loans;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Enums\LoanType;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Facades\Accountflow;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Loan;
use ArtflowStudio\AccountFlow\Models\LoanUser;
use Carbon\Carbon;
use Livewire\Component;

class CreateLoan extends Component
{
    use AuthorizesAccountFlow;

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

    /** Which account the money lands in, or comes out of. */
    public string $account_id = '';

    /** @var array<int,array<string,mixed>> */
    public array $accounts = [];

    public function mount(?int $id = null): void
    {
        $this->authorizeAccountFlow(Ability::ManageLoans);
        $this->loanPartners = LoanUser::orderBy('name')->get(['id', 'name'])->toArray();
        $this->accounts = Account::query()->active()->orderBy('name')->get(['id', 'name'])->toArray();
        $this->date = now()->format('Y-m-d');

        if ($id) {
            $this->isEdit = true;
            $this->loanId = $id;
            $loan = Loan::findOrFail($id);

            $this->name = $loan->name;
            $this->description = $loan->description ?? '';
            $this->amount = (string) $loan->amount;
            $this->loan_type = (int) $loan->loan_type;
            $this->loan_partner_id = (string) ($loan->loan_partner_id ?? '');
            $this->roi = (string) ($loan->roi ?? '');
            $this->installments = (string) ($loan->installments ?? '');
            $this->installment_type = (string) ($loan->installment_type ?? '');
            $this->date = $loan->date ? Carbon::parse($loan->date)->format('Y-m-d') : '';
            $this->due_date = $loan->due_date ? Carbon::parse($loan->due_date)->format('Y-m-d') : '';
            $this->status = (int) ($loan->status ?? 1);
        }
    }

    public function save(): void
    {
        $this->authorizeAccountFlow(Ability::ManageLoans);

        $validated = $this->validate();

        try {
            $this->isEdit ? $this->updateLoan($validated) : $this->createLoan();
        } catch (AccountFlowException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->redirect(route('accountflow::loans'), navigate: true);
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path').'livewire.loans.create-loan';
        $layout = config('accountflow.layout');
        $title = ($this->isEdit ? 'Edit' : 'Create').' Loan | '.config('accountflow.business_name');

        return view($viewpath)
            ->extends($layout)
            ->section('content')
            ->title($title);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'loan_type' => ['required', 'in:1,2'],
            'loan_partner_id' => ['required', 'exists:ac_loan_partners,id'],
            // Optional: the service falls back to the default account.
            'account_id' => ['nullable', 'exists:accounts,id'],
            'roi' => ['nullable', 'integer', 'min:0', 'max:100'],
            'installments' => ['nullable', 'integer', 'min:1'],
            'installment_type' => ['nullable', 'integer'],
            'date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:date'],
            'status' => ['integer'],
        ];
    }

    /**
     * Recording a loan has to move money.
     *
     * This screen used to call `Loan::create()` and stop, so receiving a loan
     * left every balance untouched — the cash never appeared anywhere.
     * LoanService posts it: borrowing brings cash in, lending sends it out.
     *
     * @throws AccountFlowException
     */
    private function createLoan(): void
    {
        $attributes = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'account_id' => $this->account_id ?: null,
            'roi' => $this->roi !== '' ? (int) $this->roi : null,
            'installments' => $this->installments !== '' ? (int) $this->installments : null,
            'installment_type' => $this->installment_type !== '' ? (int) $this->installment_type : null,
            'date' => $this->date ?: null,
            'due_date' => $this->due_date ?: null,
        ];

        $amount = (float) $this->amount;
        $partnerId = (int) $this->loan_partner_id;

        LoanType::from($this->loan_type) === LoanType::Borrowed
            ? Accountflow::loans()->borrow($amount, $partnerId, $attributes)
            : Accountflow::loans()->lend($amount, $partnerId, $attributes);

        session()->flash('success', 'Loan recorded and the money moved.');
    }

    /**
     * Editing only changes the loan's details.
     *
     * The amount is deliberately not editable once the loan has been posted:
     * changing it would leave the ledger entry saying one thing and the loan
     * another. Reverse the loan and record it again instead.
     *
     * @param array<string,mixed> $validated
     *
     * @throws AccountFlowException
     */
    private function updateLoan(array $validated): void
    {
        $loan = Loan::findOrFail($this->loanId);

        if ($loan->transactions()->exists() && (float) $loan->amount !== (float) $this->amount) {
            throw new AccountFlowException(
                'This loan has already been posted, so its amount cannot be changed here. '
                .'Delete it and record it again if the figure was wrong.',
            );
        }

        unset($validated['account_id']);

        $loan->update($validated);

        session()->flash('success', 'Loan updated.');
    }
}
