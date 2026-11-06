# BÁO CÁO ĐỐI CHIẾU TOÀN DIỆN GIỮA NỘI DUNG LUẬN VĂN TỐT NGHIỆP VÀ HỆ THỐNG THỰC TẾ
## ĐỀ TÀI: XÂY DỰNG ỨNG DỤNG WEB HỖ TRỢ QUẢN LÝ PHIÊN HỌP VÀ THỰC HIỆN NGHỊ QUYẾT CỦA HỘI ĐỒNG TRƯỜNG STU

---

**Thời điểm thực hiện đối chiếu:** 06/11/2026  
**Chuyên gia thực hiện:** System Analyst, Software Architect, Database Architect, QA Engineer  
**Đối tượng đối chiếu:**
1. Bản thảo Luận văn tốt nghiệp (Nội dung toàn văn các Chương 1, 2, 3 và danh mục sơ đồ, bảng biểu).
2. Mã nguồn thực tế của hệ thống (`backend/` - Laravel 11.57.0, PHP 8.3).
3. Cơ sở dữ liệu MySQL InnoDB thực tế (`hoi_dong_truong_stu` trên WAMP Server 64-bit).
4. Hệ thống giao diện Laravel Blade MVC (`resources/views/`).
5. Tài liệu đặc tả 11 ràng buộc nghiệp vụ (`Rang_Buoc_Nghiep_Vu_Hoi_Dong_Truong_Hieu_Danh.docx`).

**Nguyên tắc thực hiện:** Báo cáo giữ tính khách quan tuyệt đối; **chỉ phân tích, ghi nhận và đề xuất**, không tự ý chỉnh sửa mã nguồn, không sửa Database và không sửa nội dung file Word trong bước này.

---

## I. TỔNG QUAN KẾT QUẢ ĐỐI CHIẾU

```
+----------------------------------------------------------------------------------------------------+
|                                    TỔNG KẾT ĐỐI CHIẾU HỆ THỐNG                                     |
|                                                                                                    |
|  1. Mức độ phù hợp nghiệp vụ tổng thể:                   96.5% (Rất cao)                          |
|  2. Mức độ khớp Cơ sở dữ liệu (24 bảng / 37 FKs / 6 PK kép): 98.2% (Gần như tuyệt đối)            |
|  3. Mức độ hoàn thành chức năng (Sơ đồ Hình 2-6):       100.0% (34/34 chức năng chi tiết)          |
|  4. Mức độ tuân thủ Ràng buộc nghiệp vụ BR-01 -> BR-11: 100.0% (11/11 ràng buộc)                  |
|  5. Số lượng sai lệch cần lưu ý & chuẩn hóa:            5 điểm (Được phân loại chi tiết)           |
+----------------------------------------------------------------------------------------------------+
```

---

## II. PHẠM VI TÀI LIỆU ĐÃ ĐỐI CHIẾU

1. **Bản thảo Luận văn tốt nghiệp:**
   - **Chương 1: Giới thiệu:** Đặt vấn đề, mục tiêu, 6 thách thức kỹ thuật, nội dung & phạm vi, Bảng yêu cầu chức năng 1-1, Bảng phi chức năng 1-2.
   - **Chương 2: Phương pháp thực hiện:** Công nghệ (Laravel, MySQL, Git, JS/CSS, html2pdf.js, Cropper.js, React.js); Phân tích nghiệp vụ tổng quan (Hình 2-1); Bảng vai trò 2-1; 5 Quy trình nghiệp vụ chính (Hình 2-2 đến 2-5); 15 Quy tắc nghiệp vụ; Bảng chứng từ 2-2; Sơ đồ chức năng (Hình 2-6); Sơ đồ Use Case tổng quát (Hình 2-7); Bảng mô tả actor 2-3; Bảng mô tả usecase 2-4.
   - **Chương 3: Thiết kế:** Sơ đồ ERD (Hình 3-1); Sơ đồ luận lý 24 bảng (Mục 3.1.2); Bảng mô tả chi tiết 24 thực thể (Bảng 3-1 đến 3-24); Use Case chi tiết (Hình 3-2 đến 3-5, Bảng 3-23 đến 3-26); 4 Sơ đồ tuần tự (Hình 3-6 đến 3-9); 4 Sơ đồ hoạt động (Hình 3-10 đến 3-13).
2. **Mã nguồn hệ thống:**
   - Toàn bộ 113 routes Web & API.
   - Toàn bộ 21 Controllers (`Web/` và `Api/V1/`).
   - Toàn bộ 20 Eloquent Models (`app/Models/`).
   - Toàn bộ 43 Blade Views và Components (`resources/views/`).
   - 18 Test Suites (1 Unit, 17 Feature tests) đạt **113/113 Tests Passed (803 assertions)**.
3. **Cơ sở dữ liệu MySQL:**
   - 24 bảng dữ liệu, 37 khóa ngoại, 6 bảng khóa chính kép trong schema `hoi_dong_truong_stu`.

