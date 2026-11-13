@php
    $total = $paginator->total();
    $currentPerPage = $paginator->perPage();
    $options = [5, 10, 20, 50, 100];
    if (!in_array($currentPerPage, $options)) {
        $options[] = $currentPerPage;
        sort($options);
    }
@endphp

<nav role="navigation" aria-label="Phân trang" class="stu-pagination-container">
    {{-- 1. THÔNG TIN SỐ BẢN GHI HIỂN THỊ --}}
    <div class="stu-pagination-info">
        @if ($total > 0)
            Hiển thị <strong>{{ $paginator->firstItem() }}</strong> - <strong>{{ $paginator->lastItem() }}</strong> trong tổng số <strong>{{ $total }}</strong> bản ghi
        @else
            Hiển thị <strong>0</strong> bản ghi
        @endif
    </div>

    {{-- 2. ĐIỀU KHIỂN: CHẾ ĐỘ PHÂN TRANG (SỐ DÒNG / TRANG) & CÁC NÚT CHUYỂN TRANG --}}
    <div class="stu-pagination-controls">
        {{-- Bộ chọn chế độ phân trang (Số dòng / trang) --}}
        <div class="stu-pagination-per-page">
            <label for="stu-per-page-select-{{ $paginator->getPageName() }}">Số bản ghi / trang:</label>
            <select 
                id="stu-per-page-select-{{ $paginator->getPageName() }}" 
                class="stu-select stu-per-page-select" 
                onchange="window.location.href = this.value"
                title="Chọn số lượng bản ghi hiển thị trên mỗi trang"
            >
                @foreach($options as $opt)
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => $opt, 'page' => 1]) }}" {{ $currentPerPage == $opt ? 'selected' : '' }}>
                        {{ $opt }} dòng
                    </option>
                @endforeach
            </select>
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
            @if ($paginator->hasPages())
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
            @else
                {{-- Khi chỉ có 1 trang duy nhất --}}
                <span class="stu-page-btn active" aria-current="page">1</span>
            @endif

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
    </div>
</nav>
