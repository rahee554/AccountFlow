<?php

namespace App\Livewire\AccountFlow;

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

    public $isAdmin = false;

    public $isAdminManagementEnabled = true;

    public function mount()
    {
        // Check if admin management is enabled
        $adminConfig = config('accountflow.admin_management', []);
        $this->isAdminManagementEnabled = $adminConfig['enabled'] ?? true;

        // Check if current user is admin
        $user = auth()->user();
        $this->isAdmin = $this->checkIsAdmin($user, $adminConfig);

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
        // Check if user is admin before allowing toggle
        if (!$this->isAdmin) {
            session()->flash('error', 'Only administrators can modify feature settings.');
            return;
        }

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

    /**
     * Check if user is admin based on config
     */
    private function checkIsAdmin($user, array $adminConfig): bool
    {
        if (!$user) {
            return false;
        }

        $check = $adminConfig['check'] ?? 'isAdmin';

        // If check is callable
        if (is_callable($check)) {
            return $check($user);
        }

        // If check is a string method name
        if (is_string($check) && method_exists($user, $check)) {
            return (bool) $user->{$check}();
        }

        // Check for common admin properties/methods
        if (property_exists($user, 'is_admin') && $user->is_admin) {
            return true;
        }

        if (property_exists($user, 'role') && $user->role === 'admin') {
            return true;
        }

        return false;
    }

    public function render()
    {

        $viewpath = config('accountflow.view_path').'livewire.settings';
        $layout = config('accountflow.layout');
        $title = 'Settings | '.config('accountflow.business_name');

        return view($viewpath, [
            'salesCategories' => $this->salesCategories,
            'accounts' => $this->accounts,
            'expenseCategories' => $this->expenseCategories,
            'settings' => $this->settings,
            'featureSettings' => $this->featureSettings,
            'paymentMethods' => $this->paymentMethods,
            'isAdmin' => $this->isAdmin,
            'isAdminManagementEnabled' => $this->isAdminManagementEnabled,
        ])->extends($layout)->section('content')->title($title);
    }
}
