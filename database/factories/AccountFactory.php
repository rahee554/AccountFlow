<?php

namespace ArtflowStudio\AccountFlow\Database\Factories;

use ArtflowStudio\AccountFlow\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company().' Account',
            'description' => $this->faker->sentence(),
            'active' => true,
            'opening_balance' => 0,
            'balance' => 0,
        ];
    }

    public function withOpeningBalance(float $amount): static
    {
        return $this->state(fn (): array => [
            'opening_balance' => $amount,
            'balance' => $amount,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['active' => false]);
    }
}
