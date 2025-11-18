<?php

namespace App\Livewire\AccountFlow\Transfers;

use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Transfer;
use ArtflowStudio\AccountFlow\Facades\Accountflow;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateTransfer extends Component
{
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

            } catch (\Exception $e) {
                session()->flash('error', 'Transfer not found or invalid ID.');

                return redirect()->route('accountflow::transfers');
            }
        } else {
            $this->date = now()->format('Y-m-d');
        }
    }

    public function addTransfer()
    {
        $this->validate();

        try {
            if ($this->isEdit) {
                // Update existing transfer
                $transfer = Transfer::findOrFail($this->transferId);

                // Check if source account has sufficient balance (excluding current transfer amount)
                $fromAccount = Account::find($this->from_account);
                $requiredBalance = $this->amount;

                // If changing from account or amount, check balance
                if ($transfer->from_account != $this->from_account || $transfer->amount != $this->amount) {
                    if ($transfer->from_account == $this->from_account) {
                        // Same account, add back the old amount for balance check
                        $requiredBalance = $this->amount - $transfer->amount;
                    }

                    if ($fromAccount->balance < $requiredBalance) {
                        session()->flash('error', 'Insufficient balance in source account');

                        return;
                    }
                }

                $transfer->update([
                    'amount' => $this->amount,
                    'from_account' => $this->from_account,
                    'to_account' => $this->to_account,
                    'date' => $this->date,
                    'description' => $this->description,
                ]);

                session()->flash('success', 'Transfer updated successfully!');
            } else {
                // Create new transfer
                $fromAccount = Account::find($this->from_account);
                if (! $fromAccount || $fromAccount->balance < $this->amount) {
                    session()->flash('error', 'Insufficient balance in source account');

                    return;
                }

                Transfer::create([
                    'amount' => $this->amount,
                    'from_account' => $this->from_account,
                    'to_account' => $this->to_account,
                    'unique_id' => generateUniqueID(Transfer::class, 'unique_id'),
                    'date' => $this->date,
                    'description' => $this->description,
                    'created_by' => Auth::id(),
                ]);

                session()->flash('success', 'Transfer created successfully!');

                // Reset form for create mode
                $this->reset(['amount', 'from_account', 'to_account', 'description']);
                $this->date = now()->format('Y-m-d');
            }

            // Update account balances
            Accountflow::accounts()->updateAllAccountBalances();

            if ($this->isEdit) {
                return redirect()->route('accountflow::transfers');
            }

        } catch (\Exception $e) {
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
