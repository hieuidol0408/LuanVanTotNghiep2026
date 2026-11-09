@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Phân trang" class="stu-pagination-container">
        {{-- Thông tin số bản ghi tiếng Việt --}}
        <div class="stu-pagination-info">
            Hiển thị <strong>{{ $paginator->firstItem() }}</strong> - <strong>{{ $paginator->lastItem() }}</strong> trong tổng số <strong>{{ $paginator->total() }}</strong> bản ghi
        </div>

        {{-- Danh sách nút phân trang --}}
        <div class="stu-pagination-links">
            {{-- Nút Trang trước --}}
            @if ($paginator->onFirstPage())
                <span class="stu-page-btn disabled" aria-disabled="true" title="Trang trước">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    <span>Trước</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="stu-page-btn" title="Trang trước">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    <span>Trước</span>
                </a>
            @endif

            {{-- Các nút số trang --}}
            @foreach ($elements as $element)
                {{-- Dấu "..." --}}
                @if (is_string($element))
                    <span class="stu-page-btn dots" aria-disabled="true">{{ $element }}</span>
                @endif

                {{-- Mảng các trang --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="stu-page-btn active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="stu-page-btn">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Nút Trang sau --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="stu-page-btn" title="Trang tiếp theo">
                    <span>Sau</span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="stu-page-btn disabled" aria-disabled="true" title="Trang tiếp theo">
                    <span>Sau</span>
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>
    </nav>
@elseif ($paginator->total() > 0)
    <div class="stu-pagination-container">
        <div class="stu-pagination-info">
            Hiển thị toàn bộ <strong>{{ $paginator->total() }}</strong> bản ghi
        </div>
    </div>
@endif
