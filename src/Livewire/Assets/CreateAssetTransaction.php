<?php

namespace ArtflowStudio\AccountFlow\Livewire\Assets;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Facades\Accountflow;
use ArtflowStudio\AccountFlow\Models\Asset;
use ArtflowStudio\AccountFlow\Models\Setting;
use ArtflowStudio\AccountFlow\Models\Transaction;
use Livewire\Component;

class CreateAssetTransaction extends Component
{
    use AuthorizesAccountFlow;

    public $assets;

    public $accounts;

    public $account_id;

    public $asset_id;

    public $date;

    public $amount;

    public $description;

    public $transaction_id; // For editing

    protected $rules = [
        'account_id' => 'required|exists:accounts,id',
        'asset_id' => 'required|exists:assets,id',
        'date' => 'required|date',
        'amount' => 'required|numeric',
        'description' => 'nullable|string',
    ];

    public function mount($transaction = null)
    {
        $this->authorizeAccountFlow(Ability::ManageAssets);
        $this->assets = Asset::all();
        $this->accounts = \ArtflowStudio\AccountFlow\Models\Account::all();

        // Get default account from Setting
        $defaultAccount = Setting::where('key', 'default_account_id')->first();
        $this->account_id = $defaultAccount ? $defaultAccount->value : null;

        if ($transaction) {
            $this->transaction_id = $transaction->id;
            $this->account_id = $transaction->account_id;
            $this->asset_id = $transaction->asset_id;
            $this->date = $transaction->date;
            $this->amount = $transaction->amount;
            $this->description = $transaction->description;
        }
    }

    public function save()
    {
        $this->authorizeAccountFlow(Ability::ManageAssets);

        $this->validate();

        // Routed through AssetService so the posting goes via
        // TransactionService — the only write path that moves the account
        // balance. This screen used to call `new Transaction(...)->save()`
        // directly, so buying an asset recorded the purchase and left the cash
        // sitting in the account.
        try {
            Accountflow::assets()->purchase((int) $this->asset_id, (float) $this->amount, [
                'account_id' => $this->account_id,
                'date' => $this->date,
                'description' => $this->description ?: 'Asset Transaction',
            ]);
        } catch (AccountFlowException $e) {
            session()->flash('error', $e->getMessage());

            return null;
        }

        session()->flash('success', 'Transaction saved successfully.');

        // The old redirect targeted 'assets.transactions.index', which is not a
        // registered route name — it threw RouteNotFoundException after saving.
        return redirect()->route('accountflow::assets.transactions');
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.assets.create-asset-transaction';
        $layout = config('accountflow.layout');
        $title = 'Create Asset Transaction | '.config('accountflow.business_name');

        return view($viewpath, [
            'assets' => $this->assets,
        ])->extends($layout)->section('content')->title($title);
    }
}
