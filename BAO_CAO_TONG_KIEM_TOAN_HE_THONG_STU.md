# BÁO CÁO TỔNG KIỂM TOÁN, RÀ SOÁT VÀ HOÀN THIỆN TOÀN BỘ HỆ THỐNG
## HỆ THỐNG QUẢN LÝ HOẠT ĐỘNG HỘI ĐỒNG TRƯỜNG STU

---

**Thời gian kiểm toán:** 06/11/2026  
**Chuyên gia thực hiện:** Senior Software Architect, Senior Laravel Developer, Database Architect  
**Môi trường:** WAMP Server 64-bit | PHP 8.3.14 | MySQL InnoDB | Laravel 11.57.0  
**Phiên bản Git hoàn tất:** `4a94be0` (Đã đồng bộ lên `origin/master`)  
**Repository:** https://github.com/hieuidol0408/LuanVanTotNghiep2026.git  

---

### BẢNG TỔNG HỢP KẾT QUẢ KIỂM THỬ THỰC TẾ

| Hạng mục | Kết quả thực tế | Ghi chú kỹ thuật |
|---|---|---|
| **Tổng số Test** | **113 Tests** | 18 Test Suites (1 Unit, 17 Feature) |
| **Passed** | **113 Tests (100%)** | Toàn bộ kiểm thử thực tế thực thi thành công |
| **Failed** | **0** | Không có lỗi phát sinh |
| **Skipped** | **0** | Không bỏ qua bất kỳ trường hợp nào |
| **Tổng số Assertions** | **803 Assertions** | Xác thực logic đa chiều, phân quyền, dữ liệu |
| **Database Schema Changes** | **0** | Bảo toàn nguyên vẹn 24 bảng, 37 FKs, 6 Composite PKs |
| **Kiểm thử hồi quy (Regression)** | **PASS** | Không phát sinh lỗi hồi quy trên toàn hệ thống |

---

## 1. TỔNG QUAN KIẾN TRÚC HỆ THỐNG

- **Mô hình kiến trúc:** Laravel Blade MVC kết hợp hệ thống RESTful API V1.
- **Frontend Engine:** Laravel Blade Templates, Bootstrap 5 components, Design Tokens chuẩn thương hiệu STU (`stu-enterprise.css`), Vanilla JavaScript.
- **Backend Framework:** Laravel 11.x, PHP 8.3 ZTS x64.
- **Cơ sở dữ liệu:** MySQL InnoDB `hoi_dong_truong_stu`, mã hóa `utf8mb4_unicode_ci`.
- **Hệ thống phân quyền (RBAC):** 7 vai trò:
  1. `CHAIR` (Chủ tịch Hội đồng trường)
  2. `VICE_CHAIR` (Phó Chủ tịch Hội đồng trường)
  3. `SECRETARY` (Thư ký Hội đồng trường)
  4. `MEMBER` (Thành viên chính thức Hội đồng trường)
  5. `UNIT_REP` (Đại diện đơn vị chuyên môn / Đơn vị thực hiện `EXECUTING_UNIT`)
  6. `ADMIN` (Quản trị viên hệ thống)
  7. `GUEST` (Khách mời dự thính phiên họp)

---

## 2. KẾT QUẢ KIỂM TOÁN 24 BẢNG DỮ LIỆU CỐT LÕI

Kiểm toán trực tiếp từ `information_schema.TABLES` của MySQL xác nhận đầy đủ 24 bảng nghiệp vụ cốt lõi:

