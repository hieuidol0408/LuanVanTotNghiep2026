# HỆ THỐNG QUẢN LÝ PHIÊN HỌP VÀ THỰC HIỆN NGHỊ QUYẾT HỘI ĐỒNG TRƯỜNG STU

**Đề tài Luận văn Tốt nghiệp:** “Xây dựng ứng dụng Web hỗ trợ quản lý phiên họp và thực hiện nghị quyết của Hội đồng Trường STU”  
**Đơn vị đào tạo:** Trường Đại học Công nghệ Sài Gòn (STU)  
**Kho mã nguồn GitHub:** [https://github.com/hieuidol0408/LuanVanTotNghiep2026](https://github.com/hieuidol0408/LuanVanTotNghiep2026)  
**Kiến trúc:** Monorepo gồm Frontend React.js và Backend Laravel RESTful API kết nối MySQL trên WampServer 64-bit.

---

## 1. YÊU CẦU MÔI TRƯỜNG PHÁT TRIỂN

Dự án được tối ưu hóa và kiểm thử nghiêm ngặt trên môi trường WampServer 64-bit chạy trên hệ điều hành Windows:

- **Hệ điều hành:** Windows 10/11 64-bit.
- **Web Server & CSDL:** WampServer 64-bit (phiên bản cài đặt tại `D:\WAMP\` hoặc `C:\wamp64\`).
- **PHP:** Phiên bản `>= 8.2` (Khuyến nghị **PHP 8.3.14** đi kèm WampServer), kích hoạt các extension: `pdo_mysql`, `openssl`, `mbstring`, `curl`.
- **Cơ sở dữ liệu:** **MySQL 9.1+** (hoặc MySQL 8.0+ / MariaDB 10.4+), hỗ trợ `utf8mb4`.
- **Node.js & Quản lý gói:** **Node.js `>= 20.x`** và `npm `>= 10.x`.
- **Composer:** Phiên bản `>= 2.7` (Dự án đã tích hợp sẵn wrapper tại `scripts/composer.bat`).

---

## 2. CẤU TRÚC THƯ MỤC DỰ ÁN

```text
LVTN/
├── backend/                  # Mã nguồn Backend Laravel 11 (RESTful API /api/v1)
│   ├── app/                  # Controllers, Models, Middleware, Policies
│   ├── config/               # Cấu hình hệ thống và dịch vụ
│   ├── database/             # Migrations, Seeders, Factories
│   ├── routes/               # api.php, web.php, console.php
│   ├── tests/                # PHPUnit Test Suites (Unit & Feature)
│   └── .env.example          # Tệp mẫu cấu hình môi trường backend
├── frontend/                 # Mã nguồn Frontend React 19 + Vite 8
│   ├── src/
│   │   ├── components/       # UI Components dùng chung (Button, Modal, DataTable,...)
│   │   ├── features/         # Các module nghiệp vụ (auth, dashboard, meetings,...)
│   │   ├── routes/           # AppRoutes, ProtectedRoute (RBAC Guard)
│   │   └── services/         # apiClient, authService, meetingService
│   └── .env.example          # Tệp mẫu cấu hình môi trường frontend
├── docs/                     # Tài liệu kỹ thuật & đặc tả nghiệp vụ luận văn
│   ├── requirements.md       # Đặc tả yêu cầu phần mềm
│   ├── business-rules.md     # Quy tắc nghiệp vụ Hội đồng trường STU
│   ├── architecture.md       # Tài liệu kiến trúc hệ thống
│   ├── database-design.md    # Thiết kế CSDL chi tiết
│   └── api-contracts.md      # Quy chuẩn RESTful API Contracts
├── scripts/                  # Bộ công cụ tự động hóa chạy PHP/Composer/Artisan từ Wamp
│   ├── php.bat
│   ├── composer.bat
│   └── artisan.bat
├── .gitignore                # Ràng buộc loại trừ bí mật, cache và vendor
└── README.md                 # Hướng dẫn cài đặt và vận hành hệ thống
```

---

## 3. HƯỚNG DẪN CÀI ĐẶT BACKEND (LARAVEL 11)

### Bước 3.1: Cài đặt thư viện phụ thuộc
Mở PowerShell tại thư mục gốc dự án:
```powershell
cd backend
# Sử dụng script wrapper tích hợp sẵn trỏ đến PHP WampServer
..\scripts\composer.bat install
```
*(Hoặc dùng lệnh `composer install` nếu máy bạn đã có Composer toàn cục).*

### Bước 3.2: Thiết lập tệp môi trường `.env`
Sao chép cấu hình mẫu và tạo mã bảo mật ứng dụng:
```powershell
cp .env.example .env
..\scripts\artisan.bat key:generate
```

### Bước 3.3: Cấu hình Cơ sở dữ liệu MySQL
1. Khởi động WampServer (đảm bảo biểu tượng khay hệ thống màu xanh lá cây).
2. Mở phpMyAdmin (`http://localhost/phpmyadmin`) hoặc MySQL CLI:
   ```sql
   CREATE DATABASE IF NOT EXISTS hoi_dong_truong_stu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Cập nhật các thông số kết nối trong tệp `backend/.env`:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hoi_dong_truong_stu
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### Bước 3.4: Chạy Migration và Seed dữ liệu Demo
Khởi tạo cấu trúc bảng CSDL và 7 tài khoản mẫu:
```powershell
..\scripts\artisan.bat migrate:fresh --seed
```

---

## 4. TÀI KHOẢN MẪU KIỂM THỬ HỆ THỐNG (DEMO SEED ACCOUNTS)

Mật khẩu mặc định cho tất cả tài khoản môi trường phát triển: **`stu@2026`**

| STT | Họ và tên | Email đăng nhập | Vai trò (Role Key) | Đơn vị trực thuộc |
| :---: | :--- | :--- | :--- | :--- |
| 1 | Quản trị viên Kỹ thuật | `admin@stu.edu.vn` | `ADMIN` | Phòng Công nghệ thông tin |
| 2 | PGS.TS. Trần Văn Chủ Tịch | `chutich_hdt@stu.edu.vn` | `CHAIR` | Hội đồng trường STU |
| 3 | ThS. Nguyễn Thị Thư Ký | `thuky_hdt@stu.edu.vn` | `SECRETARY` | Văn phòng Hội đồng trường |
| 4 | TS. Lê Văn Thành Viên | `thanhvien_hdt@stu.edu.vn` | `MEMBER` | Khoa Công nghệ Thông tin |
| 5 | Văn phòng Hiệu trưởng STU | `vphieutruong@stu.edu.vn` | `RECTOR_OFFICE` | Ban Giám hiệu - VP Hiệu trưởng |
| 6 | ThS. Hoàng Đơn Vị Trình | `daotao@stu.edu.vn` | `UNIT_REP` | Phòng Đào tạo |
| 7 | KS. Phạm Đơn Vị Thi Hành | `cntt@stu.edu.vn` | `EXECUTING_UNIT` | Trung tâm Dữ liệu & CNTT |

---

## 5. HƯỚNG DẪN CÀI ĐẶT FRONTEND (REACT.JS + VITE)

### Bước 5.1: Cài đặt thư viện Node.js
Mở terminal tại thư mục `frontend`:
```powershell
cd frontend
npm install
```

### Bước 5.2: Thiết lập tệp môi trường Frontend
Tạo tệp `frontend/.env` từ tệp mẫu:
```powershell
cp .env.example .env
```
Nội dung tệp `frontend/.env`:
```dotenv
VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1
```

---

## 6. CHẠY ỨNG DỤNG TRONG MÔI TRƯỜNG LOCAL

Để chạy toàn bộ hệ thống, mở 2 cửa sổ PowerShell:

### Cửa sổ 1: Chạy Backend API Server (Port 8000)
```powershell
cd d:\WAMP\www\LVTN\backend
..\scripts\artisan.bat serve --host=127.0.0.1 --port=8000
```
- Endpoint gốc API: `http://127.0.0.1:8000/api/v1`
- Trạng thái kiểm tra sức khỏe: `http://127.0.0.1:8000/api/v1/health` (nếu có) hoặc gọi `POST /api/v1/auth/login`.

### Cửa sổ 2: Chạy Frontend Vite Dev Server (Port 5173)
```powershell
cd d:\WAMP\www\LVTN\frontend
npm run dev -- --host 127.0.0.1 --port 5173
```
- Mở trình duyệt và truy cập: `http://127.0.0.1:5173`

---

## 7. CHẠY BỘ KIỂM THỬ TỰ ĐỘNG (TEST SUITES)

### 7.1. Chạy Backend PHPUnit Tests
Tại thư mục gốc dự án:
```powershell
& "scripts/artisan.bat" test
```
*Kết quả:* Toàn bộ **14 tests (85 assertions)** kiểm tra xác thực Sanctum, phân quyền RBAC và 5 kịch bản nghiệp vụ (A–E) đạt kết quả 100% Pass.

### 7.2. Chạy Frontend Linting & Build Test
Tại thư mục `frontend`:
```powershell
npm run lint    # Rà soát tĩnh toàn bộ cú pháp (0 lỗi)
npm run build   # Đóng gói kiểm tra cấu trúc bundle hoàn chỉnh
```

---

## 8. CÁC ĐẶC TẢ NGHIỆP VỤ ĐÃ HIỆN THỰC
- **Kịch bản A:** Đơn vị trình nộp tờ trình $\rightarrow$ Thư ký HĐT thẩm tra $\rightarrow$ Đưa vào chương trình phiên họp.
- **Kịch bản B:** Triệu tập phiên họp $\rightarrow$ Điểm danh $\rightarrow$ Kiểm soát túc số $\ge 2/3$ $\rightarrow$ Biểu quyết kín (ẩn danh bằng mã băm SHA-256 cử tri, chặn khách mời bỏ phiếu).
- **Kịch bản C:** Ban hành Nghị quyết chính thức $\rightarrow$ Phân rã thành các nhiệm vụ cụ thể.
- **Kịch bản D:** Giao nhiệm vụ cho đơn vị $\rightarrow$ Báo cáo tiến độ kèm minh chứng $\rightarrow$ Tự động cảnh báo quá hạn (`OVERDUE`).
- **Kịch bản E:** Kiểm soát an ninh 403 Forbidden, ngăn chặn biểu quyết trùng, bảo mật tài liệu nội bộ `INTERNAL`.
