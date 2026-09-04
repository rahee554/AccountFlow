<?php

namespace ArtflowStudio\AccountFlow\Database\Factories;

use ArtflowStudio\AccountFlow\Enums\CategoryType;
use ArtflowStudio\AccountFlow\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'type' => CategoryType::Expense->value,
            'parent_id' => null,
            'privacy' => 1,
            'status' => 1,
            'icon' => null,
        ];
    }

    public function income(): static
    {
        return $this->state(fn (): array => ['type' => CategoryType::Income->value]);
    }

    public function expense(): static
    {
        return $this->state(fn (): array => ['type' => CategoryType::Expense->value]);
    }
}
