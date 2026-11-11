@props([
    'items' => [],
])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'stu-breadcrumb']) }}>
    <a href="{{ url('/dashboard') }}" style="display: flex; align-items: center; gap: 4px;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span>Trang chủ</span>
    </a>

    @foreach($items as $key => $item)
        @php
            if (is_array($item)) {
                $title = $item['title'] ?? $item['label'] ?? (is_string($key) ? $key : '');
                $url = $item['url'] ?? null;
            } elseif (is_string($key)) {
                $title = $key;
                $url = $item;
            } else {
                $title = (string) $item;
                $url = null;
            }
        @endphp
        <span class="stu-breadcrumb-separator">/</span>
        @if(!$loop->last && !empty($url))
            <a href="{{ $url }}">{{ $title }}</a>
        @else
            <span class="stu-breadcrumb-current">{{ $title }}</span>
        @endif
    @endforeach
</nav>
