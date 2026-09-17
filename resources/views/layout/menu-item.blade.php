{{--
    One sidebar entry — leaf or disclosure group. A group renders its own
    children through this same partial (recursive).

    Receives one node of the $menu array declared in menu.blade.php, so `key`
    is always present and `children` is always an array of the same shape.
--}}
@php
    use ArtflowStudio\AccountFlow\Support\NavState;

    $hasChildren = ! empty($item['children']);
    $isActive = NavState::isActive($item);
    $hasActiveChild = NavState::hasActiveChild($item);
@endphp
@if ($hasChildren)
    <li class="nav-item nav-item-group" data-depth="{{ $depth ?? 0 }}">
        <a class="nav-link nav-toggle @if ($hasActiveChild) active @else collapsed @endif" data-bs-toggle="collapse"
            href="#nav-{{ $item['key'] }}" role="button" aria-expanded="{{ $hasActiveChild ? 'true' : 'false' }}"
            aria-controls="nav-{{ $item['key'] }}">
            @if (! empty($item['icon']))
                <span class="nav-icon"><i data-lucide="{{ $item['icon'] }}"></i></span>
            @endif
            <span class="nav-text">{{ $item['label'] }}</span>
            @if (! empty($item['badge']))
                <span class="nav-badge">{{ $item['badge'] }}</span>
            @endif
            <span class="nav-arrow"><i data-lucide="chevron-right"></i></span>
        </a>
        <div class="collapse nav-submenu @if ($hasActiveChild) show @endif" id="nav-{{ $item['key'] }}">
            <ul class="nav-list">
                @foreach ($item['children'] as $child)
                    @include(config('accountflow.view_path') . 'layout.menu-item', ['item' => $child, 'depth' => ($depth ?? 0) + 1])
                @endforeach
            </ul>
        </div>
    </li>
@else
    @php
        $ufUrl = NavState::url($item);
        $ufMissing = NavState::missingRoute($item);
    @endphp
    <li class="nav-item" data-depth="{{ $depth ?? 0 }}">
        <a class="nav-link @if ($isActive) active @endif" href="{{ $ufUrl }}"
            @if ($ufUrl !== '#') wire:navigate @endif
            @if ($ufMissing) data-uf-missing-route="{{ $ufMissing }}" @endif
            @if ($isActive) aria-current="page" @endif>
            @if (! empty($item['icon']))
                <span class="nav-icon"><i data-lucide="{{ $item['icon'] }}"></i></span>
            @endif
            <span class="nav-text">{{ $item['label'] }}</span>
            @if (! empty($item['badge']))
                <span class="nav-badge">{{ $item['badge'] }}</span>
            @endif
        </a>
    </li>
@endif
