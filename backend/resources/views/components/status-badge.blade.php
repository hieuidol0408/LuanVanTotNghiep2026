@props([
    'status' => '',
    'type' => null,
])

@php
    // Tự động suy luận kiểu màu trạng thái theo đúng chuẩn nghiệp vụ STU nếu không truyền explicit type
    $inferredType = $type;

    if (!$inferredType) {
        $normalized = mb_strtolower(trim($status), 'UTF-8');
        
        $inferredType = match(true) {
            // Thành công / Đã xong / Có mặt / Đạt / Đã công bố / Hợp lệ
            str_contains($normalized, 'hoàn thành') || 
            str_contains($normalized, 'completed') || 
            str_contains($normalized, 'đã công bố') || 
            str_contains($normalized, 'có mặt') || 
            str_contains($normalized, 'đủ điều kiện') || 
            str_contains($normalized, 'đầy đủ') || 
            str_contains($normalized, 'đạt') || 
            str_contains($normalized, 'đã ký') || 
            str_contains($normalized, 'tán thành') || 
            str_contains($normalized, 'đã diễn ra') || 
            str_contains($normalized, 'đã xem xét') => 'success',

            // Chờ xử lý / Đang thực hiện / Đang mở / Cần bổ sung / Có xin phép
            str_contains($normalized, 'đang thực hiện') || 
            str_contains($normalized, 'in_progress') || 
            str_contains($normalized, 'ongoing') || 
            str_contains($normalized, 'đang diễn ra') || 
            str_contains($normalized, 'đang mở') || 
            str_contains($normalized, 'chờ') || 
            str_contains($normalized, 'pending') || 
            str_contains($normalized, 'yêu cầu bổ sung') || 
            str_contains($normalized, 'có xin phép') || 
            str_contains($normalized, 'sắp diễn ra') || 
            str_contains($normalized, 'xác nhận') => 'warning',

            // Nguy hiểm / Lỗi / Hủy / Vắng không phép / Không tán thành / Quá hạn
            str_contains($normalized, 'hủy') || 
            str_contains($normalized, 'cancelled') || 
            str_contains($normalized, 'không xin phép') || 
            str_contains($normalized, 'vắng') || 
            str_contains($normalized, 'quá hạn') || 
            str_contains($normalized, 'overdue') || 
            str_contains($normalized, 'không tán thành') || 
            str_contains($normalized, 'từ chối') => 'danger',

            // Thông tin / Dự thảo / Mới tạo
            str_contains($normalized, 'dự thảo') || 
            str_contains($normalized, 'mới tạo') || 
            str_contains($normalized, 'chưa mở') || 
            str_contains($normalized, 'chưa xem xét') || 
            str_contains($normalized, 'chưa phân rã') => 'info',

            default => 'neutral',
        };
    }

    $badgeClass = match($inferredType) {
        'success' => 'stu-badge-success',
        'warning' => 'stu-badge-warning',
        'danger'  => 'stu-badge-danger',
        'info'    => 'stu-badge-info',
        default   => 'stu-badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => "stu-badge {$badgeClass}"]) }}>
    <span class="stu-badge-dot"></span>
    <span>{{ $slot->isEmpty() ? $status : $slot }}</span>
</span>
