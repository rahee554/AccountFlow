<?php

namespace App\Livewire\Accountflow\Assets;

use App\Models\AccountFlow\Asset;
use App\Models\AccountFlow\Category;
use Livewire\Component;

class CreateAsset extends Component
{
    public $name;

    public $value;

    public $category;

    public $status;

    public $date;

    public $description;

    public $categories = [];

    public function mount()
    {
        // Load categories where parent_id is null and type = 2
        $this->categories = Category::whereNotNull('parent_id')->where('type', 2)->get();
    }

    public function storeAsset()
    {
        $this->validate([
            'name' => 'required|string',
            'value' => 'required|numeric',
            'category' => 'nullable|numeric',
            'status' => 'required|in:1,2,3',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $asset = new Asset;
        $asset->name = $this->name;
        $asset->value = $this->value;
        $asset->acquisition_date = $this->date;
        $asset->category_id = $this->category ?? null;
        $asset->status = $this->status;
        $asset->description = $this->description ?? null;
        $asset->save();

        session()->flash('success', 'Asset stored successfully');
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.assets.create-asset';
        $layout = config('accountflow.layout');
        $title = 'Create Asset | '.config('accountflow.business_name');

        return view($viewpath)->extends($layout)->section('content')->title($title);
    }
}
