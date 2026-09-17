{{--
    AccountFlow admin shell — ui-flow theme.

    Mirrors the theme's own shell DOM order exactly: sidebar, topbar, main,
    footer, all inside one `.app` wrapper. Don't reorder these — the theme's
    layout SCSS depends on this exact structure.

    Every AccountFlow Livewire component already renders via
    `->extends($layout)->section('content')`, which is the classic
    @extends/@section idiom — this layout supports that directly via
    @yield('content'). It also supports the component-slot idiom
    (`<x-accountflow::layout.app>...</x-accountflow::layout.app>`) for any
    future classic Blade view, so nothing already written needs to change.

    @persist keeps the sidebar/topbar/footer DOM alive across a Livewire
    wire:navigate between AccountFlow pages — without it every navigation
    would tear down and rebuild the whole shell.
--}}
@php
    $accent ??= null;
    $lockAccent ??= false;
    $theme ??= null;
    $lockTheme ??= false;
    $customizer ??= true;
    $pageLoader ??= null;
    $pageLoaderLabel ??= 'Loading';

    $locked = array_keys(array_filter([
        'accent' => $accent && $lockAccent,
        'theme' => $theme && $lockTheme,
    ]));

    $viewPath = config('accountflow.view_path');
@endphp
<!doctype html>
<html lang="en"
    @if ($locked) data-lock="{{ implode(' ', $locked) }}" @endif
    @if ($accent) data-accent="{{ $accent }}" @endif
    @if ($theme) data-bs-theme="{{ $theme }}" @endif
    @unless ($customizer) data-customizer="false" @endunless>

@include($viewPath . 'layout.head')

<body>

    @if ($pageLoader)
        <div class="page-loader @if ($pageLoader !== 'branded') page-loader-{{ $pageLoader }} @endif"
            data-page-loader data-loader-min="250" role="status" aria-live="polite"
            aria-label="{{ $pageLoaderLabel }}">
            @if ($pageLoader === 'branded')
                <div class="loader-logo">
                    <span class="brand-mark" aria-hidden="true"><i data-lucide="zap"></i></span>
                </div>
            @else
                <div class="loader loader-ring loader-lg" aria-hidden="true"></div>
            @endif
            <p class="page-loader-label mb-0">{{ $pageLoaderLabel }}<span class="loading-dots"></span></p>
        </div>
    @endif

    <a class="skip-link" href="#main-content">Skip to content</a>

    <div class="app">
        @persist('accountflow-sidebar')
            @include($viewPath . 'layout.menu')
        @endpersist

        @persist('accountflow-topbar')
            @include($viewPath . 'layout.header')
        @endpersist

        <main class="app-main" id="main-content">
            <div class="app-content">
                <div class="app-container">
                    @if (trim($slot ?? ''))
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endif
                </div>
            </div>
        </main>

        @persist('accountflow-footer')
            @include($viewPath . 'layout.footer')
        @endpersist
    </div>

    @if ($customizer)
        @include($viewPath . 'layout.customizer')
    @endif
    @include($viewPath . 'layout.palette')

    <button type="button" class="scroll-top" aria-label="Back to top">
        <i data-lucide="arrow-up"></i>
    </button>

    @include($viewPath . 'layout.scripts')
</body>

</html>
