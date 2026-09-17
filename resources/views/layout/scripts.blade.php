{{--
    Drop-in JS for the ui-flow shell, plus the Livewire wire:navigate reinit
    wiring. AccountFlow's pages are Livewire full-page components navigated
    between via wire:navigate, so all of this is load-bearing, not optional.

    data-navigate-once is LOAD-BEARING, not an optimisation: without it
    Livewire re-executes this script on every wire:navigate, and each
    execution is a whole second copy of the bundle with its own Bootstrap —
    symptoms include the sidebar accordion silently toggling twice per click
    and offcanvas backdrops stacking up.
--}}
{{-- AccountFlow owns its own shell now (it no longer borrows the host app's
     layout), so it has to bring its own charting library — several pages
     (dashboard, reports) render ApexCharts. Self-hosted at the exact version
     ui-flow's own _apexcharts.scss theming (head.blade.php) was built
     against, rather than a CDN's "latest 3.x" that can drift out of sync with
     that CSS. Loaded once here rather than per-page so it's never duplicated. --}}
<script src="{{ asset(config('accountflow.asset_path').'theme/ui-flow/js/vendor/apexcharts.min.js') }}" defer data-navigate-once></script>
{{-- Token bridge (window.AccountFlowCharts) — ported from ui-flow-admin's own
     js/charts/theme.js so chart colours come from the SAME --uf-* custom
     properties as the rest of the UI, and follow a live dark-mode/accent
     change instead of staying stuck on whatever was current at chart-init
     time. Must load after ApexCharts, before any page's own chart script. --}}
<script src="{{ asset(config('accountflow.asset_path').'theme/ui-flow/js/charts-theme.js') }}" defer data-navigate-once></script>
<script src="{{ asset(config('accountflow.asset_path').'theme/ui-flow/js/app.js') }}" defer data-navigate-once></script>
@stack('scripts')

@livewireScripts

@once
    <script>
        if (!window._afAccountFlowInit) {
            window._afAccountFlowInit = true;

            /**
             * Freezes every transition/animation for the length of a
             * wire:navigate swap. wire:navigate replaces <html>'s attributes
             * with the server's, so for the duration of the swap every
             * themed colour falls back to its default and animates back
             * when restored — this is what "the app flickers on navigation"
             * turns out to be. document.adoptedStyleSheets is the vehicle
             * because it survives the swap; an inline style or a class on a
             * persisted element does not (or only covers that element).
             */
            var freezeSheet = null;
            var scrollbarFreezeSheet = null;

            if ('adoptedStyleSheets' in Document.prototype) {
                freezeSheet = new CSSStyleSheet();
                freezeSheet.replaceSync(
                    '*:not(#nprogress, #nprogress *), *::before, *::after {' +
                    'transition: none !important; animation: none !important; }'
                );
                scrollbarFreezeSheet = new CSSStyleSheet();
                scrollbarFreezeSheet.replaceSync('html { scrollbar-gutter: stable !important; }');
            }

            var freezeTransitions = function() {
                if (!freezeSheet || document.adoptedStyleSheets.indexOf(freezeSheet) !== -1) return;
                document.adoptedStyleSheets = document.adoptedStyleSheets.concat(freezeSheet);

                if (document.documentElement.hasAttribute('data-scrollbar-space')) {
                    document.adoptedStyleSheets = document.adoptedStyleSheets.concat(scrollbarFreezeSheet);
                }
            };

            var thawTransitions = function() {
                if (!freezeSheet) return;

                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        document.adoptedStyleSheets = document.adoptedStyleSheets
                            .filter(function(sheet) {
                                return sheet !== freezeSheet && sheet !== scrollbarFreezeSheet;
                            });
                    });
                });
            };

            /**
             * Re-marks the current page in the sidebar/menubar after a
             * wire:navigate. The sidebar is persisted (see app.blade.php), so
             * wire:navigate keeps the exact DOM node — which is the whole
             * point, no re-rendered icons or collapse animations — but it
             * also means the server-rendered .active from
             * request()->routeIs() freezes on whichever page loaded first.
             * Longest-prefix match, so a detail page still lights up its
             * list entry.
             */
            var syncActiveNav = function() {
                var here = location.pathname.replace(/\/+$/, '') || '/';

                document.querySelectorAll('.sidebar-nav, .app-menubar').forEach(function(nav) {
                    var best = null;
                    var bestLength = 0;

                    nav.querySelectorAll('a[href]').forEach(function(link) {
                        link.classList.remove('active');
                        link.removeAttribute('aria-current');

                        var raw = link.getAttribute('href') || '';
                        if (!raw || raw.charAt(0) === '#') return;

                        var path;
                        try {
                            path = new URL(link.href, location.origin).pathname.replace(/\/+$/, '') || '/';
                        } catch (e) {
                            return;
                        }

                        var isMatch = here === path || (path !== '/' && here.indexOf(path + '/') === 0);
                        if (isMatch && path.length > bestLength) {
                            best = link;
                            bestLength = path.length;
                        }
                    });

                    if (!best) return;

                    best.classList.add('active');
                    best.setAttribute('aria-current', 'page');

                    var group = best.closest('.menubar-item-group');

                    while (group) {
                        var groupLink = group.querySelector(':scope > .menubar-link');
                        if (groupLink) groupLink.classList.add('active');

                        group = group.parentElement
                            ? group.parentElement.closest('.menubar-item-group')
                            : null;
                    }

                    nav.querySelectorAll('.nav-submenu.show').forEach(function(submenu) {
                        if (submenu.contains(best)) return;

                        submenu.classList.remove('show');

                        var openToggle = submenu.parentElement
                            ? submenu.parentElement.querySelector(':scope > .nav-toggle')
                            : null;

                        if (openToggle) {
                            openToggle.classList.remove('active');
                            openToggle.classList.add('collapsed');
                            openToggle.setAttribute('aria-expanded', 'false');
                        }
                    });

                    var submenu = best.closest('.nav-submenu');

                    while (submenu) {
                        submenu.classList.add('show');

                        var branchToggle = submenu.parentElement
                            ? submenu.parentElement.querySelector(':scope > .nav-toggle')
                            : null;

                        if (branchToggle) {
                            branchToggle.classList.add('active');
                            branchToggle.classList.remove('collapsed');
                            branchToggle.setAttribute('aria-expanded', 'true');
                        }

                        submenu = submenu.parentElement
                            ? submenu.parentElement.closest('.nav-submenu')
                            : null;
                    }
                });
            };

            document.addEventListener('livewire:navigating', freezeTransitions);

            document.addEventListener('livewire:navigated', function() {
                // wire:navigate replaces <html>'s attributes with the
                // server-rendered ones, wiping the visitor's theme/density/
                // radius/sidebar-skin on every navigation — re-apply them.
                if (window.ufApplyStoredSettings) window.ufApplyStoredSettings();
                if (window.ArtflowAdmin && window.ArtflowAdmin.refreshScrollbarSpace) {
                    window.ArtflowAdmin.refreshScrollbarSpace();
                }

                syncActiveNav();
                if (window.ArtflowAdmin) window.ArtflowAdmin.refresh();
                syncActiveNav();
                thawTransitions();
            });

            if (window.Livewire) {
                Livewire.hook('morph.updated', function() {
                    if (window.ArtflowAdmin) window.ArtflowAdmin.refresh();
                });

                Livewire.hook('morph.updating', function({ el }) {
                    if (el.matches && el.matches('.modal.show')) {
                        var modal = bootstrap.Modal.getInstance(el);
                        if (modal) modal.hide();
                    }
                });
            }
        }
    </script>
@endonce