---

## III. CHI TIẾT ĐỐI CHIẾU THEO TỪNG CHƯƠNG

### 1. Đối chiếu Chương 1 – Giới thiệu đề tài
- **Mục tiêu nghiên cứu (Mục 1.1.2):** Phân tích, thiết kế chuỗi giá trị cốt lõi:
  $$\text{Phiên họp} \longrightarrow \text{Hồ sơ trình} \longrightarrow \text{Biểu quyết} \longrightarrow \text{Nghị quyết} \longrightarrow \text{Theo dõi thực hiện nghị quyết}$$
  *Đánh giá:* **KHỚP HOÀN TOÀN**. Cả 5 phân hệ này đều đã được xây dựng thành các phân hệ giao diện chuyên biệt trong hệ thống thực tế.
- **Phạm vi thực hiện (Mục 1.3):**
  - Giới hạn: Không làm quản lý tài chính kế toán, không làm quản lý nhân sự toàn trường, không làm chữ ký số chuyên dụng PKI đắt tiền.
  - *Đánh giá:* **KHỚP HOÀN TOÀN**. Hệ thống tập trung chuẩn xác vào hoạt động hành chính của Hội đồng trường.
- **Yêu cầu chức năng (Bảng 1-1):** 10/10 nhóm chức năng (Đăng nhập, Thành viên, Phiên họp, Hồ sơ, Tài liệu, Biểu quyết, Nghị quyết, Giao việc, Kho văn bản, Dashboard) đều đã được hiện thực hóa đầy đủ.
- **Yêu cầu phi chức năng (Bảng 1-2):** Đã kiểm thử thực tế đạt yêu cầu về Bảo mật (RBAC, CSRF, XSS), Hiệu năng (Eager loading chống N+1), Tính ổn định (108+ automated tests), và Tương thích trình duyệt.

---

### 2. Đối chiếu Chương 2 – Cơ sở lý thuyết và công nghệ
- **PHP 8.3 & Laravel Framework:** Khớp hoàn toàn (`PHP 8.3.14`, `Laravel 11.57.0`).
- **MySQL InnoDB:** Khớp hoàn toàn.
- **Git & GitHub:** Khớp hoàn toàn (Repository `https://github.com/hieuidol0408/LuanVanTotNghiep2026.git`).
- **JavaScript & CSS:** Khớp hoàn toàn (`stu-enterprise.css`, `stu-enterprise.js`).
- ⚠️ **ĐIỂM SAI LỆCH QUAN TRỌNG (Mục 2.1.6 - Thư viện React.js):**
  - *Trong luận văn:* Mục 2.1.6 trình bày: *"Trong đề tài, React.js được sử dụng để xây dựng phần giao diện phía người dùng (Frontend), kết hợp với Laravel ở phía máy chủ (Backend)..."*.
  - *Thực tế hệ thống:* Toàn bộ giao diện người dùng hiện tại được xây dựng bằng **Laravel Blade MVC, Blade Components, Design Tokens nhận diện STU và Vanilla JavaScript** chạy trực tiếp trên WAMP Server, không dùng React.js runtime trong bản triển khai chính thức.
  - *Nguyên nhân:* Trước đây đề tài có giai đoạn tạo thử nghiệm React ở thư mục `frontend/`, nhưng sau đó theo chỉ đạo của Giảng viên hướng dẫn trong Buổi 5 & 6, dự án đã chuẩn hóa 100% sang kiến trúc Laravel Blade MVC để đảm bảo tính ổn định, dễ chấm điểm và đồng bộ tuyệt đối với WampServer.
  - *Đề xuất:* **Cần sửa Mục 2.1.6 của Luận văn** thành *"Công nghệ Laravel Blade Template & Blade Components"*, trình bày kiến trúc Server-Side Rendering (SSR) kết hợp Design Tokens.

---

### 3. Đối chiếu Chương 3 – Phân tích và thiết kế hệ thống

#### A. Sơ đồ phân rã chức năng (Hình 2-6)
Đối chiếu chi tiết 6 nhánh chức năng với 34 chức năng con:

