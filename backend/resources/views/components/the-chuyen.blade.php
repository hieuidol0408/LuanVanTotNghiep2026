@props([
    'tabs' => [],
])

<div {{ $attributes->merge(['class' => 'stu-tabs-wrapper']) }}>
    <div class="stu-tabs" role="tablist">
        @foreach($tabs as $tab)
            @php
                $isActive = $tab['active'] ?? $loop->first;
                $targetId = $tab['target'] ?? ('#' . ($tab['id'] ?? 'tab_' . $loop->index));
                $badge = $tab['badge'] ?? null;
            @endphp
            <button 
                type="button" 
                class="stu-tab-btn {{ $isActive ? 'active' : '' }}" 
                data-tab-target="{{ $targetId }}"
                role="tab"
                aria-selected="{{ $isActive ? 'true' : 'false' }}"
            >
                @if(isset($tab['icon']))
                    {!! $tab['icon'] !!}
                @endif
                <span>{{ $tab['label'] }}</span>
                @if($badge !== null)
                    <span class="stu-tab-badge">{{ $badge }}</span>
                @endif
            </button>
        @endforeach
    </div>

    <div class="stu-tab-panes">
        {{ $slot }}
    </div>
</div>
