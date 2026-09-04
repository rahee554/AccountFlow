<?php

namespace ArtflowStudio\AccountFlow\Livewire\Transfers;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Facades\Accountflow;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Transfer;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateTransfer extends Component
{
    use AuthorizesAccountFlow;

    public $transferId; // For edit mode

    public $amount;

    public $from_account;

    public $to_account;

    public $date;

    public $description;

    public $isEdit = false; // Track if we're in edit mode

    public $accounts = []; // Available accounts

    protected $rules = [
        'amount' => 'required|numeric|min:0.01',
        'from_account' => 'required|integer|different:to_account|exists:accounts,id',
        'to_account' => 'required|integer|exists:accounts,id',
        'date' => 'required|date',
        'description' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'amount.required' => 'Transfer amount is required',
        'amount.min' => 'Transfer amount must be greater than 0',
        'from_account.required' => 'Source account is required',
        'from_account.different' => 'Source and destination accounts must be different',
        'to_account.required' => 'Destination account is required',
        'date.required' => 'Transfer date is required',
    ];

    public function mount($id = null)
    {
        $this->authorizeAccountFlow(Ability::ManageTransfers);
        $this->accounts = Account::where('active', true)->get();

        if ($id) {
            $this->isEdit = true;
            $this->transferId = $id;

            try {
                $transfer = Transfer::findOrFail($id);

                $this->amount = $transfer->amount;
                $this->from_account = $transfer->from_account;
                $this->to_account = $transfer->to_account;
                $this->date = $transfer->date;
                $this->description = $transfer->description;

            } catch (Exception $e) {
                session()->flash('error', 'Transfer not found or invalid ID.');

                return redirect()->route('accountflow::transfers');
            }
        } else {
            $this->date = now()->format('Y-m-d');
        }
    }

    public function addTransfer()
    {
        $this->authorizeAccountFlow(Ability::ManageTransfers);

        $this->validate();

        try {
            // TransferService posts both ledger legs, so the money actually
            // moves and a later recalculateAll() agrees with the balance. This
            // screen used to write the Transfer row on its own, which left
            // every balance untouched.
            $payload = [
                'amount' => $this->amount,
                'from_account' => $this->from_account,
                'to_account' => $this->to_account,
                'date' => $this->date,
                'description' => $this->description,
                'created_by' => Auth::id(),
            ];

            if ($this->isEdit) {
                Accountflow::transfers()->update(
                    Transfer::findOrFail($this->transferId),
                    $payload,
                );

                session()->flash('success', 'Transfer updated successfully!');

                return redirect()->route('accountflow::transfers.list');
            }

            Accountflow::transfers()->create($payload);

            session()->flash('success', 'Transfer created successfully!');

            $this->reset(['amount', 'from_account', 'to_account', 'description']);
            $this->date = now()->format('Y-m-d');
        } catch (AccountFlowException $e) {
            // Insufficient balance, same-account transfer, non-positive amount.
            session()->flash('error', $e->getMessage());
        } catch (Exception $e) {
            session()->flash('error', 'Error '.($this->isEdit ? 'updating' : 'creating').' transfer: '.$e->getMessage());
        }
    }

    public function getFromAccountBalanceProperty()
    {
        if ($this->from_account) {
            $account = $this->accounts->firstWhere('id', $this->from_account);

            return $account ? $account->balance : 0;
        }

        return 0;
    }

    public function getToAccountBalanceProperty()
    {
        if ($this->to_account) {
            $account = $this->accounts->firstWhere('id', $this->to_account);

            return $account ? $account->balance : 0;
        }

        return 0;
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.transfers.create-transfer')->extends($layout)->section('content');
    }
}
