<?php

namespace ArtflowStudio\AccountFlow\Livewire\Wallets;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Facades\Accountflow;
use ArtflowStudio\AccountFlow\Models\Account;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Move money into, out of, or between staff wallets.
 *
 * This component had only `mount()` and `render()` — no save method — so the
 * screen could be opened and nothing could ever be recorded.
 */
class CreateUserWalletTransfers extends Component
{
    use AuthorizesAccountFlow;

    /** top-up | settle | transfer */
    public string $mode = 'transfer';

    public $from_user;

    public $to_user;

    public $user_id;

    public $account_id;

    public $amount;

    public $description;

    public $date;

    /** When true renders only the form (no layout/header). */
    public bool $standalone = false;

    protected $messages = [
        'from_user.different' => 'Pick two different people.',
        'amount.min' => 'The amount must be more than zero.',
    ];

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ManageWallets);

        $this->date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $this->authorizeAccountFlow(Ability::ManageWallets);

        $this->validate();

        try {
            match ($this->mode) {
                // Money leaves a business account and lands in someone's wallet.
                'top-up' => Accountflow::wallets()->topUp(
                    (int) $this->user_id, (float) $this->amount, $this->account_id, $this->description,
                ),
                // Someone returns money to a business account.
                'settle' => Accountflow::wallets()->settle(
                    (int) $this->user_id, (float) $this->amount, $this->account_id, $this->description,
                ),
                // Between two people: the business total is unchanged, so this
                // posts nothing to the ledger.
                default => Accountflow::wallets()->transfer(
                    (int) $this->from_user, (int) $this->to_user, (float) $this->amount, $this->date,
                ),
            };
        } catch (AccountFlowException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        session()->flash('success', 'Saved.');

        $this->reset(['amount', 'description', 'from_user', 'to_user', 'user_id']);
        $this->date = now()->format('Y-m-d');
    }

    public function render(): View
    {
        $userModel = config('auth.providers.users.model', 'App\Models\User');
        $title = 'Wallet Transfer | '.config('accountflow.business_name');

        $view = view(config('accountflow.view_path').'livewire.wallets.create-user-wallet-transfers', [
            'accounts' => Account::query()->active()->orderBy('name')->get(),
            'users' => $userModel::query()->orderBy('name')->get(['id', 'name']),
        ]);

        if (! $this->standalone) {
            return $view->extends(config('accountflow.layout'))->section('content')->title($title);
        }

        return $view;
    }

    protected function rules(): array
    {
        return match ($this->mode) {
            'transfer' => [
                'amount' => 'required|numeric|min:0.01',
                'from_user' => 'required|integer|different:to_user',
                'to_user' => 'required|integer',
                'date' => 'required|date',
            ],
            default => [
                'amount' => 'required|numeric|min:0.01',
                'user_id' => 'required|integer',
                'account_id' => 'nullable|integer|exists:accounts,id',
                'date' => 'required|date',
            ],
        };
    }
}
