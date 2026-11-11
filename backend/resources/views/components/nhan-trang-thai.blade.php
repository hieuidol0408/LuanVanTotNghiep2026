@props([
    'status' => '',
    'type' => null,
])

@php
    $rawStatus = trim((string) $status);
    $viMap = [
        'IN_PERSON' => 'Trực tiếp',
        'in_person' => 'Trực tiếp',
        'in person' => 'Trực tiếp',
        'ONLINE' => 'Trực tuyến',
        'online' => 'Trực tuyến',
        'HYBRID' => 'Kết hợp',
        'hybrid' => 'Kết hợp',
        'PREPARING' => 'Đang chuẩn bị',
        'preparing' => 'Đang chuẩn bị',
        'SCHEDULED' => 'Sắp diễn ra',
        'scheduled' => 'Sắp diễn ra',
        'ONGOING' => 'Đang diễn ra',
        'ongoing' => 'Đang diễn ra',
        'IN_PROGRESS' => 'Đang thực hiện',
        'in_progress' => 'Đang thực hiện',
        'COMPLETED' => 'Đã kết thúc',
        'completed' => 'Đã kết thúc',
        'CANCELLED' => 'Đã hủy',
        'cancelled' => 'Đã hủy',
        'DRAFT' => 'Mới tạo',
        'draft' => 'Mới tạo',
        'SUBMITTED' => 'Mới tạo',
        'submitted' => 'Mới tạo',
        'PENDING' => 'Chờ xử lý',
        'pending' => 'Chờ xử lý',
        'APPROVED_FOR_MEETING' => 'Đủ điều kiện',
        'ELIGIBLE' => 'Đủ điều kiện',
        'NEED_MORE_INFO' => 'Chờ bổ sung',
        'REVISE' => 'Chờ bổ sung',
        'COMPLETE' => 'Đầy đủ',
        'PRESENT' => 'Có mặt',
        'INVITED' => 'Đã mời',
        'ABSENT' => 'Vắng',
        'EXCUSED' => 'Có xin phép',
        'OPENING' => 'Đang mở',
        'CLOSED' => 'Đã đóng',
        'PASSED' => 'Đã thông qua',
        'REJECTED' => 'Không thông qua',
        'SIGNED' => 'Đã ký ban hành',
        'PUBLISHED' => 'Đã công bố',
        'ASSIGNED' => 'Chưa thực hiện',
        'OVERDUE' => 'Quá hạn',
        'APPROVED' => 'Đạt',
        'ACTIVE' => 'Đang hoạt động',
        'INACTIVE' => 'Ngừng hoạt động',
    ];
    $displayStatus = $viMap[$rawStatus] ?? $viMap[strtoupper($rawStatus)] ?? $rawStatus;

    // Tự động suy luận kiểu màu trạng thái theo đúng chuẩn nghiệp vụ STU nếu không truyền explicit type
    $inferredType = $type;

    if (!$inferredType) {
        $normalized = mb_strtolower(trim($displayStatus), 'UTF-8');
        
        $inferredType = match(true) {
            // Thành công / Đã xong / Có mặt / Đạt / Đã công bố / Hợp lệ
            str_contains($normalized, 'hoàn thành') || 
            str_contains($normalized, 'đã kết thúc') || 
            str_contains($normalized, 'đã công bố') || 
            str_contains($normalized, 'có mặt') || 
            str_contains($normalized, 'đủ điều kiện') || 
            str_contains($normalized, 'đầy đủ') || 
            str_contains($normalized, 'đạt') || 
            str_contains($normalized, 'đã ký') || 
            str_contains($normalized, 'tán thành') || 
            str_contains($normalized, 'đã thông qua') || 
            str_contains($normalized, 'đã diễn ra') || 
            str_contains($normalized, 'đã xem xét') => 'success',

            // Chờ xử lý / Đang thực hiện / Đang mở / Cần bổ sung / Có xin phép
            str_contains($normalized, 'đang thực hiện') || 
            str_contains($normalized, 'đang chuẩn bị') || 
            str_contains($normalized, 'đang diễn ra') || 
            str_contains($normalized, 'đang thảo luận') || 
            str_contains($normalized, 'đang mở') || 
            str_contains($normalized, 'chờ') || 
            str_contains($normalized, 'yêu cầu bổ sung') || 
            str_contains($normalized, 'có xin phép') || 
            str_contains($normalized, 'sắp diễn ra') || 
            str_contains($normalized, 'đã mời') || 
            str_contains($normalized, 'xác nhận') => 'warning',

            // Nguy hiểm / Lỗi / Hủy / Vắng không phép / Không tán thành / Quá hạn
            str_contains($normalized, 'hủy') || 
            str_contains($normalized, 'đã ẩn') || 
            str_contains($normalized, 'không xin phép') || 
            str_contains($normalized, 'vắng') || 
            str_contains($normalized, 'quá hạn') || 
            str_contains($normalized, 'không tán thành') || 
            str_contains($normalized, 'không thông qua') || 
            str_contains($normalized, 'từ chối') => 'danger',

            // Thông tin / Dự thảo / Mới tạo
            str_contains($normalized, 'dự thảo') || 
            str_contains($normalized, 'mới tạo') || 
            str_contains($normalized, 'đã xếp lịch') || 
            str_contains($normalized, 'chưa mở') || 
            str_contains($normalized, 'chưa xem xét') || 
            str_contains($normalized, 'chưa thực hiện') || 
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
    <span>{{ $slot->isEmpty() ? $displayStatus : $slot }}</span>
</span>
