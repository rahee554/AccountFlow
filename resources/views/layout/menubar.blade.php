{{--
    Horizontal navigation bar. Rendered on every page but only displayed by
    [data-layout="horizontal"] — the customizer's layout switch. Same
    $sections as the sidebar, flattened (a horizontal bar has no room for
    section headings).
--}}
<nav class="app-menubar" aria-label="Primary">
    <ul class="menubar-list">
        @foreach ($sections as $section)
            @foreach ($section['items'] as $item)
                @include(config('accountflow.view_path') . 'layout.menubar-item', ['item' => $item, 'depth' => 0])
            @endforeach
        @endforeach
    </ul>
</nav>