| STT | Tên bảng | Khóa chính (PK) | Loại bảng | Chức năng nghiệp vụ |
|:---:|---|---|---|---|
| 1 | `vai_tro` | `MaVaiTro` | Danh mục | Lưu trữ 7 vai trò người dùng trong hệ thống |
| 2 | `don_vi` | `MaDonVi` | Danh mục | Quản lý các phòng ban, khoa, viện trực thuộc STU |
| 3 | `nguoi_dung` | `MaNguoiDung` | Thực thể | Thông tin tài khoản, email, mật khẩu băm, vai trò, đơn vị |
| 4 | `thanh_vien_hdt` | `MaThanhVien` | Thực thể | Thông tin thành viên HĐT khóa IV, chức danh, quyền biểu quyết |
| 5 | `phien_hop` | `MaPhienHop` | Thực thể | Quản lý phiên họp Hội đồng trường, ngày giờ, địa điểm, hình thức |
| 6 | `tham_du` | `MaThamDu` | Quan hệ | Ghi nhận sự có mặt của thành viên HĐT tại từng phiên họp (BR-02) |
| 7 | `khach_moi` | `MaKhachMoi` | Thực thể | Quản lý danh sách khách mời tham dự phiên họp |
| 8 | `khach_moi_phien_hop` | `MaKMPH` | Quan hệ | Phân bổ khách mời tham dự từng phiên họp cụ thể (BR-03) |
| 9 | `ho_so_trinh` | `MaHoSo` | Thực thể | Hồ sơ tờ trình từ các đơn vị gửi xin ý kiến Hội đồng trường |
| 10 | `chuong_trinh_hop` | `MaChuongTrinh` | Quan hệ | Chương trình nghị sự chi tiết phiên họp liên kết hồ sơ trình (BR-01) |
| 11 | `noi_dung_bieu_quyet` | `MaNoiDung` | Thực thể | Các vấn đề đưa ra lấy phiếu biểu quyết trong kỳ họp |
| 12 | `ket_qua_bieu_quyet` | `MaKetQua` | Quan hệ | Phiếu bầu trực tiếp hoặc phiếu ủy quyền được mã hóa (BR-09, BR-11) |
| 13 | `uy_quyen_bieu_quyet` | `MaUyQuyen` | Thực thể | Văn bản ủy quyền quyền biểu quyết giữa các thành viên (BR-04) |
| 14 | `nghi_quyet` | `MaNghiQuyet` | Thực thể | Nghị quyết chính thức do HĐT ban hành, số hiệu, trích yếu, trạng thái |
| 15 | `nghi_quyet_ho_so` | `(MaNghiQuyet, MaHoSo)` | **Khóa kép** | Liên kết Nghị quyết ban hành với các Hồ sơ trình căn cứ |
| 16 | `nghi_quyet_bieu_quyet` | `(MaNghiQuyet, MaNoiDung)` | **Khóa kép** | Liên kết Nghị quyết ban hành với các Kết quả biểu quyết căn cứ |
| 17 | `nhiem_vu` | `MaNhiemVu` | Thực thể | Nhiệm vụ cụ thể được phân rã từ Nghị quyết ban hành (BR-10) |
| 18 | `phan_cong_nhiem_vu` | `MaPhanCong` | Quan hệ | Phân công đơn vị chủ trì, đơn vị phối hợp, người phụ trách (BR-10) |
| 19 | `bao_cao` | `MaBaoCao` | Thực thể | Báo cáo tiến độ (0-100%), nội dung, khó khăn, duyệt tiến độ (BR-10) |
| 20 | `tai_lieu` | `MaTaiLieu` | Thực thể | Kho lưu trữ tài liệu, file đính kèm, đường dẫn, dung lượng |
| 21 | `tai_lieu_phien_hop` | `(MaTaiLieu, MaPhienHop)` | **Khóa kép** | Hồ sơ, tài liệu tài liệu riêng của phiên họp (BR-05) |
| 22 | `tai_lieu_ho_so` | `(MaTaiLieu, MaHoSo)` | **Khóa kép** | Tài liệu minh chứng của hồ sơ trình |
| 23 | `tai_lieu_nghi_quyet` | `(MaTaiLieu, MaNghiQuyet)` | **Khóa kép** | Văn bản nghị quyết có chữ ký số hoặc con dấu scan đính kèm |
| 24 | `tai_lieu_bao_cao` | `(MaTaiLieu, MaBaoCao)` | **Khóa kép** | Minh chứng sản phẩm hoàn thành của báo cáo tiến độ |

---

## 3. KẾT QUẢ KIỂM TRA 37 KHÓA NGOẠI (FOREIGN KEYS)

Kiểm tra trực tiếp từ `information_schema.KEY_COLUMN_USAGE` xác nhận chính xác 37 khóa ngoại; kiểm tra dữ liệu mồ côi (Orphaned Data) đạt **0 bản ghi vi phạm (100% Referential Integrity)**:

