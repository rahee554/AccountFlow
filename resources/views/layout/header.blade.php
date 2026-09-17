{{--
    Topbar — ported from ui-flow's laravel-starter header.blade.php. Kept
    every piece wired to real theme JS (drawer, sidebar collapse, command
    palette, theme toggle, customizer, user menu); the demo-only "quick
    create"/"app launcher"/"notifications" panels from the theme's own
    topbar were left out.
--}}
@php
    $ufUser = auth()->user();
    $ufInitial = $ufUser?->name ? mb_strtoupper(mb_substr($ufUser->name, 0, 1)) : 'U';
@endphp
<header class="app-topbar">
    <button type="button" class="topbar-btn d-lg-none" data-drawer-toggle aria-label="Open navigation">
        <i data-lucide="menu"></i>
    </button>

    <button type="button" class="topbar-btn d-none d-lg-inline-flex" data-sidebar-collapse
        data-bs-toggle="tooltip" title="Collapse sidebar" aria-label="Collapse sidebar">
        <i data-lucide="panel-left"></i>
    </button>

    <a href="{{ \Illuminate\Support\Facades\Route::has('accountflow::dashboard') ? route('accountflow::dashboard') : '#' }}"
        class="brand topbar-brand" @if (\Illuminate\Support\Facades\Route::has('accountflow::dashboard')) wire:navigate @endif>
        <span class="brand-mark" aria-hidden="true"><i data-lucide="zap"></i></span>
        <span class="brand-text sidebar-brand-text">{{ config('accountflow.business_name', 'AccountFlow') }}</span>
    </a>

    <div class="topbar-search d-none d-md-block">
        <span class="topbar-search-icon"><i data-lucide="search"></i></span>
        <input type="search" class="form-control" placeholder="Search…" aria-label="Search" data-palette-open
            readonly>
        <kbd class="topbar-search-kbd">/</kbd>
    </div>

    <div class="topbar-actions">
        <button type="button" class="topbar-btn d-md-none" data-palette-open aria-label="Search">
            <i data-lucide="search"></i>
        </button>

        <button type="button" class="topbar-btn d-none d-md-inline-flex" data-fullscreen-toggle
            data-bs-toggle="tooltip" title="Toggle fullscreen" aria-label="Toggle fullscreen">
            <i data-lucide="maximize" class="fullscreen-icon-off"></i>
            <i data-lucide="minimize" class="fullscreen-icon-on"></i>
        </button>

        <button type="button" class="topbar-btn" data-theme-toggle data-bs-toggle="tooltip" title="Toggle theme"
            aria-label="Toggle colour theme">
            <i data-lucide="moon" class="theme-icon-light"></i>
            <i data-lucide="sun" class="theme-icon-dark"></i>
        </button>

        <button type="button" class="topbar-btn d-none d-lg-inline-flex" data-bs-toggle="offcanvas"
            data-bs-target="#customizer" aria-label="Open theme customizer">
            <i data-lucide="sliders"></i>
        </button>

        <span class="topbar-divider d-none d-sm-block"></span>

        <div class="dropdown topbar-user-slot">
            <button type="button" class="topbar-user" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar avatar-sm avatar-primary">{{ $ufInitial }}</span>
                <span class="topbar-user-meta">
                    <span class="name">{{ $ufUser?->name ?? 'Guest' }}</span>
                    <span class="role">{{ config('accountflow.business_name', 'AccountFlow') }}</span>
                </span>
                <i data-lucide="chevron-down" class="topbar-user-caret"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end min-w-200px">
                @if ($ufUser?->email)
                    <li class="dropdown-header">Signed in as {{ $ufUser->email }}</li>
                @endif
                <li>
                    <a class="dropdown-item"
                        href="{{ \Illuminate\Support\Facades\Route::has('accountflow::settings') ? route('accountflow::settings') : '#' }}">
                        <i data-lucide="settings"></i>Account settings
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button type="button" class="dropdown-item" data-palette-open>
                        <i data-lucide="command"></i>Command palette
                        <span class="dropdown-item-hint">⌘K</span>
                    </button>
                </li>
                @if (\Illuminate\Support\Facades\Route::has('logout'))
                    <li><hr class="dropdown-divider"></li>
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
</header>
