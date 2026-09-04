<?php

namespace ArtflowStudio\AccountFlow\Livewire;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Enums\SettingType;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Setting;
use ArtflowStudio\AccountFlow\Support\Authorization;
use Illuminate\View\View;
use Livewire\Component;

class Settings extends Component
{
    use AuthorizesAccountFlow;

    /** @var array<string,mixed> */
    public array $settings = [];

    /** @var array<string,string> */
    public array $featureSettings = [];

    /** @var array<int,array<string,mixed>> */
    public array $salesCategories = [];

    /** @var array<int,array<string,mixed>> */
    public array $expenseCategories = [];

    /** @var array<int,array<string,mixed>> */
    public array $accounts = [];

    /** @var array<int,array<string,mixed>> */
    public array $paymentMethods = [];

    /** @var array<int,array<string,mixed>> */
    public array $transactionTypes = [];

    public bool $isAdmin = false;

    public bool $isAdminManagementEnabled = true;

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ManageSettings);

        $this->isAdminManagementEnabled = (bool) config('accountflow.admin_management.enabled', true);
        $this->isAdmin = Authorization::isAdmin();

        foreach (Setting::all() as $setting) {
            if ((int) $setting->type === SettingType::Value->value) {
                $this->settings[$setting->key] = $setting->value;
            } else {
                $this->featureSettings[$setting->key] = $setting->value;
            }
        }

        $this->salesCategories = Category::query()
            ->where('type', 1)->whereNotNull('parent_id')->get()->toArray();
        $this->expenseCategories = Category::query()
            ->where('type', 2)->whereNotNull('parent_id')->get()->toArray();
        $this->accounts = Account::all()->toArray();
        $this->paymentMethods = PaymentMethod::orderBy('name')->get(['id', 'name'])->toArray();

        $this->transactionTypes = [
            ['id' => 1, 'name' => 'Sales'],
            ['id' => 2, 'name' => 'Expense'],
        ];

        $this->settings['default_transaction_type'] ??= 1;
        $this->settings['currency'] ??= config('accountflow.currency', 'PKR');
    }

    public function saveSettings(): void
    {
        $this->authorizeAccountFlow(Ability::ManageSettings);

        foreach ($this->settings as $key => $value) {
            $this->persist($key, (string) $value, SettingType::Value);
        }

        foreach ($this->featureSettings as $key => $value) {
            $enabled = $value === true || $value === 1 || $value === 'enabled' ? 'enabled' : 'disabled';

            $this->persist($key, $enabled, SettingType::Feature);
            $this->featureSettings[$key] = $enabled;
        }

        Setting::flushCache();

        session()->flash('success', 'Settings updated successfully.');
    }

    public function updateSetting(string $key, mixed $value): void
    {
        $this->authorizeAccountFlow(Ability::ManageSettings);

        $this->settings[$key] = $value;
        $this->persist($key, (string) $value, SettingType::Value);

        Setting::flushCache();

        session()->flash('success', 'Setting updated.');
    }

    public function toggleFeature(string $key): void
    {
        // Re-checked here rather than trusting the mounted $isAdmin property:
        // route middleware does not run for Livewire actions.
        $this->authorizeAccountFlow(Ability::ManageFeatures);

        $enabled = ($this->featureSettings[$key] ?? 'disabled') === 'enabled' ? 'disabled' : 'enabled';

        $this->featureSettings[$key] = $enabled;
        $this->persist($key, $enabled, SettingType::Feature);

        Setting::flushCache();

        session()->flash('success', ucfirst(str_replace('_', ' ', $key))." feature {$enabled}.");
    }

    public function render(): View
    {
        return view(config('accountflow.view_path').'livewire.settings', [
            'salesCategories' => $this->salesCategories,
            'accounts' => $this->accounts,
            'expenseCategories' => $this->expenseCategories,
            'settings' => $this->settings,
            'featureSettings' => $this->featureSettings,
            'paymentMethods' => $this->paymentMethods,
            'isAdmin' => $this->isAdmin,
            'isAdminManagementEnabled' => $this->isAdminManagementEnabled,
        ])
            ->extends(config('accountflow.layout'))
            ->section('content')
            ->title('Settings | '.config('accountflow.business_name'));
    }

    /**
     * Write one setting.
     *
     * `ac_settings.key` is UNIQUE, so `key` alone identifies the row. Matching
     * on ['key', 'type'] — as 0.2.x did — missed rows stored under a different
     * type, fell through to an INSERT, and hit the unique index.
     */
    private function persist(string $key, string $value, SettingType $type): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type->value],
        );
    }
}