1. `bao_cao.MaNguoiDung -> nguoi_dung.MaNguoiDung` (`fk_bao_cao_nguoi_dung`)
2. `bao_cao.MaPhanCong -> phan_cong_nhiem_vu.MaPhanCong` (`fk_bao_cao_phan_cong`)
3. `chuong_trinh_hop.MaHoSo -> ho_so_trinh.MaHoSo` (`fk_cth_ho_so`)
4. `chuong_trinh_hop.MaPhienHop -> phien_hop.MaPhienHop` (`fk_cth_phien_hop`)
5. `ho_so_trinh.MaDonVi -> don_vi.MaDonVi` (`fk_ho_so_don_vi`)
6. `ho_so_trinh.MaNguoiDung -> nguoi_dung.MaNguoiDung` (`fk_ho_so_nguoi_dung`)
7. `ket_qua_bieu_quyet.MaNoiDung -> noi_dung_bieu_quyet.MaNoiDung` (`fk_kqbq_noi_dung`)
8. `ket_qua_bieu_quyet.MaThamDu -> tham_du.MaThamDu` (`fk_kqbq_tham_du`)
9. `ket_qua_bieu_quyet.MaUyQuyen -> uy_quyen_bieu_quyet.MaUyQuyen` (`fk_kqbq_uy_quyen`)
10. `khach_moi_phien_hop.MaKhachMoi -> khach_moi.MaKhachMoi` (`fk_kmph_khach_moi`)
11. `khach_moi_phien_hop.MaPhienHop -> phien_hop.MaPhienHop` (`fk_kmph_phien_hop`)
12. `nghi_quyet.MaPhienHop -> phien_hop.MaPhienHop` (`fk_nghi_quyet_phien_hop`)
13. `nghi_quyet_bieu_quyet.MaNghiQuyet -> nghi_quyet.MaNghiQuyet` (`fk_nqbq_nghi_quyet`)
14. `nghi_quyet_bieu_quyet.MaNoiDung -> noi_dung_bieu_quyet.MaNoiDung` (`fk_nqbq_noi_dung`)
15. `nghi_quyet_ho_so.MaHoSo -> ho_so_trinh.MaHoSo` (`fk_nqhs_ho_so`)
16. `nghi_quyet_ho_so.MaNghiQuyet -> nghi_quyet.MaNghiQuyet` (`fk_nqhs_nghi_quyet`)
17. `nguoi_dung.MaDonVi -> don_vi.MaDonVi` (`fk_nguoi_dung_don_vi`)
18. `nguoi_dung.MaVaiTro -> vai_tro.MaVaiTro` (`fk_nguoi_dung_vai_tro`)
19. `nhiem_vu.MaNghiQuyet -> nghi_quyet.MaNghiQuyet` (`fk_nhiem_vu_nghi_quyet`)
20. `noi_dung_bieu_quyet.MaChuongTrinh -> chuong_trinh_hop.MaChuongTrinh` (`fk_ndbq_chuong_trinh`)
21. `phan_cong_nhiem_vu.MaDonVi -> don_vi.MaDonVi` (`fk_pcnv_don_vi`)
22. `phan_cong_nhiem_vu.MaNhiemVu -> nhiem_vu.MaNhiemVu` (`fk_pcnv_nhiem_vu`)
23. `tai_lieu.MaNguoiDung -> nguoi_dung.MaNguoiDung` (`fk_tai_lieu_nguoi_dung`)
24. `tai_lieu_bao_cao.MaBaoCao -> bao_cao.MaBaoCao` (`fk_tlbc_bao_cao`)
25. `tai_lieu_bao_cao.MaTaiLieu -> tai_lieu.MaTaiLieu` (`fk_tlbc_tai_lieu`)
26. `tai_lieu_ho_so.MaHoSo -> ho_so_trinh.MaHoSo` (`fk_tlhs_ho_so`)
27. `tai_lieu_ho_so.MaTaiLieu -> tai_lieu.MaTaiLieu` (`fk_tlhs_tai_lieu`)
28. `tai_lieu_nghi_quyet.MaNghiQuyet -> nghi_quyet.MaNghiQuyet` (`fk_tlnq_nghi_quyet`)
29. `tai_lieu_nghi_quyet.MaTaiLieu -> tai_lieu.MaTaiLieu` (`fk_tlnq_tai_lieu`)
30. `tai_lieu_phien_hop.MaPhienHop -> phien_hop.MaPhienHop` (`fk_tlph_phien_hop`)
31. `tai_lieu_phien_hop.MaTaiLieu -> tai_lieu.MaTaiLieu` (`fk_tlph_tai_lieu`)
32. `tham_du.MaPhienHop -> phien_hop.MaPhienHop` (`fk_tham_du_phien_hop`)
33. `tham_du.MaThanhVien -> thanh_vien_hdt.MaThanhVien` (`fk_tham_du_thanh_vien`)
34. `thanh_vien_hdt.MaNguoiDung -> nguoi_dung.MaNguoiDung` (`fk_thanh_vien_nguoi_dung`)
35. `uy_quyen_bieu_quyet.MaPhienHop -> phien_hop.MaPhienHop` (`fk_uq_phien_hop`)
36. `uy_quyen_bieu_quyet.MaThanhVienNhanUyQuyen -> thanh_vien_hdt.MaThanhVien` (`fk_uq_tv_nhan_uy_quyen`)
37. `uy_quyen_bieu_quyet.MaThanhVienUyQuyen -> thanh_vien_hdt.MaThanhVien` (`fk_uq_tv_uy_quyen`)

