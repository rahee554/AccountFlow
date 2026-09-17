{{--
    One horizontal-menubar entry. Recursive, like menu-item.blade.php: a
    group renders its children through this same partial, and each extra
    level becomes a side flyout.
--}}
@php
    use ArtflowStudio\AccountFlow\Support\NavState;

    $hasChildren = ! empty($item['children']);
    $isActive = NavState::isActive($item);
    $hasActiveChild = NavState::hasActiveChild($item);
@endphp
@if ($hasChildren)
    <li class="menubar-item menubar-item-group" data-depth="{{ $depth ?? 0 }}">
        <a class="menubar-link @if ($hasActiveChild) active @endif" href="#" role="button" aria-haspopup="true"
            aria-expanded="false">
            @if (! empty($item['icon']))
                <i data-lucide="{{ $item['icon'] }}"></i>
            @endif
            <span>{{ $item['label'] }}</span>
            @if (! empty($item['badge']))
                <span class="nav-badge">{{ $item['badge'] }}</span>
            @endif
            <i class="menubar-caret" data-lucide="chevron-down"></i>
        </a>

        <ul class="menubar-dropdown">
            @foreach ($item['children'] as $child)
                @include(config('accountflow.view_path') . 'layout.menubar-item', ['item' => $child, 'depth' => ($depth ?? 0) + 1])
            @endforeach
        </ul>
    </li>
@else
    @php
        $ufUrl = NavState::url($item);
        $ufMissing = NavState::missingRoute($item);
    @endphp
    <li class="menubar-item" data-depth="{{ $depth ?? 0 }}">
        <a class="menubar-link @if ($isActive) active @endif" href="{{ $ufUrl }}"
            @if ($ufUrl !== '#') wire:navigate @endif
            @if ($ufMissing) data-uf-missing-route="{{ $ufMissing }}" @endif>
            @if (! empty($item['icon']))
                <i data-lucide="{{ $item['icon'] }}"></i>
            @endif
            <span>{{ $item['label'] }}</span>
            @if (! empty($item['badge']))
                <span class="nav-badge">{{ $item['badge'] }}</span>
            @endif
        </a>
    </li>
@endif
