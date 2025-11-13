<?php

namespace App\Livewire\AccountFlow\PlannedPayments;

use App\Models\Accountflow\Account;
use App\Models\Accountflow\Category;
use App\Models\Accountflow\PlannedPayment;
use Livewire\Component;

class CreatePlannedPayment extends Component
{
    public $accounts = [];

    public $account_id;

    public $categories = [];

    public $name;

    public $category_id;

    public $amount;

    public $due_date;

    public $period;

    public $auto_post = true;

    public $recurring = false;

    // New schedule fields
    public $schedule_type = 'monthly'; // daily, weekly, monthly

    public $weekly_days = []; // array of weekdays (0=Sun..6=Sat)

    public $monthly_day = 5; // default 5th of month

    public $auto_post_date; // date to auto post when auto_post is enabled

    public $description;

    public function mount()
    {
        $this->accounts = Account::orderBy('name')->get();
        $this->account_id = $this->accounts->first()->id ?? null;
        $this->categories = Category::orderBy('name')->get();
        $this->category_id = $this->categories->first()->id ?? null;
        $this->auto_post = true;
        $this->recurring = false;
    }

    protected function rules()
    {
        return [
            'account_id' => 'required|exists:accounts,id',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:ac_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'due_date' => 'required_if:recurring,false|nullable|date|after_or_equal:today',
            'period' => 'nullable|in:1,2,3,4',
            'schedule_type' => 'nullable|in:daily,weekly,monthly',
            'weekly_days' => 'nullable|array',
            'weekly_days.*' => 'integer|min:0|max:6',
            'monthly_day' => 'nullable|integer|min:1|max:31',
            'auto_post_date' => 'nullable|date',
            'auto_post' => 'boolean',
            'recurring' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function save()
    {
        $this->validate();

        PlannedPayment::create([
            'account_id' => $this->account_id,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'amount' => $this->amount,
            'due_date' => $this->due_date,
            'period' => $this->period,
            'schedule_type' => $this->schedule_type,
            'weekly_days' => $this->weekly_days ?: null,
            'monthly_day' => $this->monthly_day,
            'auto_post_date' => $this->auto_post_date,
            'auto_post' => $this->auto_post,
            'recurring' => $this->recurring,
            'description' => $this->description,
            // 'trx_id' => null, // not used in form
        ]);

        $this->reset(['account_id', 'name', 'category_id', 'amount', 'due_date', 'period', 'auto_post', 'recurring', 'description', 'schedule_type', 'weekly_days', 'monthly_day', 'auto_post_date']);
        $this->account_id = $this->accounts->first()->id ?? null;
        $this->auto_post = true;
        $this->recurring = false;
        $this->schedule_type = 'monthly';
        $this->monthly_day = 5;
        session()->flash('success', 'Planned payment saved successfully!');
    }

    public function getShowPeriodProperty()
    {
        return (bool) $this->recurring;
    }

    public function getShowDueDateProperty()
    {
        return ! $this->recurring;
    }

    public function updatedRecurring($value)
    {
        if ($value) {
            $this->due_date = null;
            // ensure defaults for recurring
            $this->schedule_type = $this->schedule_type ?: 'monthly';
            $this->monthly_day = $this->monthly_day ?: 5;
        } else {
            $this->period = null;
        }
    }

    public function updatedPeriod($value)
    {
        if ($this->recurring && $value && ! $this->due_date) {
            // Set due_date to next occurrence based on period
            $now = now();
            switch ($value) {
                case '1': // Monthly
                    $this->due_date = $now->copy()->addMonth()->startOfMonth()->format('Y-m-d');
                    break;
                case '2': // Quarterly
                    $this->due_date = $now->copy()->addMonths(3)->startOfMonth()->format('Y-m-d');
                    break;
                case '3': // Half-Yearly
                    $this->due_date = $now->copy()->addMonths(6)->startOfMonth()->format('Y-m-d');
                    break;
                case '4': // Annually
                    $this->due_date = $now->copy()->addYear()->startOfMonth()->format('Y-m-d');
                    break;
            }
        }
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.planned-payments.create-planned-payment')->extends($layout)->section('content');
    }
}
