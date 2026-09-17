{{--
    The sidebar's MARKUP. It renders whatever $sections it is handed and knows
    nothing about where that array came from — menu.blade.php owns the data.
--}}
@php
    $ufUser = auth()->user();
    $ufInitial = $ufUser?->name ? mb_strtoupper(mb_substr($ufUser->name, 0, 1)) : 'U';
@endphp
<aside class="app-sidebar" id="app-sidebar" aria-label="Main navigation">
    <div class="sidebar-rail" role="tablist" aria-label="Navigation sections">
        @foreach ($sections as $section)
            <button type="button" class="rail-item" role="tab" data-rail-target="{{ $section['key'] }}"
                aria-selected="false" data-bs-toggle="tooltip" data-bs-placement="right" title="{{ $section['label'] }}">
                <i data-lucide="{{ $section['icon'] }}"></i>
                <span class="visually-hidden">{{ $section['label'] }}</span>
            </button>
        @endforeach
    </div>

    <div class="sidebar-main">
        <div class="sidebar-header">
            <a href="{{ \Illuminate\Support\Facades\Route::has('accountflow::dashboard') ? route('accountflow::dashboard') : '#' }}"
                class="brand" @if (\Illuminate\Support\Facades\Route::has('accountflow::dashboard')) wire:navigate @endif>
                <span class="brand-mark" aria-hidden="true"><i data-lucide="zap"></i></span>
                <span class="brand-text sidebar-brand-text">{{ config('accountflow.business_name', 'AccountFlow') }}</span>
            </a>
        </div>

        <div class="sidebar-body">
            <nav class="sidebar-nav">
                @foreach ($sections as $section)
                    <div class="nav-section panel-group" data-panel-group="{{ $section['key'] }}">
                        <div class="nav-heading">{{ $section['label'] }}</div>
                        <div class="panel-title">{{ $section['label'] }}</div>
                        <ul class="nav-list">
                            @foreach ($section['items'] as $item)
                                @include(config('accountflow.view_path') . 'layout.menu-item', ['item' => $item, 'depth' => 0])
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
        </div>

        <div class="sidebar-user">
            <div class="dropdown dropup">
                <button type="button" class="topbar-user" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar avatar-sm avatar-primary">{{ $ufInitial }}</span>
                    <span class="topbar-user-meta">
                        <span class="name">{{ $ufUser?->name ?? 'Guest' }}</span>
                        <span class="role">{{ config('accountflow.business_name', 'AccountFlow') }}</span>
                    </span>
                    <i data-lucide="chevron-down" class="topbar-user-caret"></i>
                </button>
                <ul class="dropdown-menu min-w-200px">
                    <li>
                        <a class="dropdown-item"
                            href="{{ \Illuminate\Support\Facades\Route::has('accountflow::settings') ? route('accountflow::settings') : '#' }}">
                            <i data-lucide="settings"></i>Account settings
                        </a>
                    </li>
                    @if (\Illuminate\Support\Facades\Route::has('logout'))
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item dropdown-item-danger">
                                    <i data-lucide="log-out"></i>Sign out
                                </button>
                            </form>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="sidebar-footer">
            <a href="{{ \Illuminate\Support\Facades\Route::has('accountflow::dashboard') ? route('accountflow::dashboard') : url('/') }}"
                class="btn btn-soft-primary btn-sm w-100">
                <i data-lucide="arrow-left"></i>
                <span class="nav-text">Back to app</span>
            </a>
        </div>
    </div>
</aside>

<div class="app-backdrop" aria-hidden="true"></div>
