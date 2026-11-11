@props([
    'headers' => [],
    'empty' => false,
    'emptyTitle' => 'Chưa có bản ghi nào',
    'emptyMessage' => 'Hiện tại chưa có dữ liệu nào trong danh sách.',
    'hasActions' => false,
    'actionTitle' => 'Thao tác',
    'cardTitle' => null,
    'cardActions' => null,
])

<div {{ $attributes->merge(['class' => 'stu-card']) }}>
    @if($cardTitle || $cardActions)
        <div class="stu-card-header">
            @if($cardTitle)
                <h3 class="stu-card-title">{{ $cardTitle }}</h3>
            @endif
            @if($cardActions)
                <div class="stu-card-actions">
                    {{ $cardActions }}
                </div>
            @endif
        </div>
    @endif

    <div class="stu-table-wrapper">
        <table class="stu-table">
            @if(!empty($headers))
                @php
                    $normalizedHeaders = $headers;
                    if ($hasActions && count($normalizedHeaders) > 0) {
                        $lastHeader = end($normalizedHeaders);
                        if (in_array(mb_strtolower(trim($lastHeader)), [mb_strtolower(trim($actionTitle)), 'thao tác', 'hành động'])) {
                            array_pop($normalizedHeaders);
                        }
                    }
                @endphp
                <thead>
                    <tr>
                        @foreach($normalizedHeaders as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                        @if($hasActions)
                            <th class="col-actions">{{ $actionTitle }}</th>
                        @endif
                    </tr>
                </thead>
            @elseif(isset($thead))
                <thead>
                    {{ $thead }}
                </thead>
            @endif

            <tbody>
                @if($empty)
                    <tr>
                        <td colspan="{{ count($normalizedHeaders ?? $headers) + ($hasActions ? 1 : 0) }}" style="padding: 0;">
                            <div class="stu-empty-state">
                                <div class="stu-empty-icon">
                                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <div class="stu-empty-title">{{ $emptyTitle }}</div>
                                <div class="stu-empty-desc">{{ $emptyMessage }}</div>
                            </div>
                        </td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>
</div>
