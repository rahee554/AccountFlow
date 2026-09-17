/**
 * ApexCharts <-> ui-flow token bridge, plain-script port of ui-flow-admin's
 * js/charts/theme.js.
 *
 * The original is an ES module built by ui-flow's own Vite pipeline
 * (`import ApexCharts from 'apexcharts'`). AccountFlow ships pre-built,
 * no-build-step assets (self-hosted apexcharts.min.js as a plain global,
 * see layout/scripts.blade.php), so this is the same token-resolution and
 * live-theme-reactivity logic rewritten as a global IIFE instead of an
 * import graph — not a different design, just a different module format.
 */
(function () {
    var root = document.documentElement;

    function read(name) {
        return getComputedStyle(root).getPropertyValue(name).trim();
    }

    function tokens() {
        return {
            accent: read('--uf-accent'),
            accentOn: read('--uf-accent-on'),
            success: read('--uf-success'),
            warning: read('--uf-warning'),
            danger: read('--uf-danger'),
            info: read('--uf-info'),
            text: read('--uf-text'),
            textMuted: read('--uf-text-muted'),
            border: read('--uf-border-subtle'),
            surface: read('--uf-surface'),
            font: read('--uf-font-sans'),
            isDark: root.getAttribute('data-bs-theme') === 'dark',
        };
    }

    function palette() {
        var t = tokens();
        return [t.accent, t.info, t.success, t.warning, t.danger];
    }

    var compactNumber = new Intl.NumberFormat(undefined, { notation: 'compact', maximumFractionDigits: 1 });

    function formatCompact(value) {
        return compactNumber.format(value);
    }

    function baseOptions() {
        var t = tokens();

        return {
            chart: {
                fontFamily: t.font,
                foreColor: t.textMuted,
                toolbar: { show: false },
                zoom: { enabled: false },
                animations: { speed: 400 },
                background: 'transparent',
            },
            theme: { mode: t.isDark ? 'dark' : 'light' },
            colors: palette(),
            grid: {
                borderColor: t.border,
                strokeDashArray: 4,
                padding: { left: 4, right: 4, top: 0 },
            },
            dataLabels: { enabled: false },
            tooltip: {
                theme: t.isDark ? 'dark' : 'light',
                style: { fontFamily: t.font, fontSize: '12px' },
            },
            legend: {
                fontFamily: t.font,
                fontSize: '12px',
                markers: { size: 5, offsetX: -2 },
                itemMargin: { horizontal: 10 },
                labels: { colors: t.textMuted },
            },
            xaxis: {
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { fontSize: '11px' } },
            },
            yaxis: {
                labels: { style: { fontSize: '11px' } },
            },
            stroke: { lineCap: 'round' },
        };
    }

    /** Re-applies token-derived colours to every registered chart on a live theme change. */
    var registry = [];
    var scheduled = false;

    function registerChart(chart, rebuild) {
        registry.push({ chart: chart, rebuild: rebuild });
    }

    window.addEventListener('uiflow:settings', function () {
        if (scheduled) return;
        scheduled = true;

        requestAnimationFrame(function () {
            scheduled = false;
            registry.forEach(function (entry) {
                try {
                    entry.chart.updateOptions(entry.rebuild(), false, false);
                } catch (e) {
                    /* chart destroyed by a wire:navigate teardown — nothing to update */
                }
            });
        });
    });

    window.AccountFlowCharts = {
        tokens: tokens,
        palette: palette,
        baseOptions: baseOptions,
        formatCompact: formatCompact,
        registerChart: registerChart,
    };
})();