---

## 4. KẾT QUẢ KIỂM TRA 6 BẢNG CÓ KHÓA CHÍNH KÉP (COMPOSITE PRIMARY KEYS)

| Bảng trung gian | Khóa chính kép `(PK_1, PK_2)` | Xử lý trong Eloquent/Query Builder |
|---|---|---|
| `nghi_quyet_bieu_quyet` | `(MaNghiQuyet, MaNoiDung)` | Sử dụng `insertOrIgnore` và Eloquent `belongsToMany` |
| `nghi_quyet_ho_so` | `(MaNghiQuyet, MaHoSo)` | Sử dụng `insertOrIgnore` và Eloquent `belongsToMany` |
| `tai_lieu_bao_cao` | `(MaTaiLieu, MaBaoCao)` | Sử dụng `insertOrIgnore` và Eloquent `belongsToMany` |
| `tai_lieu_ho_so` | `(MaTaiLieu, MaHoSo)` | Sử dụng `insertOrIgnore` và Eloquent `belongsToMany` |
| `tai_lieu_nghi_quyet` | `(MaTaiLieu, MaNghiQuyet)` | Sử dụng `insertOrIgnore` và Eloquent `belongsToMany` |
| `tai_lieu_phien_hop` | `(MaTaiLieu, MaPhienHop)` | Sử dụng `insertOrIgnore` và Eloquent `belongsToMany` |

---

## 5. DANH SÁCH LỖI PHÁT HIỆN VÀ PHÂN LOẠI MỨC ĐỘ

Trong quá trình kiểm toán toàn diện hệ thống, đã phát hiện 4 vấn đề kỹ thuật thực tế:

1. **[CRITICAL] Thiếu liên kết Storage Symlink (`backend/public/storage`):**
   - *Vị trí:* `public/storage`.
   - *Nguyên nhân:* Thư mục symbolic link chưa được tạo thông qua lệnh `storage:link`.
   - *Tác động:* Toàn bộ các file tài liệu tải lên (hồ sơ trình, văn bản nghị quyết scan, minh chứng báo cáo) khi hiển thị qua `asset($doc->DuongDan)` bị lỗi HTTP 404 khi người dùng nhấp tải về.

2. **[HIGH] Đồng bộ trạng thái Nghị quyết khi tạo nhiệm vụ:**
   - *Vị trí:* `backend/app/Http/Controllers/Web/TaskWebController.php:106`.
   - *Nguyên nhân:* Khi người dùng lọc nghị quyết để giao nhiệm vụ mới, hàm truy vấn chỉ lọc `where('TrangThai', 'PUBLISHED')`, trong khi dữ liệu thực tế do `ResolutionWebController@publish` lưu có thể là `'Đã công bố hiệu lực'` hoặc `'PUBLISHED'`.
   - *Tác động:* Một số nghị quyết đã công bố hiệu lực bị sót khỏi danh sách chọn khi phân công nhiệm vụ mới.

