{{--
    <head> — the boot script below is copied verbatim from the ui-flow theme
    (core/partials/shell/head.hbs, ported via ui-flow's own laravel-starter).
    It must stay: (1) inline, not in a bundled file — it has to run before
    the module graph exists; (2) ABOVE the stylesheet link — it sets
    data-bs-theme/data-accent/etc. on <html> before first paint, so a
    dark-mode visitor never sees a white flash. Moving either breaks it
    silently, no error, just a flash on every load.

    The body is a NAMED function, window.ufApplyStoredSettings, rather than a
    bare IIFE, so layout/scripts.blade.php can call it again after a
    wire:navigate (wire:navigate replaces <html>'s attributes with the
    server-rendered set on every navigation, wiping the visitor's stored
    theme/density/radius/sidebar-skin choices — this is what puts them back).
--}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="color-scheme" content="light dark">
    <meta name="description" content="{{ config('accountflow.business_name', 'AccountFlow') }} — accounting.">
    <title>@yield('title', $title ?? 'Dashboard') · {{ config('accountflow.business_name', 'AccountFlow') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">

    <script>
        (function() {
            var DEFAULTS = {
                theme: 'light', layout: 'vertical', accent: 'sky', gray: 'cool', radius: 'default',
                density: 'comfortable', font: 'inter', icons: 'line', sidebar: 'light',
                sidebarLogo: 'full', topbar: 'light', shadow: 'soft', shell: 'docked', bg: 'plain',
                contentWidth: 'fluid', chromeWidth: 'fluid', scrollbar: 'overlay', mobileNav: 'tabbar',
                footer: 'light', footerPosition: 'static', footerVisible: 'visible',
                menubarAttach: 'separate', sidebarDocs: 'visible', userMenu: 'header', footerWidth: 'inset',
            };
            var ATTRS = {
                layout: 'data-layout', accent: 'data-accent', gray: 'data-gray', radius: 'data-radius',
                density: 'data-density', font: 'data-font', icons: 'data-icons', sidebar: 'data-sidebar',
                sidebarLogo: 'data-sidebar-logo', topbar: 'data-topbar', shadow: 'data-shadow',
                shell: 'data-shell', bg: 'data-bg', contentWidth: 'data-content-width',
                chromeWidth: 'data-chrome-width', scrollbar: 'data-scrollbar', mobileNav: 'data-mobile-nav',
                footer: 'data-footer', footerPosition: 'data-footer-position',
                footerVisible: 'data-footer-visible', menubarAttach: 'data-menubar-attach',
                sidebarDocs: 'data-sidebar-docs', userMenu: 'data-user-menu', footerWidth: 'data-footer-width',
            };
            var NEUTRAL = {
                'default': 1, 'docked': 1, 'plain': 1, 'inherit': 1, '': 1, 'static': 1,
                'separate': 1, 'visible': 1, 'line': 1, 'header': 1, 'inset': 1,
            };

            function applyStoredSettings() {
            try {
                var stored = JSON.parse(localStorage.getItem('accountflow.uiflow.settings') || '{}');
                var root = document.documentElement;

                var seed = root.getAttribute('data-accent');
                if (seed && stored.accent === undefined && !(root.getAttribute('data-lock') || '').includes('accent')) {
                    stored.accent = seed;
                    localStorage.setItem('accountflow.uiflow.settings', JSON.stringify(stored));
                }

                var s = Object.assign({}, DEFAULTS, stored);

                var theme = s.theme;
                if (theme === 'auto') {
                    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                root.setAttribute('data-bs-theme', theme);

                var locked = (root.getAttribute('data-lock') || '').split(/[\s,]+/);

                for (var key in ATTRS) {
                    if (locked.indexOf(key) !== -1) continue;
                    var value = s[key];
                    if (value == null || NEUTRAL[value]) root.removeAttribute(ATTRS[key]);
                    else root.setAttribute(ATTRS[key], value);
                }

                var tokens = stored.tokens || {};
                for (var name in tokens) root.style.setProperty(name, tokens[name]);
            } catch (e) {
                document.documentElement.setAttribute('data-bs-theme', 'light');
                document.documentElement.setAttribute('data-layout', 'vertical');
            }
            }

            window.ufApplyStoredSettings = applyStoredSettings;
            applyStoredSettings();
        })();
    </script>

    <link rel="stylesheet" href="{{ asset(config('accountflow.asset_path').'theme/ui-flow/css/app.css') }}">
    {{-- Themes ApexCharts' tooltip/legend/gridline chrome to match ui-flow's
         tokens (compiled from the theme's own core/scss/plugins/_apexcharts.scss
         — that file isn't in app.css above, which only bundles bootstrap+core+
         admin, not the plugins layer). --}}
    <link rel="stylesheet" href="{{ asset(config('accountflow.asset_path').'theme/ui-flow/css/apexcharts.css') }}">
    @stack('styles')

    @livewireStyles
</head>
