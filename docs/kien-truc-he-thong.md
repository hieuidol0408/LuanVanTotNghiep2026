# TÀI LIỆU KIẾN TRÚC HỆ THỐNG (SYSTEM ARCHITECTURE)
**Dự án:** Ứng dụng web hỗ trợ quản lý phiên họp và thực hiện nghị quyết của Hội đồng trường STU  
**Phiên bản:** 1.0  
**Trạng thái:** Bản chuẩn hóa kiến trúc  

---

## 1. Tổng quan Kiến trúc Hệ thống

Hệ thống được thiết kế theo mô hình **Monorepo phân tách rõ rệt giữa Frontend và Backend** (Decoupled Client-Server Architecture):
* **Frontend:** Ứng dụng Single Page Application (SPA) phát triển bằng **React.js (JavaScript thuần)**, đóng gói và build bằng **Vite**.
* **Backend:** Ứng dụng cung cấp dịch vụ máy chủ phát triển bằng **Laravel 11.x (PHP 8.2+)**, tổ chức theo mô hình **Layered Service Architecture** và cung cấp giao diện lập trình ứng dụng chuẩn **RESTful API**.
* **Database:** Hệ quản trị cơ sở dữ liệu quan hệ **MySQL 9.1 / 8.0** (InnoDB Engine).
* **Giao tiếp:** Trao đổi dữ liệu qua giao thức HTTP/HTTPS với định dạng dữ liệu chuẩn **JSON** và cơ chế bảo mật Bearer Token (Laravel Sanctum).

