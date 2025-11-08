<?php

namespace App\Livewire\Accountflow;

use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Category;
use App\Models\AccountFlow\PaymentMethod;
use App\Models\AccountFlow\Setting;
use Livewire\Component;

class Settings extends Component
{
    public $settings = [];

    public $featureSettings = [];

    public $salesCategories = [];

    public $expenseCategories = [];

    public $accounts = [];
    public $paymentMethods = [];
    public $transactionTypes = [];

    public function mount()
    {
        // Load all settings
        $allSettings = Setting::all();

        // Separate type 2 (value) and type 1 (feature toggle)
        foreach ($allSettings as $setting) {
            if ($setting->type == 2) {
                $this->settings[$setting->key] = $setting->value;
            } elseif ($setting->type == 1) {
                $this->featureSettings[$setting->key] = $setting->value;
            }
        }

        // Load categories and accounts (only categories with parent_id not null)
        $this->salesCategories = Category::where('type', 1)->whereNotNull('parent_id')->get()->toArray();
        $this->expenseCategories = Category::where('type', 2)->whereNotNull('parent_id')->get()->toArray();
        $this->accounts = Account::all()->toArray();
        // Load payment methods for default selection
        $this->paymentMethods = PaymentMethod::orderBy('name')->get(['id', 'name'])->toArray();
        $this->transactionTypes = [
            ['id' => 1, 'name' => 'Sales'],
            ['id' => 2, 'name' => 'Expense'],
        ];

        if (! isset($this->settings['default_transaction_type'])) {
            $this->settings['default_transaction_type'] = 1;
        }
    }

    public function saveSettings()
    {
        // Save all type 2 settings
        foreach ($this->settings as $key => $value) {
            Setting::updateOrCreate([
                'key' => $key, 'type' => 2,
            ], [
                'value' => $value,
            ]);
        }

        // Save all type 1 settings (feature toggles)
        foreach ($this->featureSettings as $key => $value) {
            // If checkbox is checked, value will be true, otherwise false
            $isEnabled = ($value === true || $value === 'enabled' || $value === 1) ? 'enabled' : 'disabled';
            Setting::updateOrCreate([
                'key' => $key, 'type' => 1,
            ], [
                'value' => $isEnabled,
            ]);
            $this->featureSettings[$key] = $isEnabled;
        }

        session()->flash('success', 'Settings updated successfully.');
    }

    public function updateSetting($key, $value)
    {
        $this->settings[$key] = $value;
        Setting::updateOrCreate([
            'key' => $key, 'type' => 2,
        ], [
            'value' => $value,
        ]);
        session()->flash('success', 'Setting updated.');
    }

    public function toggleFeature($key)
    {
        $current = $this->featureSettings[$key] ?? 'disabled';
        $newValue = $current === 'enabled' ? 'disabled' : 'enabled';
        $this->featureSettings[$key] = $newValue;
        Setting::updateOrCreate([
            'key' => $key, 'type' => 1,
        ], [
            'value' => $newValue,
        ]);
        session()->flash('success', ucfirst($key).' feature '.($newValue === 'enabled' ? 'enabled' : 'disabled').'.');
    }

    public function render()
    {

        $viewpath = config('accountflow.view_path').'livewire.settings';

        return view($viewpath, [
            'salesCategories' => $this->salesCategories,
            'accounts' => $this->accounts,
            'expenseCategories' => $this->expenseCategories,
            'settings' => $this->settings,
            'featureSettings' => $this->featureSettings,
            'paymentMethods' => $this->paymentMethods,
        ])->extends(config('accountflow.layout'))->section('content');
    }
}

