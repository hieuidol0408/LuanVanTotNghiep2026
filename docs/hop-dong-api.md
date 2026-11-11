# ĐẶC TẢ GIAO DIỆN LẬP TRÌNH ỨNG DỤNG (API CONTRACTS)
**Dự án:** Ứng dụng web hỗ trợ quản lý phiên họp và thực hiện nghị quyết của Hội đồng trường STU  
**Phiên bản API:** V1 (`/api/v1`)  
**Chuẩn giao tiếp:** RESTful API  
**Định dạng dữ liệu:** JSON (`Content-Type: application/json`, `Accept: application/json`)  

---

## 1. Quy ước Chung (Conventions)

### 1.1. Cấu trúc Phản hồi JSON Chuẩn (Standard Response Envelope)
Mọi endpoint đều trả về cấu trúc thống nhất:
```json
{
  "success": true,
  "message": "Thông báo kết quả thao tác",
  "data": { ... },
  "errors": null,
  "meta": {
    "page": 1,
    "per_page": 15,
    "total": 120,
    "last_page": 8
  }
}
```

### 1.2. Mã Trạng thái HTTP (HTTP Status Codes)
* `200 OK`: Thành công với phương thức GET, PUT, PATCH.
* `201 Created`: Thành công tạo mới tài nguyên với POST.
* `204 No Content`: Thành công xóa tài nguyên với DELETE.
* `400 Bad Request`: Lỗi logic nghiệp vụ hoặc yêu cầu không đúng điều kiện.
* `401 Unauthorized`: Chưa xác thực hoặc Token không hợp lệ / hết hạn.
* `403 Forbidden`: Người dùng không có quyền truy cập tài nguyên (ví dụ: Khách mời cố tình gửi phiếu biểu quyết).
* `404 Not Found`: Không tìm thấy tài nguyên theo ID hoặc tham số truy vấn.
* `422 Unprocessable Entity`: Lỗi kiểm thực dữ liệu đầu vào (Validation errors). Chi tiết lỗi trả về trong trường `errors`.
* `500 Internal Server Error`: Lỗi phát sinh từ phía máy chủ.

### 1.3. Quy chuẩn Truy vấn Danh sách (Pagination, Filtering, Sorting, Searching)
Đối với các endpoint dạng danh sách (Index):
* **Phân trang:** `?page=1&per_page=15` (mặc định: `page=1`, `per_page=15`, tối đa `per_page=100`).
* **Tìm kiếm từ khóa chung:** `?search=tu_khoa`.
* **Lọc chính xác (Filters):** `?status=IN_PROGRESS&department_id=2`.
* **Sắp xếp (Sorting):** `?sort_by=created_at&sort_order=desc`.

---

## 2. Danh mục Endpoints theo Module

### 2.1. Module 1: Xác thực & Hồ sơ Cá nhân (Auth & Profile)

| Phương thức | Endpoint | Mô tả | Vai trò yêu cầu |
|:---|:---|:---|:---|
| `POST` | `/api/v1/auth/login` | Đăng nhập hệ thống, cấp Bearer Token | Public |
| `POST` | `/api/v1/auth/logout` | Đăng xuất, hủy Bearer Token hiện tại | Đã xác thực |
| `GET` | `/api/v1/auth/me` | Lấy thông tin tài khoản đang đăng nhập và quyền hạn | Đã xác thực |
| `PUT` | `/api/v1/auth/change-password` | Đổi mật khẩu tài khoản cá nhân | Đã xác thực |
| `PUT` | `/api/v1/auth/profile` | Cập nhật thông tin liên hệ, ảnh đại diện | Đã xác thực |

#### Request Mẫu: `POST /api/v1/auth/login`
```json
{
  "email": "thuky_hdt@stu.edu.vn",
  "password": "Password123@"
}
```

#### Response Thành công Mẫu (200 OK):
```json
{
  "success": true,
  "message": "Đăng nhập thành công.",
  "data": {
    "token": "1|LaravelSanctumTokenString...",
    "user": {
      "id": 2,
      "full_name": "TS. Trần Văn Thư Ký",
      "email": "thuky_hdt@stu.edu.vn",
      "department": { "id": 1, "code": "VP_HDT", "name": "Văn phòng Hội đồng trường" },
      "roles": ["SECRETARY"],
      "permissions": ["dossiers.review", "meetings.manage", "votings.manage", "resolutions.manage"]
    }
  },
  "errors": null,
  "meta": null
}
```

