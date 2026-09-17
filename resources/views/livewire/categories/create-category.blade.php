<div>
        @unless($standalone)
            <div class="page-header">
                <div class="page-header-body">
                    <nav class="page-breadcrumb" aria-label="Breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('accountflow::categories') }}" wire:navigate>Categories</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'Add' }}</li>
                        </ol>
                    </nav>
                    <div class="page-header-title"><h1>{{ $isEdit ? 'Edit Category' : 'Add Category' }}</h1></div>
                </div>
                <div class="page-header-actions">
                    <a href="{{ route('accountflow::categories') }}" class="btn btn-soft-secondary" wire:navigate>
                        <i data-lucide="arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        @endunless

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <form wire:submit.prevent="save" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" id="name" class="form-control @error('name') is-invalid @enderror"
                                        wire:model="name" placeholder="Enter category name">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="type" class="form-label">Flow Type <span class="text-danger">*</span></label>
                                    <div class="nav nav-segmented w-100">
                                        <button type="button" class="nav-link flex-fill @if((int) $type === 1) active @endif" wire:click="$set('type', 1)">Income</button>
                                        <button type="button" class="nav-link flex-fill @if((int) $type === 2) active @endif" wire:click="$set('type', 2)">Expense</button>
                                    </div>
                                    @error('type') <div class="text-danger text-size-sm mt-1">{{ $message }}</div> @enderror
                                    @if($isEdit)
                                        <div class="form-text text-warning">
                                            <i data-lucide="triangle-alert"></i> Changing flow type resets the parent category
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="parent_id" class="form-label">Parent Category</label>
                                    <select id="parent_id" class="form-select @error('parent_id') is-invalid @enderror" wire:model="parent_id">
                                        <option value="">None (top-level category)</option>
                                        @foreach($parentCategories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Only {{ $type == 1 ? 'income' : 'expense' }} categories are shown.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="iconFile" class="form-label">{{ $isEdit ? 'Replace Icon (SVG)' : 'Icon (SVG)' }}</label>
                                    <input type="file" id="iconFile" class="form-control @error('iconFile') is-invalid @enderror"
                                        wire:model="iconFile" accept=".svg">
                                    @error('iconFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text" wire:loading.remove wire:target="iconFile">
                                        @if($isEdit && $originalIcon)
                                            Current: <code>{{ $originalIcon }}</code>@if($icon !== $originalIcon) → <code>{{ $icon }}</code>@endif
                                        @elseif($iconFile)
                                            <span class="text-success"><i data-lucide="check"></i> {{ $iconFile->getClientOriginalName() }}</span>
                                        @else
                                            Optional — auto-named from the category name if omitted.
                                        @endif
                                    </div>
                                    <div class="form-text" wire:loading wire:target="iconFile">
                                        <i data-lucide="loader"></i> Uploading…
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <div class="form-check form-switch pt-2">
                                        <input class="form-check-input" type="checkbox" id="status" wire:model="status">
                                        <label class="form-check-label" for="status">{{ $status ? 'Active' : 'Inactive' }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 pt-3 mt-2 border-top">
                                @if($isEdit)
                                    <a href="{{ route('accountflow::categories') }}" class="btn btn-soft-secondary" wire:navigate>Back to List</a>
                                @else
                                    <button type="button" class="btn btn-soft-secondary" onclick="window.history.back()">Cancel</button>
                                @endif
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="iconFile">
                                    <span wire:loading.remove wire:target="iconFile">{{ $isEdit ? 'Update Category' : 'Create Category' }}</span>
                                    <span wire:loading wire:target="iconFile">Uploading…</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h2 class="card-title">Preview</h2></div>
                    <div class="card-body">
                        @if($name)
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="icon-box icon-box-{{ (int) $type === 1 ? 'success' : 'danger' }}">
                                    <i data-lucide="{{ (int) $type === 1 ? 'trending-up' : 'trending-down' }}"></i>
                                </span>
                                <div class="fw-semibold">{{ $name }}</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge badge-soft-{{ (int) $type === 1 ? 'success' : 'danger' }}">{{ (int) $type === 1 ? 'Income' : 'Expense' }}</span>
                                <span class="badge badge-soft-{{ $status ? 'success' : 'secondary' }}">{{ $status ? 'Active' : 'Inactive' }}</span>
                                @if($parent_id)
                                    @php $parentName = $parentCategories->firstWhere('id', $parent_id)->name ?? 'Unknown'; @endphp
                                    <span class="badge badge-soft-secondary">Child of {{ $parentName }}</span>
                                @endif
                            </div>
                        @else
                            <p class="text-body-secondary text-size-sm mb-0">Enter a name to see a preview here.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
</div>
