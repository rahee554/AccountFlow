<?php

namespace ArtflowStudio\AccountFlow\Database\Factories;

use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'unique_id' => strtoupper($this->faker->unique()->bothify('??####')),
            'amount' => $this->faker->randomFloat(2, 10, 5000),
            'type' => TransactionType::Expense->value,
            'account_id' => Account::factory(),
            'category_id' => Category::factory(),
            'payment_method' => PaymentMethod::factory(),
            'date' => now()->toDateString(),
            'description' => $this->faker->sentence(),
        ];
    }

    public function income(): static
    {
        return $this->state(fn (): array => ['type' => TransactionType::Income->value]);
    }

    public function expense(): static
    {
        return $this->state(fn (): array => ['type' => TransactionType::Expense->value]);
    }

    public function withoutPaymentMethod(): static
    {
        return $this->state(fn (): array => ['payment_method' => null]);
    }
}
