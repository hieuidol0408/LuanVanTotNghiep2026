<?php

namespace App\Models;

/**
 * Model NguoiDung (Kế thừa từ User để hỗ trợ định danh Tiếng Việt chuẩn mực)
 * Đại diện cho Bảng 2: NGUOI_DUNG trong CSDL Hội đồng trường STU
 */
class NguoiDung extends User
{
    // Kế thừa toàn bộ cấu hình bảng, quan hệ và xác thực từ User
}
