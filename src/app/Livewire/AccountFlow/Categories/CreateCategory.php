<?php

namespace App\Livewire\Accountflow\Categories;

use App\Models\AccountFlow\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateCategory extends Component
{
    use WithFileUploads;

    public $categoryId; // For edit mode

    public $name;

    public $type = 1; // Default to income

    public $parent_id;

    public $privacy = 2; // Default privacy set to 2

    public $icon;

    public $iconFile; // New property for SVG file upload

    public $originalIcon; // Store original icon for comparison

    public $status = 1; // Default to active

    public $isEdit = false; // Track if we're in edit mode

    public $parentCategories = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:1,2',
        'parent_id' => 'nullable|exists:ac_categories,id',
        'icon' => 'nullable|string|max:100',
        'iconFile' => 'nullable|file|mimes:svg|max:1024', // Only SVG files, max 1MB
        'status' => 'required|boolean',
    ];

    protected $messages = [
        'name.required' => 'Category name is required',
        'type.required' => 'Flow type is required',
        'type.in' => 'Flow type must be either Income or Expense',
        'parent_id.exists' => 'Selected parent category does not exist',
        'iconFile.mimes' => 'Icon must be an SVG file',
        'iconFile.max' => 'Icon file size must be less than 1MB',
    ];

    public function mount($id = null)
    {
        if ($id) {
            // Decode the base64-encoded id if needed
            if (! is_numeric($id)) {
                $id = base64_decode($id);
            }
        }
        if ($id) {
            $this->isEdit = true;
            $this->categoryId = $id;
            $category = Category::findOrFail($id);

            $this->name = $category->name;
            $this->type = $category->type;
            $this->parent_id = $category->parent_id;
            $this->privacy = $category->privacy;
            $this->icon = $category->icon;
            $this->originalIcon = $category->icon;
            $this->status = $category->status;
        }

        $this->loadParentCategories();
    }

    public function updatedName($value)
    {
        // Auto-generate icon from name slug only if no custom icon was uploaded
        if ($value && ! $this->iconFile) {
            $this->icon = Str::slug($value).'.svg';
        } else {
            $this->icon = '';
        }
    }

    public function updatedFlowType()
    {
        $this->parent_id = null; // Reset parent selection when flow type changes
        $this->loadParentCategories();
    }

    private function loadParentCategories()
    {
        $query = Category::where('parent_id', null)
            ->where('type', $this->type)
            ->where('status', 1);

        // Exclude current category from parent options when editing
        if ($this->isEdit && $this->categoryId) {
            $query->where('id', '!=', $this->categoryId);
        }

        $this->parentCategories = $query->orderBy('name')->get();
    }

    public function save()
    {
        // Add custom validation for edit mode to prevent circular reference
        if ($this->isEdit && $this->parent_id) {
            $this->validate([
                'parent_id' => [
                    'nullable',
                    'exists:ac_categories,id',
                    function ($attribute, $value, $fail) {
                        if ($value == $this->categoryId) {
                            $fail('A category cannot be its own parent.');
                        }

                        // Check if selected parent is a child of current category
                        $selectedParent = Category::find($value);
                        if ($selectedParent && $selectedParent->parent_id == $this->categoryId) {
                            $fail('Cannot select a child category as parent.');
                        }
                    },
                ],
            ]);
        }

        $this->validate();

        try {
            // Handle SVG file upload
            if ($this->iconFile) {
                $iconPath = $this->uploadSvgIcon();
                if (! $iconPath) {
                    throw new \Exception('Failed to upload SVG icon');
                }
                // Update icon field with the filename
                $this->icon = basename($iconPath);
            }

            if ($this->isEdit) {
                // Update existing category
                $category = Category::findOrFail($this->categoryId);
                $category->update([
                    'name' => $this->name,
                    'type' => $this->type,
                    'parent_id' => $this->parent_id,
                    'privacy' => $this->privacy,
                    'icon' => $this->icon,
                    'status' => $this->status,
                ]);

                session()->flash('success', 'Category updated successfully!');

                return redirect()->route('accountflow::categories');
            } else {
                // Create new category
                Category::create([
                    'name' => $this->name,
                    'type' => $this->type,
                    'parent_id' => $this->parent_id,
                    'privacy' => $this->privacy, // Will always be 2
                    'icon' => $this->icon,
                    'status' => $this->status,
                    'added_by' => Auth::id(),
                ]);

                session()->flash('success', 'Category created successfully!');

                // Reset form
                $this->reset(['name', 'parent_id', 'icon', 'iconFile']);
                $this->type = 1;
                $this->privacy = 2;
                $this->status = 1;

                // Reload parent categories
                $this->loadParentCategories();
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error '.($this->isEdit ? 'updating' : 'creating').' category: '.$e->getMessage());
        }
    }

    private function uploadSvgIcon()
    {
        try {
            // Create the directory path
            $iconDirectory = 'vendor/artflow-studio/accountflow/assets/icons/accounts_icons';
            $fullPath = public_path($iconDirectory);

            // Create directory if it doesn't exist
            if (! file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            // Generate filename from category name
            $filename = Str::slug($this->name).'.svg';
            $filePath = $iconDirectory.'/'.$filename;
            $fullFilePath = public_path($filePath);

            // Get file contents
            $fileContents = file_get_contents($this->iconFile->getRealPath());

            // Basic SVG validation
            if (strpos($fileContents, '<svg') === false) {
                throw new \Exception('Invalid SVG file format');
            }

            // Save the file
            if (file_put_contents($fullFilePath, $fileContents) === false) {
                throw new \Exception('Failed to save SVG file');
            }

            return $filePath;
        } catch (\Exception $e) {
            logger()->error('SVG upload failed: '.$e->getMessage());

            return false;
        }
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path');
        $layout = config('accountflow.layout');

        return view($viewpath.'livewire.categories.create-category')->extends($layout)->section('content');
    }
}