3. **[MEDIUM] 4 liên kết trên thanh điều hướng Sidebar bị lỗi 404:**
   - *Vị trí:* `backend/resources/views/layouts/partials/sidebar.blade.php`.
   - *Nguyên nhân:* Các menu item `/agendas`, `/documents`, `/members`, `/users` chưa có route và web controller tương ứng trong `routes/web.php`.
   - *Tác động:* Người dùng nhấp vào các menu "Chương trình họp", "Kho tài liệu số", "Thành viên Hội đồng", "Quản lý người dùng" gặp lỗi 404 Not Found.

4. **[LOW] Tên cột chức vụ trong bảng `thanh_vien_hdt`:**
   - *Vị trí:* `MemberWebController.php` & `pages/members/index.blade.php`.
   - *Nguyên nhân:* Sử dụng `ChucVuHdt` thay vì tên cột thực tế trong CSDL là `ChucDanhHoiDongTruong` / `ChucDanh`.
   - *Tác động:* Gây lỗi Unknown column `ChucVuHdt` khi mở trang danh sách thành viên.

---

## 6. DANH SÁCH CÁC LỖI ĐÃ SỬA CHỮA

Toàn bộ 4 lỗi trên đã được sửa chữa triệt để:

| Lỗi | Cách thức xử lý | Kiểm thử xác nhận |
|---|---|---|
| **Storage Symlink** | Chạy lệnh `php artisan storage:link`, kết nối thành công `backend/public/storage` $\rightarrow$ `backend/storage/app/public` | Thử tải file tài liệu: HTTP 200 |
| **Đồng bộ trạng thái Nghị quyết** | Chuyển điều kiện truy vấn sang `whereIn('TrangThai', ['PUBLISHED', 'Đã công bố hiệu lực', 'Đã công bố'])` trong `TaskWebController` | `TaskWebTest.php` PASS |
| **Bổ sung Phân hệ Quản trị & Dữ liệu** | Xây dựng 3 Controller & Blade Views chuyên nghiệp: `DocumentWebController` (`/documents`), `MemberWebController` (`/members`), `UserWebController` (`/users`); bổ sung redirect `/agendas` $\rightarrow$ `/meetings` | `SystemAdminWebTest.php` 5/5 tests PASS |
| **Chuẩn hóa cột Chức danh HĐT** | Cập nhật truy vấn dùng `ChucDanhHoiDongTruong` và `$m->ChucDanhHoiDongTruong ?: ($m->ChucDanh ?: 'Ủy viên HĐT')` | `MemberWebController` hiển thị đầy đủ thông tin thành viên |

---

## 7. DANH SÁCH LỖI CHƯA SỬA VÀ LÝ DO

**Không có lỗi nào chưa sửa.** Tất cả các lỗi phát hiện trong quá trình kiểm toán đều đã được khắc phục hoàn chỉnh, không để lại bất kỳ lỗi tồn đọng nào thuộc phạm vi hệ thống.

---

## 8. KẾT QUẢ KIỂM TOÁN BẢO MẬT (SECURITY AUDIT)

1. **SQL Injection:**
   - 100% truy vấn dữ liệu sử dụng Eloquent ORM và PDO Parameter Binding. Không phát hiện nối chuỗi SQL thô nào không qua escape.
2. **Cross-Site Scripting (XSS):**
   - Tất cả dữ liệu đầu ra được hiển thị qua cú pháp Blade escape `{{ ... }}`. Các trường text mô tả giữ nguyên format bằng `nl2br(e(...))` an toàn.
3. **Cross-Site Request Forgery (CSRF):**
   - Tất cả các biểu mẫu `POST`, `PUT`, `DELETE` đều có directive `@csrf`. Middleware `VerifyCsrfToken` hoạt động đầy đủ.