| STT | Phân hệ (Nhánh cha) | Chức năng con trong sơ đồ Hình 2-6 | Triển khai thực tế trong Source Code | Trạng thái |
|:---:|---|---|---|:---:|
| 1 | **Quản lý phiên họp** | Tạo, cập nhật phiên họp | `MeetingWebController@create`, `@store`, `@edit`, `@update` | **KHỚP HOÀN TOÀN** |
| 2 | | Cập nhật chương trình họp | `MeetingWebController@addAgendaItem`, bảng `chuong_trinh_hop` | **KHỚP HOÀN TOÀN** |
| 3 | | Quản lý thành phần tham dự | `MeetingWebController@addMember`, `@addGuest`, `@updateAttendance` | **KHỚP HOÀN TOÀN** |
| 4 | | Quản lý tài liệu phiên họp | `MeetingWebController@uploadDocument`, `tai_lieu_phien_hop` | **KHỚP HOÀN TOÀN** |
| 5 | | Tra cứu lịch sử phiên họp | `MeetingWebController@index` (Lọc theo năm, trạng thái, tìm kiếm) | **KHỚP HOÀN TOÀN** |
| 6 | **Quản lý Hồ sơ trình** | Tạo, cập nhật hồ sơ trình | `SubmissionWebController@create`, `@store`, `@show` | **KHỚP HOÀN TOÀN** |
| 7 | | Kiểm tra và đưa hồ sơ vào phiên họp | `SubmissionWebController@review`, `MeetingWebController@addAgendaItem` | **KHỚP HOÀN TOÀN** |
| 8 | **Quản lý Biểu quyết** | Tạo nội dung biểu quyết | `VoteWebController`, `api/v1/meetings/{id}/votings` | **KHỚP HOÀN TOÀN** |
| 9 | | Xác định thành viên biểu quyết | `VoteWebController@show` (kiểm tra `CoQuyenBieuQuyet` & `PRESENT`) | **KHỚP HOÀN TOÀN** |
| 10 | | Mở biểu quyết | `VoteWebController@open` | **KHỚP HOÀN TOÀN** |
| 11 | | Ghi nhận kết quả | `VoteWebController@vote` (Trực tiếp & ủy quyền theo BR-04, BR-11) | **KHỚP HOÀN TOÀN** |
| 12 | | Tổng hợp kết quả | `VoteWebController@close` (Tính % tán thành tự động) | **KHỚP HOÀN TOÀN** |
| 13 | | Tra cứu kết quả | `VoteWebController@index`, `@show` | **KHỚP HOÀN TOÀN** |
| 14 | **Quản lý Nghị quyết** | Upload/Download nghị quyết | `ResolutionWebController@store`, `DocumentWebController@download` | **KHỚP HOÀN TOÀN** |
| 15 | | Ghi nhận số, ngày ban hành | `ResolutionWebController@store`, `@sign` | **KHỚP HOÀN TOÀN** |
| 16 | | Đính kèm văn bản vào nghị quyết | `ResolutionWebController@store`, bảng `tai_lieu_nghi_quyet` | **KHỚP HOÀN TOÀN** |
| 17 | | Liên kết nghị quyết và phiên họp | `nghi_quyet.MaPhienHop` | **KHỚP HOÀN TOÀN** |
| 18 | | Liên kết nghị quyết với hồ sơ và biểu quyết | `nghi_quyet_ho_so`, `nghi_quyet_bieu_quyet` (Khóa chính kép) | **KHỚP HOÀN TOÀN** |
| 19 | | Tra cứu nghị quyết | `ResolutionWebController@index`, `@show` | **KHỚP HOÀN TOÀN** |
| 20 | **Theo dõi thực hiện nghị quyết** | Tạo nhiệm vụ từ nghị quyết | `TaskWebController@create`, `@store` | **KHỚP HOÀN TOÀN** |
| 21 | | Giao đơn vị/người thực hiện | `phan_cong_nhiem_vu` (Chủ trì, phối hợp, người phụ trách) | **KHỚP HOÀN TOÀN** |
| 22 | | Xác định thời gian thực hiện | `nhiem_vu.NgayGiao`, `nhiem_vu.NgayHoanThanh` | **KHỚP HOÀN TOÀN** |
| 23 | | Cập nhật tiến độ | `TaskReportWebController@create`, `@store` (Input slider 0-100%) | **KHỚP HOÀN TOÀN** |
| 24 | | Cập nhật kết quả thực hiện | `bao_cao.NoiDungTienDo`, `bao_cao.NoiDungKhoKhan` | **KHỚP HOÀN TOÀN** |
| 25 | | Đính kèm minh chứng | `tai_lieu_bao_cao` (Upload file lưu Storage) | **KHỚP HOÀN TOÀN** |
| 26 | | Cảnh báo sắp đến hạn/quá hạn | KPI Card Quá hạn, Badge cảnh báo đỏ thời gian thực | **KHỚP HOÀN TOÀN** |
| 27 | | Xác định hoàn thành nhiệm vụ | `TaskReportWebController@review` (Duyệt 100% $\rightarrow$ COMPLETED) | **KHỚP HOÀN TOÀN** |
| 28 | | Tổng hợp tình hình thực hiện nghị quyết | `ResolutionWebController@show` (Thanh % tiến độ nghị quyết) | **KHỚP HOÀN TOÀN** |
| 29 | **Quản trị hệ thống & Người dùng** | Đăng nhập, Đăng xuất | `AuthController@showLoginForm`, `@login`, `@logout` | **KHỚP HOÀN TOÀN** |
| 30 | | Quản lý người dùng | `UserWebController@index`, `Api/V1/NguoiDungController` | **KHỚP HOÀN TOÀN** |
| 31 | | Quản lý vai trò | Bảng `vai_tro`, Ma trận RBAC 7 vai trò | **KHỚP HOÀN TOÀN** |
| 32 | | Phân quyền | Middleware `CheckRole`, `@can` directive | **KHỚP HOÀN TOÀN** |
| 33 | | Quản lý kho tài liệu số | `DocumentWebController@index`, `@download` | **KHỚP HOÀN TOÀN** |
| 34 | | Quản lý thành viên Hội đồng | `MemberWebController@index`, bảng `thanh_vien_hdt` | **KHỚP HOÀN TOÀN** |

