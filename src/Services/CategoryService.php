<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Models\Category;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * CategoryService - Category Management
 *
 * Handles all category-related operations including:
 * - Creating and updating categories
 * - Category hierarchy management (parent/child relationships)
 * - Category status and privacy control
 * - Filtering categories by type (income/expense)
 *
 * @example
 * // Create income category
 * $category = Accountflow::categories()->create([
 *     'name' => 'Product Sales',
 *     'type' => 1, // Income
 *     'parent_id' => null // Main category
 * ]);
 *
 * // Get all income categories
 * $incomeCategories = Accountflow::categories()->getByType(1);
 */
class CategoryService
{
    /**
     * Create a new category
     *
     * @param array{
     *     name: string,
     *     type: int, // 1=income, 2=expense
     *     parent_id?: int|null,
     *     privacy?: int, // 1=locked, 2=unlocked
     *     icon?: string|null,
     *     status?: int // 1=active, 2=inactive
     * } $data
     *
     * @throws Exception
     */
    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            // Validate required fields
            if (empty($data['name'])) {
                throw new Exception('Category name is required');
            }

            if (! isset($data['type']) || ! in_array((int) $data['type'], [1, 2], true)) {
                throw new Exception('Category type must be 1 (income) or 2 (expense)');
            }

            // Validate parent category if provided
            if (! empty($data['parent_id'])) {
                $parent = Category::find($data['parent_id']);
                if (! $parent) {
                    throw new Exception("Parent category #{$data['parent_id']} not found");
                }
                // Parent category must have same type
                if ($parent->type !== (int) $data['type']) {
                    throw new Exception('Parent category must have same type as child');
                }
            }

            // Prepare category data
            $categoryData = [
                'name' => trim($data['name']),
                'type' => (int) $data['type'],
                'parent_id' => (int) ($data['parent_id'] ?? null) ?: null,
                'privacy' => (int) ($data['privacy'] ?? 1), // 1 = locked by default
                'icon' => $data['icon'] ?? null,
                'status' => (int) ($data['status'] ?? 1), // 1 = active by default
                'added_by' => auth()->id(),
            ];

            return Category::create($categoryData);
        });
    }

    /**
     * Update a category
     */
    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = trim($data['name']);
            }

            if (isset($data['privacy'])) {
                $updateData['privacy'] = (int) $data['privacy'];
            }

            if (isset($data['icon'])) {
                $updateData['icon'] = $data['icon'];
            }

            if (isset($data['status'])) {
                $updateData['status'] = (int) $data['status'];
            }

            if (! empty($updateData)) {
                $category->update($updateData);
            }

            return $category->fresh();
        });
    }

    /**
     * Get all categories by type
     *
     * @param int $type 1=income, 2=expense
     */
    public function getByType(int $type, bool $onlyActive = true): Collection
    {
        $query = Category::where('type', $type);

        if ($onlyActive) {
            $query->where('status', 1);
        }

        return $query->whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all categories
     */
    public function getAll(bool $onlyActive = true): Collection
    {
        $query = Category::query();

        if ($onlyActive) {
            $query->where('status', 1);
        }

        return $query->orderBy('type')
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get income categories
     */
    public function getIncomeCategories(bool $onlyActive = true): Collection
    {
        return $this->getByType(1, $onlyActive);
    }

    /**
     * Get expense categories
     */
    public function getExpenseCategories(bool $onlyActive = true): Collection
    {
        return $this->getByType(2, $onlyActive);
    }

    /**
     * Get category with children
     *
     *
     *
     * @throws Exception
     */
    public function getWithChildren(int $categoryId): Category
    {
        $category = Category::with('children')->find($categoryId);

        if (! $category) {
            throw new Exception("Category #{$categoryId} not found");
        }

        return $category;
    }

    /**
     * Get category hierarchy
     */
    public function getHierarchy(int $type): array
    {
        $categories = Category::where('type', $type)
            ->where('status', 1)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        return $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'children' => $category->children->map(fn ($child) => [
                    'id' => $child->id,
                    'name' => $child->name,
                ])->toArray(),
            ];
        })->toArray();
    }

    /**
     * Deactivate a category
     */
    public function deactivate(Category $category): Category
    {
        return $this->update($category, ['status' => 2]);
    }

    /**
     * Activate a category
     */
    public function activate(Category $category): Category
    {
        return $this->update($category, ['status' => 1]);
    }

    /**
     * Lock a category (prevent user modification)
     */
    public function lock(Category $category): Category
    {
        return $this->update($category, ['privacy' => 1]);
    }

    /**
     * Unlock a category (allow user modification)
     */
    public function unlock(Category $category): Category
    {
        return $this->update($category, ['privacy' => 2]);
    }

    /**
     * Delete a category
     * Only if it has no transactions and is not locked
     *
     *
     *
     * @throws Exception
     */
    public function delete(Category $category): bool
    {
        return DB::transaction(function () use ($category) {
            // Check if locked
            if ($category->privacy === 1) {
                throw new Exception('Cannot delete locked category');
            }

            // Check if has transactions
            if ($category->transactions()->exists()) {
                throw new Exception('Cannot delete category with existing transactions');
            }

            // Check if has child categories
            if ($category->children()->exists()) {
                throw new Exception('Cannot delete category with child categories');
            }

            return $category->delete();
        });
    }
}