```
┌───────────────────────────────────────────────────────────────────────────┐
│                        FRONTEND: React.js (Vite)                          │
│                                                                           │
│   ┌───────────────────┐    ┌────────────────────┐    ┌────────────────┐   │
│   │  React Router v6  │───>│  Features Modules  │───>│ Ant Design 5.x │   │
│   │ (Protected Routes)│    │ (Components/Pages) │    │ (UI Components)│   │
│   └───────────────────┘    └────────────────────┘    └────────────────┘   │
│             │                        │                                    │
│             ▼                        ▼                                    │
│   ┌───────────────────┐    ┌────────────────────┐                         │
│   │ Global Store/Auth │    │   TanStack Query   │                         │
│   │   (Zustand/Ctx)   │    │  (Caching & Sync)  │                         │
│   └───────────────────┘    └────────────────────┘                         │
│                                      │                                    │
│                                      ▼                                    │
│                            ┌────────────────────┐                         │
│                            │   Axios Instance   │                         │
│                            │ (JWT Interceptors) │                         │
│                            └────────────────────┘                         │
└──────────────────────────────────────┬────────────────────────────────────┘
                                       │ (RESTful API / JSON)
                                       │ Bearer Token Authentication
                                       ▼
┌───────────────────────────────────────────────────────────────────────────┐
│                       BACKEND: Laravel 11.x (PHP)                         │
│                                                                           │
│   ┌───────────────────────────────────────────────────────────────────┐   │
│   │                      Routing (routes/api.php)                     │   │
│   └─────────────────────────────────┬─────────────────────────────────┘   │
│                                     ▼                                     │
│   ┌───────────────────────────────────────────────────────────────────┐   │
│   │           Middleware (Sanctum Auth, Role/Permission Guard)        │   │
│   └─────────────────────────────────┬─────────────────────────────────┘   │
│                                     ▼                                     │
│   ┌───────────────────────────────────────────────────────────────────┐   │
│   │        FormRequest Layer (Validation đầu vào & Authorization)     │   │
│   └─────────────────────────────────┬─────────────────────────────────┘   │
│                                     ▼                                     │
│   ┌───────────────────────────────────────────────────────────────────┐   │
│   │            Controller Layer (app/Http/Controllers/Api/V1)         │   │
│   └───────────────────┬───────────────────────────────┬───────────────┘   │
│                       ▼                               ▼                   │
│   ┌───────────────────────────────────┐   ┌───────────────────────────┐   │
│   │    Service Layer (Business Logic) │   │ Policy Layer (Auth Check) │   │
│   └───────────────────┬───────────────┘   └───────────────────────────┘   │
│                       ▼                                                   │
│   ┌───────────────────────────────────────────────────────────────────┐   │
│   │                 Eloquent ORM Models & Enums (PHP 8.1+)            │   │
│   └───────────────────┬───────────────────────────────┬───────────────┘   │
│                       ▼                               ▼                   │
│   ┌───────────────────────────────────┐   ┌───────────────────────────┐   │
│   │ API Resource (Transform JSON Out) │   │  Audit & Logging Service  │   │
│   └───────────────────────────────────┘   └───────────────────────────┘   │
└──────────────────────────────────────┬────────────────────────────────────┘
                                       │
                                       ▼
┌───────────────────────────────────────────────────────────────────────────┐
│                      DATABASE: MySQL 9.1 / 8.0                            │
│  - Storage Engine: InnoDB (Hỗ trợ ACID Transactions và Foreign Keys)      │
│  - Character Set: utf8mb4_unicode_ci (Hỗ trợ đầy đủ tiếng Việt có dấu)    │
│  - Private File Storage: storage/app/private/ (Quản lý tệp nhạy cảm)      │
└───────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Kiến trúc Frontend (React.js)

### 2.1. Lựa chọn công nghệ & Thư viện
* **Core:** React 18.x, JavaScript thuần (ES6+, JSX).
* **Build tool:** Vite (tốc độ khởi động siêu tốc, HMR tức thì, tối ưu dung lượng bundle production).
* **UI Framework:** **Ant Design (antd v5)**: Thư viện chuẩn mực nhất cho hệ thống quản trị hành chính doanh nghiệp, hỗ trợ phong phú các component như `Table` (phân trang, lọc, sắp xếp nhiều cột), `Form` (validation), `Modal`, `Steps` (tiến trình hồ sơ), `Timeline` (lịch sử thực hiện), `Tag`, `Badge`.
* **State Management & Caching:** **TanStack Query v5 (React Query)** kết hợp **Zustand / Context API**: Tự động quản lý caching dữ liệu máy chủ, tái đồng bộ ngầm (revalidation), chống gọi API dư thừa.
* **Routing:** **React Router DOM v6**: Quản lý tuyến đường và bảo vệ trang theo trạng thái đăng nhập và vai trò người dùng (`ProtectedRoute`).
* **HTTP Client:** **Axios**: Đính kèm tự động Bearer Token vào Request Header, bắt lỗi HTTP tập trung qua Response Interceptors.

### 2.2. Tổ chức mã nguồn theo Feature-based (Tính năng độc lập)
Cấu trúc Frontend không chia theo loại tệp (controllers, views) mà chia theo **chức năng nghiệp vụ cụ thể (Features)**:
* `src/features/[feature_name]/`:
  * `pages/`: Các màn hình view chính của feature đó (ví dụ: `MeetingListPage.jsx`, `MeetingDetailPage.jsx`).
  * `components/`: Các component giao diện nhỏ phục vụ riêng cho feature (ví dụ: `BallotCard.jsx`, `AgendaList.jsx`).
  * `services/`: Các hàm gọi API tương ứng của feature (ví dụ: `meetingService.js`, `votingService.js`).
  * `hooks/`: Custom hooks riêng của feature (nếu có).

### 2.3. Quản lý trạng thái và Tuyến đường bảo vệ (Protected Routes)
* Mọi tuyến đường yêu cầu xác thực đều được bọc bởi component `<ProtectedRoute />`:
  1. Kiểm tra trạng thái đăng nhập từ Auth Context: Nếu chưa đăng nhập $\rightarrow$ chuyển hướng về `/login`.
  2. Kiểm tra danh sách quyền/vai trò: Nếu người dùng không sở hữu vai trò hợp lệ $\rightarrow$ chuyển hướng về trang `/403-forbidden`.

---

## 3. Kiến trúc Backend (Laravel)

### 3.1. Phân tầng kiến trúc (Layered Architecture)
Hệ thống áp dụng mô hình phân tầng chặt chẽ nhằm tránh việc nhồi nhét mã nguồn vào Controller ("Fat Controller"):
1. **Routing Layer (`routes/api.php`):** Định nghĩa các endpoint RESTful, gom nhóm theo phiên bản `/api/v1/` và bảo vệ bằng middleware `auth:sanctum`.
2. **Validation Layer (`app/Http/Requests/`):** Mỗi request gửi dữ liệu lên (POST/PUT/PATCH) bắt buộc có một class `FormRequest` riêng biệt. Tầng này kiểm tra tính toàn vẹn kiểu dữ liệu, độ dài, định dạng file, kiểm tra tồn tại khóa ngoại trước khi dữ liệu chạm tới Controller.
3. **Controller Layer (`app/Http/Controllers/Api/V1/`):** Chỉ đóng vai trò điều phối: nhận dữ liệu đã được validate từ `FormRequest`, gọi tầng `Service` xử lý và trả kết quả về thông qua `API Resource`. Controller không chứa câu lệnh SQL phức tạp hay logic nghiệp vụ cốt lõi.
4. **Service Layer (`app/Services/`):** Chứa toàn bộ nghiệp vụ thực tế của hệ thống:
   * `VotingService`: Quản lý mở/đóng biểu quyết, kiểm tra quyền bỏ phiếu, mã hóa lá phiếu kín, tính toán kết quả và tỷ lệ thông qua.
   * `ResolutionService`: Quản lý quy tắc sinh số nghị quyết, gắn tệp đã ký duyệt.
   * `TaskService`: Quản lý phân rã đầu việc, tính toán cảnh báo quá hạn, nghiệm thu minh chứng.
   * `DossierService`: Quản lý luồng thẩm tra hồ sơ trình.
5. **Authorization Policy Layer (`app/Policies/`):** Kiểm soát quyền chi tiết đến từng bản ghi dữ liệu (ví dụ: chỉ Thư ký phụ trách mới được thẩm tra, chỉ Thành viên có mặt mới được bỏ phiếu).
6. **Data Access & Model Layer (`app/Models/`):** Sử dụng Eloquent ORM, khai báo quan hệ ràng buộc (Relationships), các Scope truy vấn và cast dữ liệu sang **PHP Enums**.
7. **Transformation Layer (`app/Http/Resources/`):** Định dạng dữ liệu JSON trả về cho Frontend, ẩn giấu các trường nhạy cảm (mật khẩu, hash phiếu kín) và chuẩn hóa tên trường theo quy chuẩn `camelCase` hoặc `snake_case`.

### 3.2. Quản lý trạng thái bằng PHP Enums (PHP 8.1+)
Hệ thống sử dụng Backed Enums chuẩn hóa để loại bỏ hoàn toàn các chuỗi ký tự cứng ("magic strings") trong mã nguồn:
* `RoleEnum`: `ADMIN`, `CHAIR`, `SECRETARY`, `MEMBER`, `UNIT_REP`, `EXECUTING_UNIT`, `RECTOR_OFFICE`.
* `DossierStatus`: `DRAFT`, `SUBMITTED`, `IN_REVIEW`, `APPROVED_FOR_MEETING`, `REVISION_REQUIRED`, `REJECTED`.
* `MeetingStatus`: `PREPARING`, `SCHEDULED`, `IN_PROGRESS`, `COMPLETED`, `CANCELLED`.
* `VotingType`: `OPEN`, `SECRET`.
* `VotingStatus`: `PENDING`, `OPENED`, `CLOSED`.
* `TaskStatus`: `PENDING`, `IN_PROGRESS`, `UNDER_REVIEW`, `COMPLETED`, `OVERDUE`.

---

## 4. Luồng xác thực, phân quyền và bảo mật

### 4.1. Luồng Xác thực (Authentication Flow)
1. Người dùng nhập thông tin đăng nhập trên Frontend React.
2. Axios gửi `POST /api/v1/auth/login` kèm email/username và mật khẩu.
3. Backend kiểm tra tài khoản, xác thực mật khẩu với hàm `Hash::check()` (bcrypt).
4. Nếu hợp lệ: Cấp phát Bearer Token thông qua **Laravel Sanctum** với thời gian sống xác định (ví dụ: 8 giờ).
5. Frontend lưu trữ Token trong bộ nhớ an toàn (LocalStorage / SessionStorage) và cập nhật Header mặc định cho Axios:
   `Authorization: Bearer <token>`
6. Khi người dùng bấm Đăng xuất (`POST /api/v1/auth/logout`), backend thu hồi và xóa token hiện tại khỏi bảng `personal_access_tokens`.

### 4.2. Luồng Phân quyền (Authorization Flow)
* **Tầng 1 (Role-level Guard):** Sử dụng Middleware kiểm tra vai trò người dùng trước khi cho phép Request đi vào Controller.
* **Tầng 2 (Resource-level Policy):** Trong Controller/Service, sử dụng các Policy method (ví dụ: `$this->authorize('castVote', [$voting, $user])`) để kiểm tra quyền hạn cụ thể trên từng thực thể dữ liệu (người dùng có phải thành viên có mặt không, phiên biểu quyết đã đóng chưa).

---

## 5. Luồng Quản lý Tải tệp & Bảo mật Tài liệu (File Storage Architecture)

Do đặc thù các tài liệu Hội đồng trường chứa thông tin bảo mật, quy hoạch và nhân sự cấp cao:
* **Không lưu trữ công khai (No Public Symlink):** Toàn bộ tệp hồ sơ trình, tài liệu phiên họp nội bộ, dự thảo nghị quyết và minh chứng nhiệm vụ được lưu trữ tại thư mục bảo vệ:
  `backend/storage/app/private/`
* **Quy trình tải lên (Upload):**
  1. Frontend gửi `multipart/form-data` qua Axios.
  2. Backend kiểm tra loại MIME hợp lệ (PDF, DOC, DOCX, XLSX, PNG, JPG) và kích thước tối đa (50MB).
  3. Tệp được lưu với tên ngẫu nhiên (UUID băm) để chống tấn công đoán tên tệp (Path Traversal).
  4. Thông tin tệp gốc (tên gốc, đường dẫn lưu trữ, định dạng, kích thước, người tải) được lưu vào bảng dữ liệu tương ứng.
* **Quy trình tải về (Secure Download/Stream):**
  1. Người dùng bấm xem/tải tệp trên giao diện React.
  2. Request gửi tới route bảo vệ: `GET /api/v1/documents/{id}/download`.
  3. Controller kiểm tra Token và gọi Policy để xác thực:
     * Nếu là tài liệu nội bộ HĐT: Người dùng bắt buộc phải là Thành viên HĐT, Thư ký hoặc Chủ tịch.
     * Khách mời cố tình tải tệp nội bộ sẽ lập tức nhận mã lỗi `403 Forbidden`.
  4. Nếu hợp lệ: Backend đọc tệp từ ổ đĩa và trả về qua luồng `Storage::download()` hoặc `response()->file()` kèm Header kiểm soát cache.

---

## 6. Luồng Xử lý Lỗi & Chuẩn hóa phản hồi API (Error Handling)

### 6.1. Cấu trúc phản hồi API thống nhất (Unified Response Envelope)
Mọi phản hồi từ backend bắt buộc tuân thủ cấu trúc chuẩn:
```json
{
  "success": true,
  "message": "Nội dung thông báo kết quả",
  "data": { ... },
  "errors": null,
  "meta": {
    "page": 1,
    "per_page": 15,
    "total": 120
  }
}
```

### 6.2. Mã lỗi HTTP chuẩn mực
* `200 OK`: Truy vấn hoặc cập nhật thành công.
* `201 Created`: Tạo mới thành công tài nguyên.
* `204 No Content`: Xóa thành công, không có dữ liệu trả về.
* `400 Bad Request`: Yêu cầu không hợp lệ về mặt logic nghiệp vụ.
* `401 Unauthorized`: Chưa đăng nhập hoặc token đã hết hạn.
* `403 Forbidden`: Đã đăng nhập nhưng không có quyền thực hiện tác vụ này (ví dụ: Khách mời cố tình biểu quyết).
* `404 Not Found`: Không tìm thấy tài nguyên trong hệ thống.
* `422 Unprocessable Entity`: Lỗi kiểm thực dữ liệu (Form validation fails). Trả về chi tiết lỗi theo từng trường:
  ```json
  {
    "success": false,
    "message": "Dữ liệu gửi lên không hợp lệ.",
    "errors": {
      "title": ["Tiêu đề hồ sơ không được để trống."],
      "lead_department_id": ["Đơn vị chủ trì không tồn tại."]
    },
    "data": null
  }
  ```
* `500 Internal Server Error`: Lỗi phát sinh từ máy chủ. Ghi chi tiết log vào file máy chủ, chỉ trả thông báo chung an toàn cho người dùng cuối.

---

## 7. Nguyên tắc Tổ chức Code & Đảm bảo Chất lượng (Clean Code Guidelines)

1. **Tuân thủ quy ước mã nguồn:**
   * **PHP/Laravel:** Tuân thủ chặt chẽ tiêu chuẩn **PSR-12** về phong cách lập trình PHP. Tên bảng số nhiều (`dossiers`), tên Model số ít (`Dossier`), tên cột dạng `snake_case` (`lead_department_id`), tên Controller dạng `PascalCase` (`MeetingController`).
   * **JavaScript/React:** Tuân thủ chuẩn **ESLint** và **Airbnb JavaScript Style Guide**. Tên Component dạng `PascalCase` (`MeetingCard.jsx`), tên hàm và biến dạng `camelCase` (`fetchMeetingList`), tên hằng số dạng `UPPER_SNAKE_CASE` (`MAX_FILE_SIZE`).
2. **Nguyên tắc SOLID:** Tách biệt trách nhiệm các lớp (Single Responsibility), không viết các hàm dài quá 50 dòng, tách logic phức tạp vào các helper hoặc service chuyên dụng.
3. **Quản lý phiên bản Git:**
   * Nhánh chính: `main` (mã nguồn ổn định).
   * Đặt tên commit theo chuẩn Conventional Commits: `feat:`, `fix:`, `docs:`, `refactor:`, `test:`, `chore:`.