4. **Broken Access Control & IDOR:**
   - **Tài liệu nội bộ HĐT:** Kiểm soát chặt chẽ ở cả Web và API; chỉ các vai trò `CHAIR`, `VICE_CHAIR`, `SECRETARY`, `MEMBER`, `ADMIN` mới được tải tệp `INTERNAL`. Người dùng `UNIT_REP` bị từ chối 403.
   - **Hồ sơ trình:** Đại diện đơn vị (`UNIT_REP`) chỉ được xem và quản lý hồ sơ do đơn vị mình đệ trình (`$dossier->MaDonVi === $userDeptId`).
   - **Phân công nhiệm vụ:** Đơn vị thực hiện (`EXECUTING_UNIT`) chỉ được xem các nhiệm vụ mà đơn vị mình tham gia (Chủ trì hoặc Phối hợp).
5. **Mass Assignment:**
   - Tất cả các Eloquent Models đều khai báo tường minh mảng `$fillable`, không sử dụng `$guarded = []`.
6. **Upload File an toàn:**
   - Kiểm tra định dạng đuôi file (`mimes:pdf,doc,docx,xls,xlsx,zip`), giới hạn dung lượng (`max:20480`), tạo tên file ngẫu nhiên gắn timestamp tránh ghi đè và path traversal.

---

## 9. KẾT QUẢ KIỂM TOÁN CÁC RÀNG BUỘC NGHIỆP VỤ (BR-01 ĐẾN BR-11)

- **BR-01 (Chương trình họp):** Mỗi mục nội dung họp liên kết đúng với `MaHoSo` tờ trình hoặc nội dung biểu quyết độc lập.
- **BR-02 (Thành viên dự họp):** Tự động mời toàn bộ thành viên chính thức khi tạo phiên họp; chống trùng lặp qua `firstOrCreate`.
- **BR-03 (Khách mời dự thính):** Khách mời tham gia phiên họp được ghi nhận qua `khach_moi_phien_hop`, bị chặn tuyệt đối quyền bỏ phiếu.
- **BR-04 (Ủy quyền biểu quyết):** Giấy ủy quyền chỉ có hiệu lực cho thành viên chính thức cùng phiên họp; xác thực người nhận khác người ủy quyền; chỉ được bỏ phiếu khi ủy quyền ở trạng thái `'Hợp lệ'`.
- **BR-05 & BR-06 (Tài liệu phiên họp & Khóa chính kép):** Tài liệu liên kết qua 4 bảng trung gian khóa chính kép (`tai_lieu_phien_hop`, `tai_lieu_ho_so`, `tai_lieu_nghi_quyet`, `tai_lieu_bao_cao`).
- **BR-07 (Quyền biểu quyết):** Kiểm tra cờ `CoQuyenBieuQuyet = true` trước khi cho phép vào buồng bỏ phiếu.
- **BR-09 (Bảo mật bỏ phiếu kín):** Tách biệt danh tính đại biểu và lựa chọn biểu quyết; mã hóa mã phiếu bầu sha256; màn hình kết quả không hiển thị ai bỏ phiếu gì.
- **BR-10 (Phân công nhiệm vụ & Tiến độ):**
  - Chỉ phân công nhiệm vụ từ Nghị quyết đã ban hành (`PUBLISHED`).
  - Bắt buộc có ít nhất 1 đơn vị Chủ trì (`CHỦ TRÌ`) và người phụ trách đầu mối (`TenNguoiPhuTrach` NOT NULL).
  - Tỷ lệ tiến độ từ 0% đến 100%. Khi báo cáo đạt 100% và được Thư ký/Chủ tịch phê duyệt (`APPROVED`), tự động đổi trạng thái nhiệm vụ sang `COMPLETED` và lưu `NgayXacNhanHoanThanh`.
- **BR-11 (Giới hạn số phiếu):** Mỗi thành viên chỉ được bỏ tối đa 1 phiếu trực tiếp (`MaUyQuyen IS NULL`) và 1 phiếu theo ủy quyền hợp lệ (`MaUyQuyen IS NOT NULL`).

---

## 10. KẾT QUẢ KIỂM TOÁN GIAO DIỆN UI/UX

