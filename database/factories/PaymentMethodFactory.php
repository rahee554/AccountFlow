<?php

namespace ArtflowStudio\AccountFlow\Database\Factories;

use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word().' Pay',
            'account_id' => Account::factory(),
            'status' => 1,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['status' => 2]);
    }
}