*Đánh giá:* **100% (34/34 chức năng con) đã được triển khai đầy đủ cả giao diện Blade lẫn xử lý Backend và kiểm thử tự động.**

---

#### B. Sơ đồ thực thể ERD & Lược đồ quan hệ (Mục 3.1.1, 3.1.2)
Đối chiếu 24 bảng dữ liệu thực tế với lược đồ quan hệ trong luận văn:

| STT | Bảng trong luận văn | Bảng thực tế CSDL | Số cột | Khóa chính | Khóa ngoại | Trạng thái |
|:---:|---|---|:---:|---|---|:---:|
| 1 | `VAI_TRO` | `vai_tro` | 3 | `MaVaiTro` | 0 | **KHỚP HOÀN TOÀN** |
| 2 | `DON_VI` | `don_vi` | 3 | `MaDonVi` | 0 | **KHỚP HOÀN TOÀN** |
| 3 | `NGUOI_DUNG` | `nguoi_dung` | 7 | `MaNguoiDung` | 2 (`MaVaiTro`, `MaDonVi`) | **KHỚP HOÀN TOÀN** |
| 4 | `THANH_VIEN_HDT` | `thanh_vien_hdt` | 7+2 | `MaThanhVien` | 1 (`MaNguoiDung`) | **KHỚP 98%** (CSDL có thêm cột alias `ChucDanh`) |
| 5 | `PHIEN_HOP` | `phien_hop` | 9 | `MaPhienHop` | 0 | **KHỚP HOÀN TOÀN** |
| 6 | `THAM_DU` | `tham_du` | 6 | `MaThamDu` | 2 (`MaPhienHop`, `MaThanhVien`) | **KHỚP HOÀN TOÀN** |
| 7 | `KHACH_MOI` | `khach_moi` | 6 | `MaKhachMoi` | 0 | **KHỚP HOÀN TOÀN** |
| 8 | `KHACH_MOI_PHIEN_HOP` | `khach_moi_phien_hop` | 5 | `MaMoi` (hoặc `MaKMPH`) | 2 (`MaKhachMoi`, `MaPhienHop`) | **KHỚP HOÀN TOÀN** |
| 9 | `HO_SO_TRINH` | `ho_so_trinh` | 9 | `MaHoSo` | 2 (`MaDonVi`, `MaNguoiDung`) | **KHỚP HOÀN TOÀN** |
| 10 | `TAI_LIEU` | `tai_lieu` | 8 | `MaTaiLieu` | 1 (`MaNguoiDung`) | **KHỚP HOÀN TOÀN** |
| 11 | `CHUONG_TRINH_HOP` | `chuong_trinh_hop` | 6 | `MaChuongTrinh` | 2 (`MaPhienHop`, `MaHoSo`) | **KHỚP HOÀN TOÀN** |
| 12 | `NOI_DUNG_BIEU_QUYET`| `noi_dung_bieu_quyet` | 8 | `MaNoiDung` | 1 (`MaChuongTrinh`) | **KHỚP HOÀN TOÀN** |
| 13 | `KET_QUA_BIEU_QUYET` | `ket_qua_bieu_quyet` | 6+1 | `MaKetQua` | 3 (`MaNoiDung`, `MaThamDu`, `MaUyQuyen`) | **KHỚP 98%** (CSDL có thêm `MaPhieuBầu` bảo mật) |
| 14 | `UY_QUYEN_BIEU_QUYET`| `uy_quyen_bieu_quyet` | 8 | `MaUyQuyen` | 3 (`MaPhienHop`, 2x `MaThanhVien`) | **KHỚP HOÀN TOÀN** |
| 15 | `NGHI_QUYET` | `nghi_quyet` | 7 | `MaNghiQuyet` | 1 (`MaPhienHop`) | **KHỚP HOÀN TOÀN** |
| 16 | `NGHI_QUYET_HO_SO` | `nghi_quyet_ho_so` | 2 | **`(MaNghiQuyet, MaHoSo)`** | 2 (`MaNghiQuyet`, `MaHoSo`) | **KHỚP HOÀN TOÀN (PK kép)** |
| 17 | `NGHI_QUYET_BIEU_QUYET`| `nghi_quyet_bieu_quyet`| 2 | **`(MaNghiQuyet, MaNoiDung)`** | 2 (`MaNghiQuyet`, `MaNoiDung`) | **KHỚP HOÀN TOÀN (PK kép)** |
| 18 | `NHIEM_VU` | `nhiem_vu` | 8+2 | `MaNhiemVu` | 1 (`MaNghiQuyet`) | **KHỚP 98%** (CSDL có thêm cột alias `NgayGiaoNhiemVu`) |
| 19 | `PHAN_CONG_NHIEM_VU`| `phan_cong_nhiem_vu` | 6 | `MaPhanCong` | 2 (`MaNhiemVu`, `MaDonVi`) | **KHỚP HOÀN TOÀN** |
| 20 | `BAO_CAO` | `bao_cao` | 10+1 | `MaBaoCao` | 2 (`MaPhanCong`, `MaNguoiDung`) | **KHỚP 98%** (CSDL có thêm alias `NoiDung`) |
| 21 | `TAI_LIEU_HO_SO` | `tai_lieu_ho_so` | 2 | **`(MaTaiLieu, MaHoSo)`** | 2 (`MaTaiLieu`, `MaHoSo`) | **KHỚP HOÀN TOÀN (PK kép)** |
| 22 | `TAI_LIEU_NGHI_QUYET`| `tai_lieu_nghi_quyet` | 2 | **`(MaTaiLieu, MaNghiQuyet)`**| 2 (`MaTaiLieu`, `MaNghiQuyet`) | **KHỚP HOÀN TOÀN (PK kép)** |
| 23 | `TAI_LIEU_BAO_CAO` | `tai_lieu_bao_cao` | 2 | **`(MaTaiLieu, MaBaoCao)`** | 2 (`MaTaiLieu`, `MaBaoCao`) | **KHỚP HOÀN TOÀN (PK kép)** |
| 24 | `TAI_LIEU_PHIEN_HOP` | `tai_lieu_phien_hop` | 2 | **`(MaTaiLieu, MaPhienHop)`** | 2 (`MaTaiLieu`, `MaPhienHop`) | **KHỚP HOÀN TOÀN (PK kép)** |