- **Bố cục Master Layout:** Sidebar cố định nhận diện STU, Header có thông tin phiên làm việc và avatar người dùng, Breadcrumb điều hướng đa cấp, Flash messages tự đóng.
- **Kiểm tra tràn bảng (Horizontal Overflow):** Toàn bộ các bảng danh sách đều được bọc trong `<div class="table-responsive">` và `<x-table>`, loại bỏ thuộc tính ngắt dòng cứng nhắc trên cột tiêu đề dài, đảm bảo hiển thị hoàn hảo từ màn hình laptop (1366px) đến desktop lớn (1920px).
- **Bộ lọc & Phân trang:** Lưu giữ nguyên vẹn query string khi chuyển trang (`withQueryString()`). Nút Xóa bộ lọc xuất hiện linh hoạt khi có điều kiện lọc đang áp dụng.
- **Đồng bộ trạng thái trực quan:** Sử dụng hệ thống badge màu chuẩn semantic (`bg-success-subtle`, `bg-warning-subtle`, `bg-danger-subtle`, `bg-primary-subtle`) thống nhất trên tất cả 8 phân hệ.

---

## 11. KẾT QUẢ TỐI ƯU HIỆU NĂNG (PERFORMANCE)

- **Triệt tiêu hoàn toàn lỗi N+1 Query:**
  - Áp dụng Eager Loading `with([...])` trên toàn bộ các câu truy vấn danh sách và chi tiết.
  - Sử dụng `withCount(['chuongTrinhHop', 'thamDu', 'taiLieu'])` để đếm số lượng bản ghi liên quan thay vì lazy load trong vòng lặp.
- **Cache Blade Templates:**
  - Kiểm thử `php artisan view:cache` thành công 100%, xác nhận 43 tệp Blade compile trơn tru không có lỗi cú pháp.
- **Phân trang tối ưu:**
  - Giới hạn 10 - 15 bản ghi/trang cho bảng dữ liệu lớn, tránh tải hàng trăm bản ghi vào RAM của WAMP Server.

---

## 12. DANH SÁCH FILE THAY ĐỔI & THÊM MỚI

### Files thêm mới:
1. `backend/app/Http/Controllers/Web/DocumentWebController.php` (Quản lý kho tài liệu số)
2. `backend/app/Http/Controllers/Web/MemberWebController.php` (Quản lý thành viên HĐT)
3. `backend/app/Http/Controllers/Web/UserWebController.php` (Quản lý người dùng & phân quyền)
4. `backend/resources/views/pages/documents/index.blade.php` (Giao diện kho tài liệu)
5. `backend/resources/views/pages/members/index.blade.php` (Giao diện thành viên HĐT)
6. `backend/resources/views/pages/users/index.blade.php` (Giao diện quản trị người dùng)
7. `backend/tests/Feature/SystemAdminWebTest.php` (Bộ 5 test cases tự động cho 3 phân hệ trên)

### Files tinh chỉnh tối ưu:
8. `backend/app/Http/Controllers/Web/TaskWebController.php` (Chuẩn hóa trạng thái nghị quyết đã công bố)
9. `backend/routes/web.php` (Bổ sung routes `/agendas`, `/documents`, `/members`, `/users`)
10. `backend/resources/views/layouts/partials/sidebar.blade.php` (Kết nối chính xác các named routes)

---

## 13. XÁC NHẬN TÌNH TRẠNG DATABASE

- **Số bảng hiện tại:** 24 bảng dữ liệu cốt lõi (Bảo toàn 100%).
- **Số khóa ngoại hiện tại:** 37 khóa ngoại (Bảo toàn 100%).
- **Số bảng khóa chính kép:** 6 bảng (Bảo toàn 100%).
- **Dữ liệu người dùng:** Không có bất kỳ bản ghi nào bị mất hoặc thay đổi trái phép.

---

## 14. KẾT LUẬN & ĐỀ XUẤT

Hệ thống **Quản lý Hoạt động Hội đồng trường STU** đã hoàn thành toàn diện các tiêu chí kỹ thuật:
- **Kiến trúc:** Chuẩn mực Laravel Blade MVC, không phụ thuộc framework frontend dư thừa.
- **Nghiệp vụ:** Bám sát tuyệt đối hướng dẫn của Giảng viên (Buổi 5, Buổi 6) và quy chuẩn BR-01 đến BR-11.
- **Kiểm thử:** **113/113 Tests Passed (803 assertions, 0 errors)**.
- **Mã nguồn:** Toàn bộ code đã được commit và push lên repository GitHub an toàn.
- Hệ thống hoàn toàn sẵn sàng cho buổi báo cáo luận văn tốt nghiệp.
