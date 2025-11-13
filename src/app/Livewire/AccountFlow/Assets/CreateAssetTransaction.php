<?php

namespace App\Livewire\AccountFlow\Assets;

use App\Models\AccountFlow\Asset;
use App\Models\AccountFlow\AssetTransaction;
use App\Models\AccountFlow\Category;
use App\Models\AccountFlow\Setting;
use App\Models\AccountFlow\Transaction;
use Livewire\Component;

class CreateAssetTransaction extends Component
{
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
        $this->assets = Asset::all();
        $this->accounts = \App\Models\AccountFlow\Account::all();

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
        $this->validate();

        // Fetch asset and its category/type
        $asset = Asset::findOrFail($this->asset_id);
        $category_id = $asset->category_id;
        $type = $asset->category->type;

        // Create Transaction
        $transaction = new Transaction([
            'unique_id' => generateUniqueID(Transaction::class, 'unique_id'),
            'account_id' => $this->account_id,
            'category_id' => $category_id,
            'amount' => $this->amount,
            'type' => $type,
            'date' => $this->date,
            'description' => $this->description ?: 'Asset Transaction',
        ]);
        $transaction->save();

        // Link AssetTransaction to Transaction
        AssetTransaction::create([
            'asset_id' => $asset->id,
            'trx_id' => $transaction->id,
        ]);

        session()->flash('success', 'Transaction saved successfully.');

        return redirect()->route('assets.transactions.index');
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