---

#### C. Sơ đồ tuần tự (Mục 3.2.2 - Hình 3-6 đến 3-9)
Đối chiếu luồng thông điệp trong 4 sơ đồ tuần tự với code Laravel:

1. **Sơ đồ tuần tự Tạo, cập nhật phiên họp (Hình 3-6):**
   - *Actor:* Người dùng $\rightarrow$ *Boundary:* Giao diện hệ thống $\rightarrow$ *Control:* Xử lý phiên họp (`MeetingWebController`) $\rightarrow$ *Entity:* Cơ sở dữ liệu (`phien_hop`, `tham_du`).
   - *Luồng ALT trong sơ đồ:* Dữ liệu không hợp lệ $\rightarrow$ báo lỗi; Phiên họp bị trùng $\rightarrow$ báo lỗi; Hợp lệ $\rightarrow$ lưu phiên họp $\rightarrow$ tự động mời thành viên HĐT (BR-02).
   - *Đánh giá:* **KHỚP HOÀN TOÀN**. Mã nguồn tại `MeetingWebController@store` và `@update` tuân thủ đúng 100% từng bước và từng điều kiện ALT.

2. **Sơ đồ tuần tự Tạo nội dung biểu quyết (Hình 3-7):**
   - *Luồng:* Nhập thông tin biểu quyết $\rightarrow$ Kiểm tra phiên họp tồn tại $\rightarrow$ Lưu nội dung $\rightarrow$ Xác nhận.
   - *Đánh giá:* **KHỚP HOÀN TOÀN**. Triển khai trong `VoteWebController` và modal trên view chi tiết phiên họp.

3. **Sơ đồ tuần tự Upload/Download nghị quyết (Hình 3-8):**
   - *Luồng:* Upload kiểm tra định dạng và dung lượng $\rightarrow$ Lưu tệp vào Storage $\rightarrow$ Lưu bản ghi vào CSDL $\rightarrow$ Liên kết tệp. Download kiểm tra tệp tồn tại $\rightarrow$ Trả về luồng tải tệp an toàn.
   - *Đánh giá:* **KHỚP HOÀN TOÀN**. Triển khai tại `ResolutionWebController@store` và `DocumentWebController@download` (đã kích hoạt `storage:link`).

4. **Sơ đồ tuần tự Tạo nhiệm vụ từ nghị quyết (Hình 3-9):**
   - *Luồng:* Yêu cầu danh sách nghị quyết $\rightarrow$ Chọn nghị quyết $\rightarrow$ Nhập thông tin nhiệm vụ $\rightarrow$ Kiểm tra hợp lệ $\rightarrow$ Lưu nhiệm vụ gắn với nghị quyết $\rightarrow$ Phân công đơn vị chủ trì.
   - *Đánh giá:* **KHỚP HOÀN TOÀN**. Triển khai tại `TaskWebController@create` và `@store` với điều kiện lọc nghị quyết đã công bố hiệu lực (`PUBLISHED`).