---

### 2.2. Module 2: Quản lý Hội đồng trường & Thành viên (Councils & Members)

| Phương thức | Endpoint | Mô tả | Vai trò yêu cầu |
|:---|:---|:---|:---|
| `GET` | `/api/v1/councils` | Danh sách các khóa/nhiệm kỳ HĐT | Đã xác thực |
| `POST` | `/api/v1/councils` | Tạo mới nhiệm kỳ HĐT | `ADMIN` |
| `GET` | `/api/v1/councils/{id}` | Chi tiết nhiệm kỳ | Đã xác thực |
| `PUT` | `/api/v1/councils/{id}` | Cập nhật thông tin nhiệm kỳ | `ADMIN` |
| `GET` | `/api/v1/councils/{id}/members` | Danh sách thành viên HĐT theo nhiệm kỳ | Đã xác thực |
| `POST` | `/api/v1/councils/{id}/members` | Bổ nhiệm thành viên vào nhiệm kỳ HĐT | `ADMIN`, `SECRETARY` |
| `PUT` | `/api/v1/councils/members/{member_id}` | Cập nhật chức vụ, trạng thái thành viên | `ADMIN`, `SECRETARY` |

---

### 2.3. Module 3: Quản lý Hồ sơ trình (Dossiers)

| Phương thức | Endpoint | Mô tả | Vai trò yêu cầu |
|:---|:---|:---|:---|
| `GET` | `/api/v1/dossiers` | Danh sách hồ sơ trình (phân trang, lọc theo status, dept) | Đã xác thực |
| `POST` | `/api/v1/dossiers` | Tạo mới hồ sơ trình (kèm upload file tờ trình) | `UNIT_REP` |
| `GET` | `/api/v1/dossiers/{id}` | Chi tiết hồ sơ trình và danh sách file đính kèm | Đã xác thực |
| `PUT` | `/api/v1/dossiers/{id}` | Cập nhật hồ sơ (chỉ khi đang ở `DRAFT` hoặc `REVISION_REQUIRED`) | `UNIT_REP` |
| `POST` | `/api/v1/dossiers/{id}/submit` | Nộp hồ sơ chính thức lên Thư ký | `UNIT_REP` |
| `POST` | `/api/v1/dossiers/{id}/review` | Thẩm tra hồ sơ (Duyệt đạt / Yêu cầu sửa / Từ chối) | `SECRETARY` |
| `DELETE` | `/api/v1/dossiers/{id}` | Xóa hồ sơ (chỉ khi ở trạng thái `DRAFT`) | `UNIT_REP`, `ADMIN` |

#### Request Mẫu: `POST /api/v1/dossiers/{id}/review`
```json
{
  "decision": "APPROVED",
  "comments": "Hồ sơ đầy đủ căn cứ pháp lý, thống nhất trình phiên họp tháng 11/2026."
}
```

---

### 2.4. Module 4: Quản lý Phiên họp & Tài liệu (Meetings)

