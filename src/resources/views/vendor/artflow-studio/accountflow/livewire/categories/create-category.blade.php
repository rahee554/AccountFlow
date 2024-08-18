<div>
          @include(config('accountflow.view_path') . '.blades.dashboard-header')
   <div class="px-2 px-md-5 px-lg-10">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header {{ $isEdit ? 'bg-warning text-dark' : 'bg-primary text-white' }}">
                    <h4 class="mb-0">
                        <i class="fas fa-{{ $isEdit ? 'edit' : 'plus-circle' }} me-2"></i>
                        {{ $isEdit ? 'Edit Category' : 'Create New Category' }}
                    </h4>
                </div>
                
                <div class="card-body">
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

                    <form wire:submit.prevent="save" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Category Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    Category Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       wire:model="{{ $isEdit ? 'defer' : '' }}.name" 
                                       placeholder="Enter category name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Flow Type -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">
                                    Flow Type <span class="text-danger">*</span>
                                </label>
                                <select id="type" 
                                        class="form-select @error('type') is-invalid @enderror" 
                                        wire:model.change="type">
                                    <option value="1">Income</option>
                                    <option value="2">Expense</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($isEdit)
                                <div class="form-text text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Changing flow type will reset parent category selection
                                </div>
                                @endif
                            </div>

                            <!-- Parent Category -->
                            <div class="col-md-6 mb-3">
                                <label for="parent_id" class="form-label">Parent Category</label>
                                <select id="parent_id" 
                                        class="form-select @error('parent_id') is-invalid @enderror" 
                                        wire:model="parent_id">
                                    <option value="">Select Parent Category (Optional)</option>
                                    @foreach($parentCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Leave empty to {{ $isEdit ? 'keep as' : 'create a' }} main category. Only 
                                    <span class="badge bg-{{ $type == 1 ? 'success' : 'danger' }}">
                                        {{ $type == 1 ? 'Income' : 'Expense' }}
                                    </span> 
                                    categories are shown.
                                </div>
                            </div>

                            <!-- Icon (Auto-generated, read-only display) -->
                            <div class="col-md-6 mb-3">
                                <label for="icon" class="form-label">Icon File</label>
                                <input type="text" 
                                       id="icon" 
                                       class="form-control @error('icon') is-invalid @enderror" 
                                       wire:model="icon" 
                                       readonly
                                       placeholder="Auto-generated from category name">
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    @if($isEdit && $originalIcon)
                                        Current icon: <code>{{ $originalIcon }}</code>
                                        @if($icon !== $originalIcon)
                                            → Will change to: <code>{{ $icon }}</code>
                                        @endif
                                    @else
                                        Icon filename is auto-generated from category name. 
                                        @if($icon)
                                            Expected file: <code>{{ $icon }}</code>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- SVG Icon Upload -->
                            <div class="col-md-6 mb-3">
                                <label for="iconFile" class="form-label">
                                    {{ $isEdit ? 'Upload New SVG Icon' : 'Upload SVG Icon' }}
                                </label>
                                <input type="file" 
                                       id="iconFile" 
                                       class="form-control @error('iconFile') is-invalid @enderror" 
                                       wire:model="iconFile"
                                       accept=".svg">
                                @error('iconFile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Upload {{ $isEdit ? 'a new' : 'an' }} SVG icon file (max 1MB){{ $isEdit ? ' to replace current icon' : '' }}. 
                                    @if($icon)
                                        Will be saved as: <code>{{ $icon }}</code>
                                    @endif
                                </div>
                                @if($iconFile)
                                    <div class="mt-2">
                                        <small class="text-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Selected: {{ $iconFile->getClientOriginalName() }}
                                        </small>
                                    </div>
                                @endif
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="status" 
                                           wire:model="status" 
                                           {{ $status ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status">
                                        {{ $status ? 'Active' : 'Inactive' }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Progress -->
                        <div wire:loading wire:target="iconFile" class="mb-3">
                            <div class="alert alert-info">
                                <i class="fas fa-spinner fa-spin me-2"></i>Uploading SVG icon...
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Fields marked with <span class="text-danger">*</span> are required
                                </small>
                            </div>
                            <div>
                                @if($isEdit)
                                    <a href="{{ route('accountflow::categories') }}" class="btn btn-secondary me-2" wire:navigate>
                                        <i class="fas fa-arrow-left me-1"></i>Back to List
                                    </a>
                                @else
                                    <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                                        <i class="fas fa-arrow-left me-1"></i>Cancel
                                    </button>
                                @endif
                                <button type="submit" class="btn btn-{{ $isEdit ? 'warning' : 'primary' }}" wire:loading.attr="disabled" wire:target="iconFile">
                                    <span wire:loading.remove wire:target="iconFile">
                                        <i class="fas fa-save me-1"></i>{{ $isEdit ? 'Update Category' : 'Create Category' }}
                                    </span>
                                    <span wire:loading wire:target="iconFile">
                                        <i class="fas fa-spinner fa-spin me-1"></i>Uploading...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Category Preview -->
            @if($name)
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-eye me-2"></i>{{ $isEdit ? 'Updated Category Preview' : 'Category Preview' }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            @if($iconFile)
                                <div class="icon-preview bg-light p-3 rounded text-center border" style="width: 60px; height: 60px;">
                                    <small class="text-success">{{ $isEdit ? 'New SVG' : 'SVG' }}<br>Uploaded</small>
                                </div>
                            @elseif($icon)
                                <div class="icon-preview bg-light p-3 rounded text-center border" style="width: 60px; height: 60px;">
                                    <small class="text-muted">{{ $icon }}</small>
                                </div>
                            @else
                                <i class="fas fa-folder fa-2x text-muted"></i>
                            @endif
                        </div>
                        <div>
                            <h5 class="mb-1">{{ $name }}</h5>
                            <div class="d-flex gap-2">
                                <span class="badge bg-{{ $type == 1 ? 'success' : 'danger' }}">
                                    {{ $type == 1 ? 'Income' : 'Expense' }}
                                </span>
                                <span class="badge bg-{{ $status ? 'success' : 'secondary' }}">
                                    {{ $status ? 'Active' : 'Inactive' }}
                                </span>
                                @if($parent_id)
                                    @php
                                        $parentName = $parentCategories->firstWhere('id', $parent_id)->name ?? 'Unknown';
                                    @endphp
                                    <span class="badge bg-light text-dark">
                                        Child of: {{ $parentName }}
                                    </span>
                                @endif
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-folder-open me-1"></i>
                                    Icon {{ $isEdit ? 'path' : 'will be saved to' }}: <code>public/vendor/artflow-studio/accountflow/assets/icons/accounts_icons/{{ $icon }}</code>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div></div>