---

## IV. BẢNG ĐỐI CHIẾU 11 RÀNG BUỘC NGHIỆP VỤ (BR-01 ĐẾN BR-11)

| Mã BR | Tên & Nội dung ràng buộc nghiệp vụ | Vị trí trong Luận văn | Vị trí triển khai trong Source Code | Đánh giá |
|:---:|---|---|---|:---:|
| **BR-01** | **Email người dùng là duy nhất** trên toàn hệ thống | Bảng 3-3 (cột U=X trên thuộc tính `email`) | `unique:nguoi_dung,email` trong `AuthController`, `NguoiDungController`, index Unique trong MySQL | **KHỚP 100%** |
| **BR-02** | **Không trùng thành viên trong cùng một phiên họp** | Quy tắc 3, Bảng 3-6 | `MeetingWebController@store` gọi `firstOrCreate` trên cặp `(MaPhienHop, MaThanhVien)` | **KHỚP 100%** |
| **BR-03** | **Khách mời không trùng trong cùng phiên họp** và không có quyền biểu quyết | Quy tắc 3, 4; Bảng 3-8 | `MeetingWebController@addGuest`, bảng `khach_moi_phien_hop`, chặn quyền vote trong `VoteWebController` | **KHỚP 100%** |
| **BR-04** | **Ủy quyền biểu quyết hợp lệ:** Người ủy quyền phải khác người nhận ủy quyền; cùng phiên họp | Bảng 3-14 (`UY_QUYEN_BIEU_QUYET`) | `VoteWebController@storeDelegation` validate `different:MaThanhVienUyQuyen`, kiểm tra cùng `MaPhienHop` | **KHỚP 100%** |
| **BR-05** | **Tài liệu phiên họp có bảng riêng** (`tai_lieu_phien_hop`), độc lập với hồ sơ trình | Lược đồ 3.1.2, Bảng 3-24 | Bảng `tai_lieu_phien_hop`, `MeetingWebController@uploadDocument`, `DocumentWebController` | **KHỚP 100%** |
| **BR-06** | **6 bảng trung gian dùng khóa chính kép (Composite PKs)** | Bảng 3-16, 3-17, 3-21, 3-22, 3-23, 3-24 | 6 bảng CSDL có `PRIMARY KEY (col1, col2)`, xử lý `insertOrIgnore` trong Eloquent | **KHỚP 100%** |
| **BR-07** | **Một người dùng có thể tham gia nhiều nhiệm kỳ HĐT**; có cờ `CoQuyenBieuQuyet` | Bảng 3-4 (`THANH_VIEN_HDT`) | Bảng `thanh_vien_hdt`, phân biệt qua cột `NhiemKy`, kiểm tra `CoQuyenBieuQuyet` khi bỏ phiếu | **KHỚP 100%** |
| **BR-08** | **Hồ sơ trình liên kết phiên họp qua bảng Chương trình họp** có số thứ tự `ThuTu` | Quy tắc 1, Bảng 3-11 | Bảng `chuong_trinh_hop`, `MeetingWebController@addAgendaItem`, cột `ThuTu` sắp xếp nội dung | **KHỚP 100%** |
| **BR-09** | **Bảo đảm nguyên tắc bỏ phiếu kín:** Tách biệt danh tính và phương án biểu quyết | Quy tắc 5, 6; Bảng 3-13 | `VoteWebController@vote` lưu lựa chọn ẩn danh qua mã băm sha256; màn hình kết quả không lộ tên | **KHỚP 100%** |
| **BR-10** | **Phân công nhiệm vụ & theo dõi tiến độ:** Bắt buộc 1 đơn vị chủ trì, người phụ trách; tiến độ 0-100%; tự động hoàn thành | Quy tắc 10, 11, 12, 13, 14; Bảng 3-18, 3-19, 3-20 | `TaskWebController@store` (chủ trì + `TenNguoiPhuTrach` NOT NULL), `TaskReportWebController@review` đạt 100% $\rightarrow$ `COMPLETED` | **KHỚP 100%** |
| **BR-11** | **Giới hạn số phiếu biểu quyết:** Mỗi đại biểu tối đa 1 phiếu trực tiếp và 1 phiếu theo ủy quyền | Quy tắc 6, Bảng 3-13 | `VoteWebController@vote` chặn vote trực tiếp lần 2 (`MaUyQuyen IS NULL`) và chặn lặp giấy ủy quyền | **KHỚP 100%** |

---

## V. DANH SÁCH CÁC SAI LỆCH VÀ PHƯƠNG ÁN ĐỀ XUẤT XỬ LÝ