| Phương thức | Endpoint | Mô tả | Vai trò yêu cầu |
|:---|:---|:---|:---|
| `GET` | `/api/v1/meetings` | Danh sách các phiên họp (lọc theo năm, kỳ, status) | Đã xác thực |
| `POST` | `/api/v1/meetings` | Lập kế hoạch phiên họp mới | `SECRETARY`, `CHAIR` |
| `GET` | `/api/v1/meetings/{id}` | Chi tiết phiên họp, chương trình họp, thành phần dự họp | Đã xác thực |
| `PUT` | `/api/v1/meetings/{id}` | Cập nhật thông tin phiên họp | `SECRETARY`, `CHAIR` |
| `POST` | `/api/v1/meetings/{id}/agendas` | Thêm mục vào chương trình họp (gắn hồ sơ trình) | `SECRETARY` |
| `PUT` | `/api/v1/meetings/agendas/{agenda_id}` | Cập nhật thứ tự, thời lượng mục chương trình | `SECRETARY` |
| `DELETE` | `/api/v1/meetings/agendas/{agenda_id}` | Xóa mục chương trình | `SECRETARY` |
| `POST` | `/api/v1/meetings/{id}/attendees` | Thêm khách mời vào danh sách tham dự | `SECRETARY` |
| `POST` | `/api/v1/meetings/{id}/checkin` | Điểm danh thành phần tham dự phiên họp | `SECRETARY` |
| `POST` | `/api/v1/meetings/{id}/send-invitations` | Phát hành giấy triệu tập qua email & hệ thống | `SECRETARY`, `CHAIR` |
| `POST` | `/api/v1/meetings/{id}/start` | Bắt đầu phiên họp (chuyển `IN_PROGRESS`) | `CHAIR`, `SECRETARY` |
| `POST` | `/api/v1/meetings/{id}/complete` | Bế mạc phiên họp (chuyển `COMPLETED`) | `CHAIR`, `SECRETARY` |

#### Request Mẫu: `POST /api/v1/meetings/{id}/checkin`
```json
{
  "attendees": [
    { "user_id": 3, "attendance_status": "PRESENT", "notes": "Có mặt đúng giờ" },
    { "user_id": 4, "attendance_status": "EXCUSED", "notes": "Đi công tác nước ngoài" }
  ]
}
```

---

### 2.5. Module 5: Quản lý Biểu quyết & Kiểm phiếu (Voting)

| Phương thức | Endpoint | Mô tả | Vai trò yêu cầu |
|:---|:---|:---|:---|
| `GET` | `/api/v1/meetings/{meeting_id}/votings` | Danh sách nội dung biểu quyết của phiên họp | Đã xác thực |
| `POST` | `/api/v1/meetings/{meeting_id}/votings` | Tạo mới nội dung biểu quyết | `SECRETARY`, `CHAIR` |
| `GET` | `/api/v1/votings/{id}` | Chi tiết nội dung biểu quyết và phương án | Đã xác thực |
| `POST` | `/api/v1/votings/{id}/open` | Mở cổng nhận phiếu biểu quyết | `CHAIR`, `SECRETARY` |
| `POST` | `/api/v1/votings/{id}/vote` | **Thực hiện nộp phiếu biểu quyết** | **Chỉ Thành viên HĐT có mặt** |
| `POST` | `/api/v1/votings/{id}/close` | Đóng biểu quyết, tự động tổng hợp kết quả | `CHAIR`, `SECRETARY` |
| `GET` | `/api/v1/votings/{id}/results` | Xem biên bản và biểu đồ kết quả kiểm phiếu | Đã xác thực |

#### Request Mẫu: `POST /api/v1/votings/{id}/vote`
```json
{
  "voting_option_id": 1
}
```

#### Phản hồi Lỗi Bị Chặn (403 Forbidden - Khi khách mời cố tình gửi phiếu):
```json
{
  "success": false,
  "message": "Bạn không có quyền biểu quyết trong phiên họp này (Chỉ dành cho Thành viên HĐT chính thức đã điểm danh có mặt).",
  "errors": null,
  "data": null
}
```

#### Response Kết quả Mẫu: `GET /api/v1/votings/{id}/results` (200 OK):
```json
{
  "success": true,
  "message": "Lấy kết quả biểu quyết thành công.",
  "data": {
    "voting_id": 10,
    "title": "Thông qua Đề án phát triển Khoa Công nghệ Thông tin giai đoạn 2026-2030",
    "voting_type": "SECRET",
    "status": "CLOSED",
    "pass_threshold_percentage": 50.0,
    "base_calculation": "ATTENDEES",
    "total_eligible_voters": 15,
    "total_votes_cast": 15,
    "options": [
      { "id": 1, "option_text": "Tán thành", "vote_count": 13, "percentage": 86.67 },
      { "id": 2, "option_text": "Không tán thành", "vote_count": 2, "percentage": 13.33 },
      { "id": 3, "option_text": "Không có ý kiến", "vote_count": 0, "percentage": 0.0 }
    ],
    "is_passed": true,
    "conclusion": "ĐẠT - Thông qua theo quy chế."
  }
}
```