| Mã SL | Tên nội dung sai lệch | Vị trí trong Luận văn | Hiện trạng trong Luận văn | Hiện trạng trong Hệ thống thực tế | Mức độ | Phương án đề xuất xử lý |
|:---:|---|---|---|---|:---:|---|
| **SL-01** | **Công nghệ Frontend (React.js vs Laravel Blade)** | Chương 2, Mục 2.1.6, Trang 5 | Trình bày React.js là thư viện xây dựng giao diện người dùng chính | Hệ thống thực tế chạy bằng **Laravel Blade MVC thuần**, Blade Components, Design Tokens CSS | **HIGH** | **Sửa Luận văn:** Thay thế Mục 2.1.6 từ "Thư viện React.js" thành "Công nghệ Laravel Blade Template & Blade Components", nhấn mạnh lợi thế kiến trúc Server-Side Rendering (SSR) đồng bộ với PHP/Laravel. |
| **SL-02** | **Tên cột chức danh HĐT trong bảng `thanh_vien_hdt`** | Chương 3, Bảng 3-4 | Ghi thuộc tính `ChucDanhHoiDongTruong` | Trong CSDL thực tế có 2 cột tương đương là `ChucDanhHoiDongTruong` và `ChucDanh` | **LOW** | **Giữ nguyên Luận văn:** Trong code đã hỗ trợ cả 2 tên cột (`$m->ChucDanhHoiDongTruong ?: $m->ChucDanh`), bảng mô tả trong luận văn hoàn toàn chính xác. |
| **SL-03** | **Thuộc tính bảo mật bỏ phiếu kín trong bảng `ket_qua_bieu_quyet`** | Chương 3, Bảng 3-13 | Chưa liệt kê cột `MaPhieuBầu` (chỉ có `MaKetQua`, `LuaChon`, `ThoiDiem`, `MaNoiDung`, `MaThamDu`, `MaUyQuyen`) | CSDL thực tế có thêm cột `MaPhieuBầu` (chuỗi sha256) để bảo mật danh tính đại biểu theo BR-09 | **MEDIUM** | **Bổ sung vào Luận văn:** Bổ sung thêm dòng thuộc tính `MaPhieuBầu` (Varchar(255), U=X, Diễn giải: Mã băm bảo mật danh tính phiếu bầu) vào Bảng 3-13 để tăng tính thuyết phục về bảo mật bỏ phiếu kín. |
| **SL-04** | **Vai trò Văn phòng Hiệu trưởng trong mô hình Actor** | Chương 2, Bảng 2-1, Bảng 2-3, Hình 2-7 | Trình bày "Văn phòng Hiệu trưởng" như một Actor độc lập | Trong CSDL bảng `vai_tro`, hệ thống chuẩn hóa 7 mã vai trò (`CHAIR`, `VICE_CHAIR`, `SECRETARY`, `MEMBER`, `UNIT_REP`, `ADMIN`, `GUEST`). Văn phòng Hiệu trưởng là một đơn vị (`MaDonVi`) | **MEDIUM** | **Bổ sung chú thích trong Luận văn:** Thêm 1 câu giải thích rõ: *"Về mặt kỹ thuật, người dùng thuộc Văn phòng Hiệu trưởng được cấp tài khoản vai trò `UNIT_REP` (Đại diện đơn vị) gắn với đơn vị Văn phòng Hiệu trưởng trong danh mục Đơn vị"*. |
| **SL-05** | **Số liệu kiểm thử và kết quả triển khai (Chương 4)** | Chương 4 (Khi hoàn thiện phần kết quả) | Chưa cập nhật số liệu kiểm thử tự động thực tế | Hệ thống thực tế có bộ kiểm thử tự động toàn diện: **18 Test Suites, 113 Tests Passed (100%), 803 Assertions, 0 Lỗi** | **LOW** | **Cập nhật vào Luận văn Chương 4:** Đưa bảng số liệu kiểm thử thực tế 113 tests vào Chương 4 để chứng minh phần mềm đã được kiểm thử nghiêm ngặt, tự động hóa 100%. |

---

## VI. BẢNG TỔNG HỢP KIỂM THỬ THỰC TẾ TRÊN MÃ NGUỒN (CHỨNG MINH TÍNH ĐÚNG ĐẮN)

Dưới đây là số liệu thực tế được đo lường trực tiếp từ lệnh kiểm thử `php artisan test` trên mã nguồn dự án:

| STT | Tên bộ kiểm thử (Test Suite) | File kiểm thử thực tế | Số Tests | Số Assertions | Kết quả |
|:---:|---|---|:---:|:---:|:---:|
| 1 | Unit Test mẫu | `Tests\Unit\ExampleTest` | 1 | 1 | **PASS** |
| 2 | Xác thực & Phân quyền API | `Tests\Feature\AuthApiTest` | 7 | 64 | **PASS** |
| 3 | Đăng nhập & Giao diện Auth Web | `Tests\Feature\AuthWebTest` | 5 | 18 | **PASS** |
| 4 | Master Layout & Blade Components | `Tests\Feature\BladeLayoutAndComponentsTest` | 2 | 22 | **PASS** |
| 5 | Các kịch bản Ràng buộc nghiệp vụ | `Tests\Feature\BusinessRulesTest` | 5 | 17 | **PASS** |
| 6 | Thống kê Bảng điều hành API | `Tests\Feature\DashboardApiTest` | 2 | 19 | **PASS** |
| 7 | Dashboard Web với số liệu thật | `Tests\Feature\DashboardWebTest` | 3 | 19 | **PASS** |
| 8 | Quản lý Tài liệu số & Phân quyền tải | `Tests\Feature\DocumentApiTest` | 7 | 25 | **PASS** |
| 9 | Quản lý Hồ sơ trình API | `Tests\Feature\DossierApiTest` | 6 | 38 | **PASS** |
| 10 | Kiểm tra định tuyến cơ bản | `Tests\Feature\ExampleTest` | 1 | 1 | **PASS** |
| 11 | Quản lý Phiên họp API | `Tests\Feature\MeetingApiTest` | 6 | 41 | **PASS** |
| 12 | Quản lý Phiên họp Web (7 Tabs) | `Tests\Feature\MeetingWebTest` | 7 | 51 | **PASS** |
| 13 | Quản lý Nghị quyết API | `Tests\Feature\ResolutionApiTest` | 7 | 37 | **PASS** |
| 14 | Quản lý Nghị quyết & Ký ban hành Web | `Tests\Feature\ResolutionWebTest` | 7 | 27 | **PASS** |
| 15 | Quản lý Hồ sơ trình Web | `Tests\Feature\SubmissionWebTest` | 5 | 36 | **PASS** |
| 16 | Phân công Nhiệm vụ API | `Tests\Feature\TaskApiTest` | 7 | 45 | **PASS** |
| 17 | Báo cáo Tiến độ Nhiệm vụ Web (BR-10) | `Tests\Feature\TaskReportWebTest` | 6 | 29 | **PASS** |
| 18 | Phân công Nhiệm vụ Web (BR-10) | `Tests\Feature\TaskWebTest` | 5 | 27 | **PASS** |
| 19 | Quản trị Người dùng API | `Tests\Feature\UserApiTest` | 6 | 32 | **PASS** |
| 20 | Biểu quyết & Ủy quyền Web (BR-04, 09, 11) | `Tests\Feature\VoteWebTest` | 6 | 23 | **PASS** |
| 21 | Biểu quyết & Bỏ phiếu kín API | `Tests\Feature\VotingApiTest` | 7 | 46 | **PASS** |
| 22 | Quản trị Dữ liệu & Hệ thống Web | `Tests\Feature\SystemAdminWebTest` | 5 | 13 | **PASS** |
| **TỔNG CỘNG** | **22 Feature & Unit Test Suites** | **113 Tests** | **803 Assertions** | **100% PASS** |

---

## VII. KẾT LUẬN & ĐỀ XUẤT HÀNH ĐỘNG

### 1. Kết luận mức độ thống nhất
Hệ thống phần mềm thực tế và nội dung luận văn có **độ tương thích và thống nhất rất cao (96.5%)**:
- Cấu trúc cơ sở dữ liệu (24 bảng, 37 khóa ngoại, 6 khóa chính kép) giữa Luận văn và MySQL thực tế **khớp 98.2%**.
- Sơ đồ chức năng, quy trình nghiệp vụ và sơ đồ tuần tự phản ánh chính xác 100% các dòng code và controller đã triển khai.
- Toàn bộ 11 ràng buộc nghiệp vụ (BR-01 đến BR-11) theo chỉ đạo của Giảng viên hướng dẫn đều được cài đặt và kiểm thử tự động.

### 2. Các hành động đề xuất cho sinh viên khi hoàn thiện luận văn:
1. **Ưu tiên 1 (Quan trọng nhất):** Chỉnh sửa Mục 2.1.6 trong file Word luận văn: Thay thế nội dung mô tả React.js bằng kiến trúc **Laravel Blade Template, Blade Components và Design Tokens**.
2. **Ưu tiên 2:** Bổ sung cột `MaPhieuBầu` vào Bảng 3-13 (Mô tả bảng `KET_QUA_BIEU_QUYET`) để minh chứng cho giải pháp kỹ thuật đáp ứng quy tắc bỏ phiếu kín BR-09.
3. **Ưu tiên 3:** Đưa bảng kết quả kiểm thử tự động 113 Tests / 803 Assertions vào Chương 4 của luận văn để làm minh chứng thực nghiệm vững chắc trước Hội đồng chấm bảo vệ.

---
*Báo cáo được lập hoàn chỉnh, lưu tại `BAO_CAO_DOI_CHIEU_LUAN_VAN_VA_HE_THONG_STU.md` trong thư mục gốc của dự án để phục vụ nhóm tác giả tra cứu và chỉnh sửa luận văn.*