---

### 2.6. Module 6: Quản lý Biên bản & Nghị quyết (Resolutions)

| Phương thức | Endpoint | Mô tả | Vai trò yêu cầu |
|:---|:---|:---|:---|
| `GET` | `/api/v1/meetings/{id}/minutes` | Lấy nội dung biên bản phiên họp | Đã xác thực |
| `POST` | `/api/v1/meetings/{id}/minutes` | Lưu/cập nhật dự thảo biên bản phiên họp | `SECRETARY` |
| `POST` | `/api/v1/meetings/{id}/minutes/approve` | Phê duyệt biên bản phiên họp | `CHAIR` |
| `GET` | `/api/v1/resolutions` | Danh sách Nghị quyết đã ban hành | Đã xác thực |
| `POST` | `/api/v1/resolutions` | Tạo mới và ban hành Nghị quyết chính thức | `SECRETARY`, `CHAIR` |
| `GET` | `/api/v1/resolutions/{id}` | Chi tiết Nghị quyết và danh sách nhiệm vụ phân rã | Đã xác thực |
| `PUT` | `/api/v1/resolutions/{id}` | Cập nhật thông tin trích yếu, tệp đính kèm | `SECRETARY`, `CHAIR` |

---

### 2.7. Module 7: Phân rã Nhiệm vụ & Theo dõi thực hiện (Tasks)

| Phương thức | Endpoint | Mô tả | Vai trò yêu cầu |
|:---|:---|:---|:---|
| `GET` | `/api/v1/tasks` | Danh sách nhiệm vụ (lọc theo đơn vị, hạn chót, trạng thái) | Đã xác thực |
| `POST` | `/api/v1/tasks` | Tách nhiệm vụ từ Nghị quyết, gán đơn vị chủ trì & deadline | `SECRETARY`, `CHAIR` |
| `GET` | `/api/v1/tasks/{id}` | Chi tiết nhiệm vụ, nhật ký tiến độ và minh chứng | Đã xác thực |
| `PUT` | `/api/v1/tasks/{id}` | Điều chỉnh thông tin phân công nhiệm vụ | `SECRETARY`, `CHAIR` |
| `POST` | `/api/v1/tasks/{id}/progress-logs` | Đơn vị nộp báo cáo tiến độ (%) và tải lên minh chứng | `EXECUTING_UNIT` |
| `POST` | `/api/v1/tasks/progress-logs/{log_id}/review` | Thư ký/Chủ tịch nghiệm thu báo cáo minh chứng | `SECRETARY`, `CHAIR` |

#### Request Mẫu: `POST /api/v1/tasks/{id}/progress-logs` (Multipart/form-data)
* `progress_percentage`: `100`
* `report_content`: `Đã hoàn thành xây dựng chuẩn đầu ra ngành CNTT theo đúng chỉ đạo tại Nghị quyết.`
* `evidences[]`: `[File upload: Quyết định ban hành chuẩn đầu ra.pdf]`

---

### 2.8. Module 8: Dashboard, Thông báo & Tài liệu an toàn

| Phương thức | Endpoint | Mô tả | Vai trò yêu cầu |
|:---|:---|:---|:---|
| `GET` | `/api/v1/dashboard/summary` | Số liệu thống kê tổng quan (Họp, Nghị quyết, Nhiệm vụ) | Đã xác thực |
| `GET` | `/api/v1/dashboard/task-stats` | Thống kê tiến độ nhiệm vụ theo phòng/ban | Đã xác thực |
| `GET` | `/api/v1/notifications` | Danh sách thông báo người dùng | Đã xác thực |
| `PUT` | `/api/v1/notifications/{id}/read` | Đánh dấu đã đọc thông báo | Đã xác thực |
| `GET` | `/api/v1/documents/{id}/download` | **Tải tệp an toàn có kiểm tra quyền truy cập** | Đã xác thực |
| `GET` | `/api/v1/audit-logs` | Tra cứu nhật ký thao tác hệ thống | `ADMIN` |
