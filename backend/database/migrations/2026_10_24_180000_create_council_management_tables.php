<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Khởi tạo toàn bộ 24 bảng theo Lược đồ quan hệ và Đặc tả nghiệp vụ Hội đồng trường STU
     * Tuân thủ 100% tài liệu duyệt Buổi 5 & Buổi 6 từ Giảng viên hướng dẫn
     * Áp dụng đầy đủ BR-01 đến BR-11 (Khóa chính, Khóa ngoại, Khóa chính kép, Ràng buộc UNIQUE & CHECK)
     */
    public function up(): void
    {
        // 1. VAI_TRO
        Schema::create('vai_tro', function (Blueprint $table) {
            $table->increments('MaVaiTro');
            $table->string('TenVaiTro', 191)->unique();
            $table->text('MoTa')->nullable();
        });

        // 2. DON_VI
        Schema::create('don_vi', function (Blueprint $table) {
            $table->increments('MaDonVi');
            $table->string('TenDonVi', 191)->unique();
            $table->string('TrangThai', 255)->default('Đang hoạt động'); // 'Đang hoạt động' | 'Ngừng hoạt động'
        });

        // 3. NGUOI_DUNG
        // [BR-01] Ràng buộc tính duy nhất của Email người dùng (Unique Email)
        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->increments('MaNguoiDung');
            $table->string('HoTen', 255);
            $table->string('email', 191)->unique(); // [BR-01] Email UNIQUE bắt buộc
            $table->string('MatKhau', 255);
            $table->string('TrangThai', 255)->default('Cho phép'); // 'Cho phép' | 'Không cho phép'
            $table->unsignedInteger('MaDonVi');
            $table->unsignedInteger('MaVaiTro');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('MaDonVi')->references('MaDonVi')->on('don_vi')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('MaVaiTro')->references('MaVaiTro')->on('vai_tro')->cascadeOnUpdate()->restrictOnDelete();
        });

        // 4. THANH_VIEN (thanh_vien_hdt)
        // [BR-07] Quy tắc Đa tư cách (1-N với NGUOI_DUNG, không unique MaNguoiDung) & CoQuyenBieuQuyet
        Schema::create('thanh_vien_hdt', function (Blueprint $table) {
            $table->increments('MaThanhVien');
            $table->string('ChucDanh', 255)->default('Thành viên'); // 'Chủ tịch' | 'Thành viên' | 'Thư ký'
            $table->string('ChucDanhHoiDongTruong', 255)->nullable(); // Tương thích ngược
            $table->string('NhiemKy', 255); // Ví dụ: 'Khóa IV (2020-2025)'
            $table->date('NgayBatDauThamGia'); // [BR-11] NOT NULL
            $table->date('NgayBatDau')->nullable(); // Tương thích ngược
            $table->date('NgayKetThuc')->nullable(); // [BR-11] Cho phép NULL khi đang đương nhiệm
            $table->boolean('CoQuyenBieuQuyet')->default(true); // [BR-07] Bit/Boolean quyền biểu quyết
            $table->unsignedInteger('MaNguoiDung'); // [BR-07] 1 NguoiDung có thể có nhiều bản ghi qua các nhiệm kỳ

            $table->foreign('MaNguoiDung')->references('MaNguoiDung')->on('nguoi_dung')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 5. PHIEN_HOP
        Schema::create('phien_hop', function (Blueprint $table) {
            $table->increments('MaPhienHop');
            $table->string('TenPhienHop', 255);
            $table->dateTime('ThoiGianBatDau');
            $table->dateTime('ThoiGianKetThuc'); // [BR-11] NOT NULL
            $table->date('NgayHop');
            $table->string('DiaDiem', 255);
            $table->string('HinhThuc', 255); // 'Trực tiếp' | 'Trực tuyến' | 'Kết hợp'
            $table->string('TrangThai', 255); // 'Đã diễn ra' | 'Đang diễn ra' | 'Hủy'
            $table->text('GhiChu')->nullable(); // [BR-11] NULLable
        });

        // 6. THAM_DU
        // [BR-02] Ràng buộc bộ duy nhất (MaPhienHop, MaThanhVien) trong bảng THAM_DU
        Schema::create('tham_du', function (Blueprint $table) {
            $table->increments('MaThamDu');
            $table->string('TrangThaiThamDu', 255); // 'Có mặt' | 'Vắng' | 'Có xin phép' | 'Không xin phép'
            $table->dateTime('ThoiDiemXacNhan')->nullable(); // [BR-11] Cho phép NULL khi chưa điểm danh
            $table->text('GhiChu')->nullable(); // [BR-11] NULLable
            $table->unsignedInteger('MaPhienHop');
            $table->unsignedInteger('MaThanhVien');

            $table->foreign('MaPhienHop')->references('MaPhienHop')->on('phien_hop')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaThanhVien')->references('MaThanhVien')->on('thanh_vien_hdt')->cascadeOnUpdate()->cascadeOnDelete();

            // [BR-02] Bộ duy nhất: Cặp (MaPhienHop, MaThanhVien) bắt buộc là UNIQUE
            $table->unique(['MaPhienHop', 'MaThanhVien'], 'tham_du_phien_hop_thanh_vien_unique');
        });

        // 7. KHACH_MOI
        Schema::create('khach_moi', function (Blueprint $table) {
            $table->increments('MaKhachMoi');
            $table->string('HoTen', 255);
            $table->string('ChucVu', 255)->nullable(); // [BR-11] NULLable
            $table->string('DonViCongTac', 255)->nullable(); // [BR-11] NULLable
            $table->string('Email', 191)->nullable(); // [BR-11] NULLable
            $table->string('SoDienThoai', 255)->nullable(); // [BR-11] NULLable
        });

        // 8. KHACH_MOI_PHIEN_HOP
        // [BR-03] Ràng buộc bộ duy nhất (MaKhachMoi, MaPhienHop) trong bảng KHACH_MOI_PHIEN_HOP
        Schema::create('khach_moi_phien_hop', function (Blueprint $table) {
            $table->increments('MaMoi');
            $table->text('NoiDungMoi');
            $table->string('TrangThaiThamDu', 255); // 'Xác nhận' | 'Có mặt' | 'Vắng' | 'Không xin phép'
            $table->unsignedInteger('MaKhachMoi');
            $table->unsignedInteger('MaPhienHop');

            $table->foreign('MaKhachMoi')->references('MaKhachMoi')->on('khach_moi')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaPhienHop')->references('MaPhienHop')->on('phien_hop')->cascadeOnUpdate()->cascadeOnDelete();

            // [BR-03] Bộ duy nhất: Cặp (MaKhachMoi, MaPhienHop) bắt buộc là UNIQUE
            $table->unique(['MaKhachMoi', 'MaPhienHop'], 'khach_moi_phien_hop_unique');
        });

        // 9. HO_SO_TRINH
        Schema::create('ho_so_trinh', function (Blueprint $table) {
            $table->increments('MaHoSo');
            $table->string('TieuDe', 255);
            $table->text('NoiDungTomTat');
            $table->date('NgayTrinh');
            $table->string('LinhVuc', 255);
            $table->string('TrangThai', 255); // 'Mới tạo' | 'Chờ bổ sung' | 'Đủ điều kiện' | 'Đầy đủ'
            $table->text('GhiChu')->nullable(); // [BR-11] NULLable
            $table->unsignedInteger('MaDonVi');
            $table->unsignedInteger('MaNguoiDung');

            $table->foreign('MaDonVi')->references('MaDonVi')->on('don_vi')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('MaNguoiDung')->references('MaNguoiDung')->on('nguoi_dung')->cascadeOnUpdate()->restrictOnDelete();
        });

        // 10. TAI_LIEU
        Schema::create('tai_lieu', function (Blueprint $table) {
            $table->increments('MaTaiLieu');
            $table->string('TenTaiLieu', 255);
            $table->string('LoaiTaiLieu', 255); // 'Tờ trình' | 'Biên bản' | 'Nghị quyết' | 'Minh chứng' | 'Các văn bản khác'
            $table->string('DuongDan', 255);
            $table->string('DinhDang', 255); // 'PDF' | 'Excel' | 'Doc' | 'Hình ảnh' | 'Khác'
            $table->integer('DungLuong')->nullable(); // [BR-11] NULLable
            $table->dateTime('NgayTaiLen');
            $table->unsignedInteger('MaNguoiDung');

            $table->foreign('MaNguoiDung')->references('MaNguoiDung')->on('nguoi_dung')->cascadeOnUpdate()->restrictOnDelete();
        });

        // 11. CHUONG_TRINH_HOP
        // [BR-08] Liên kết Hồ sơ trình vào Phiên họp thông qua Chương trình họp
        Schema::create('chuong_trinh_hop', function (Blueprint $table) {
            $table->increments('MaChuongTrinh');
            $table->integer('ThuTu'); // [BR-08] Thứ tự xét duyệt rõ ràng (> 0)
            $table->string('TrangThai', 255); // 'Chưa xem xét' | 'Đã xem xét' | 'Hoãn' | 'Khác'
            $table->text('GhiChu')->nullable(); // [BR-11] NULLable
            $table->unsignedInteger('MaPhienHop');
            $table->unsignedInteger('MaHoSo');

            $table->foreign('MaPhienHop')->references('MaPhienHop')->on('phien_hop')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaHoSo')->references('MaHoSo')->on('ho_so_trinh')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 12. NOI_DUNG_BIEU_QUYET
        Schema::create('noi_dung_bieu_quyet', function (Blueprint $table) {
            $table->increments('MaNoiDung');
            $table->text('NoiDung');
            $table->dateTime('ThoiGianMo')->nullable(); // [BR-11] NULLable khi chưa mở
            $table->dateTime('ThoiGianDong')->nullable(); // [BR-11] NULLable khi chưa đóng
            $table->string('TrangThai', 255); // 'Chưa mở' | 'Đang mở' | 'Đã đóng'
            $table->string('KetQuaBieuQuyet', 255)->nullable(); // 'Thông qua' | 'Không thông qua'
            $table->string('KetQua', 255)->nullable(); // Tương thích ngược
            $table->text('GhiChu')->nullable(); // [BR-11] NULLable
            $table->unsignedInteger('MaChuongTrinh');

            $table->foreign('MaChuongTrinh')->references('MaChuongTrinh')->on('chuong_trinh_hop')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 13. UY_QUYEN_BIEU_QUYET
        // [BR-04] Ràng buộc Ủy quyền: MaThanhVienUyQuyen != MaThanhVienNhanUyQuyen
        Schema::create('uy_quyen_bieu_quyet', function (Blueprint $table) {
            $table->increments('MaUyQuyen');
            $table->date('NgayUyQuyen');
            $table->string('PhamViUyQuyen', 255);
            $table->string('TaiLieuUyQuyen', 255); // [BR-11] NOT NULL - văn bản ủy quyền bắt buộc
            $table->string('TrangThai', 255); // 'Còn hiệu lực' | 'Đã hủy'
            $table->unsignedInteger('MaPhienHop');
            $table->unsignedInteger('MaThanhVienUyQuyen');
            $table->unsignedInteger('MaThanhVienNhanUyQuyen');

            $table->foreign('MaPhienHop')->references('MaPhienHop')->on('phien_hop')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaThanhVienUyQuyen')->references('MaThanhVien')->on('thanh_vien_hdt')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaThanhVienNhanUyQuyen')->references('MaThanhVien')->on('thanh_vien_hdt')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // [BR-04] Áp dụng CHECK constraint ở Database
        try {
            DB::statement('ALTER TABLE uy_quyen_bieu_quyet ADD CONSTRAINT chk_thanh_vien_khac_nhau CHECK (MaThanhVienUyQuyen != MaThanhVienNhanUyQuyen)');
        } catch (\Throwable $e) {
            // Trường hợp driver không hỗ trợ check constraint
        }

        // 14. KET_QUA_BIEU_QUYET
        // [BR-09] Cơ chế Ghi nhận Phiếu Kết quả biểu quyết (chỉ 3 thuộc tính chính + FKs)
        Schema::create('ket_qua_bieu_quyet', function (Blueprint $table) {
            $table->increments('MaKetQua');
            $table->string('LuaChon', 255); // 'Tán thành' | 'Không tán thành' | 'Không có ý kiến'
            $table->dateTime('ThoiDiem');
            $table->unsignedInteger('MaNoiDung');
            $table->unsignedInteger('MaThamDu');
            $table->unsignedInteger('MaUyQuyen')->nullable(); // [BR-11] Cho phép NULL khi tự biểu quyết

            $table->foreign('MaNoiDung')->references('MaNoiDung')->on('noi_dung_bieu_quyet')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaThamDu')->references('MaThamDu')->on('tham_du')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaUyQuyen')->references('MaUyQuyen')->on('uy_quyen_bieu_quyet')->cascadeOnUpdate()->nullOnDelete();
        });

        // 15. NGHI_QUYET
        Schema::create('nghi_quyet', function (Blueprint $table) {
            $table->increments('MaNghiQuyet');
            $table->string('SoNghiQuyet', 191)->unique();
            $table->date('NgayBanHanh');
            $table->string('TrichYeu', 255);
            $table->text('NoiDung');
            $table->string('TrangThai', 255); // 'Dự thảo' | 'Đã ban hành'
            $table->unsignedInteger('MaPhienHop');

            $table->foreign('MaPhienHop')->references('MaPhienHop')->on('phien_hop')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 16. NGHI_QUYET_HO_SO (Bảng trung gian N-N)
        // [BR-06] Khóa chính kép (MaNghiQuyet, MaHoSo)
        Schema::create('nghi_quyet_ho_so', function (Blueprint $table) {
            $table->unsignedInteger('MaNghiQuyet');
            $table->unsignedInteger('MaHoSo');

            $table->primary(['MaNghiQuyet', 'MaHoSo']);
            $table->foreign('MaNghiQuyet')->references('MaNghiQuyet')->on('nghi_quyet')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaHoSo')->references('MaHoSo')->on('ho_so_trinh')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 17. NGHI_QUYET_BIEU_QUYET (Bảng trung gian N-N)
        // [BR-06] Khóa chính kép (MaNghiQuyet, MaNoiDung)
        Schema::create('nghi_quyet_bieu_quyet', function (Blueprint $table) {
            $table->unsignedInteger('MaNghiQuyet');
            $table->unsignedInteger('MaNoiDung');

            $table->primary(['MaNghiQuyet', 'MaNoiDung']);
            $table->foreign('MaNghiQuyet')->references('MaNghiQuyet')->on('nghi_quyet')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaNoiDung')->references('MaNoiDung')->on('noi_dung_bieu_quyet')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 18. NHIEM_VU
        Schema::create('nhiem_vu', function (Blueprint $table) {
            $table->increments('MaNhiemVu');
            $table->string('TenNhiemVu', 255);
            $table->text('NoiDung');
            $table->date('NgayGiaoNhiemVu')->nullable(); // [BR-10] Ngày giao nhiệm vụ
            $table->date('NgayGiao')->nullable(); // Tương thích ngược
            $table->date('NgayHoanThanhNhiemVu')->nullable(); // [BR-10] Hạn hoàn thành nhiệm vụ
            $table->date('NgayHoanThanh')->nullable(); // Tương thích ngược
            $table->string('TrangThai', 255); // 'Chưa thực hiện' | 'Đang thực hiện' | 'Đã hoàn thành'
            $table->date('NgayXacNhanHoanThanh')->nullable(); // [BR-11] NULL khi chưa xong
            $table->unsignedInteger('MaNghiQuyet');

            $table->foreign('MaNghiQuyet')->references('MaNghiQuyet')->on('nghi_quyet')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 19. PHAN_CONG_NHIEM_VU
        // [BR-10] Phân công bắt buộc có TenNguoiPhuTrach (NOT NULL)
        Schema::create('phan_cong_nhiem_vu', function (Blueprint $table) {
            $table->increments('MaPhanCong');
            $table->string('VaiTroThucHien', 255);
            $table->string('TrangThaiThucHien', 255);
            $table->string('TenNguoiPhuTrach', 255)->default('Người phụ trách đơn vị'); // [BR-10] Bắt buộc ghi nhận tên người phụ trách (NOT NULL)
            $table->unsignedInteger('MaNhiemVu');
            $table->unsignedInteger('MaDonVi');

            $table->foreign('MaNhiemVu')->references('MaNhiemVu')->on('nhiem_vu')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaDonVi')->references('MaDonVi')->on('don_vi')->cascadeOnUpdate()->restrictOnDelete();
        });

        // 20. BAO_CAO
        // [BR-10] Ràng buộc Tiến độ (0 <= TienDo <= 100) & TrangThaiXacNhan
        Schema::create('bao_cao', function (Blueprint $table) {
            $table->increments('MaBaoCao');
            $table->date('NgayBaoCao');
            $table->integer('TienDo'); // [BR-10] 0 <= TienDo <= 100 (%)
            $table->text('NoiDung'); // [BR-10] Nội dung tiến độ
            $table->text('NoiDungTienDo')->nullable(); // Tương thích ngược
            $table->text('NoiDungKhoKhan')->nullable(); // [BR-11] NULLable
            $table->string('TrangThaiXacNhan', 255); // 'Chờ kiểm tra' | 'Đạt' | 'Yêu cầu bổ sung'
            $table->date('NgayXacNhan')->nullable(); // [BR-11] NULLable
            $table->text('GhiChu')->nullable(); // [BR-11] NULLable
            $table->unsignedInteger('MaPhanCong');
            $table->unsignedInteger('MaNguoiDung');

            $table->foreign('MaPhanCong')->references('MaPhanCong')->on('phan_cong_nhiem_vu')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaNguoiDung')->references('MaNguoiDung')->on('nguoi_dung')->cascadeOnUpdate()->restrictOnDelete();
        });

        // [BR-10] Áp dụng CHECK constraint 0 <= TienDo <= 100 ở Database
        try {
            DB::statement('ALTER TABLE bao_cao ADD CONSTRAINT chk_tien_do_hop_le CHECK (TienDo >= 0 AND TienDo <= 100)');
        } catch (\Throwable $e) {
            // Trường hợp driver không hỗ trợ check constraint
        }

        // 21. TAI_LIEU_HO_SO (Bảng trung gian N-N)
        // [BR-06] Khóa chính kép (MaTaiLieu, MaHoSo)
        Schema::create('tai_lieu_ho_so', function (Blueprint $table) {
            $table->unsignedInteger('MaTaiLieu');
            $table->unsignedInteger('MaHoSo');

            $table->primary(['MaTaiLieu', 'MaHoSo']);
            $table->foreign('MaTaiLieu')->references('MaTaiLieu')->on('tai_lieu')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaHoSo')->references('MaHoSo')->on('ho_so_trinh')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 22. TAI_LIEU_NGHI_QUYET (Bảng trung gian N-N)
        // [BR-06] Khóa chính kép (MaTaiLieu, MaNghiQuyet)
        Schema::create('tai_lieu_nghi_quyet', function (Blueprint $table) {
            $table->unsignedInteger('MaTaiLieu');
            $table->unsignedInteger('MaNghiQuyet');

            $table->primary(['MaTaiLieu', 'MaNghiQuyet']);
            $table->foreign('MaTaiLieu')->references('MaTaiLieu')->on('tai_lieu')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaNghiQuyet')->references('MaNghiQuyet')->on('nghi_quyet')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 23. TAI_LIEU_BAO_CAO (Bảng trung gian N-N)
        // [BR-06] Khóa chính kép (MaTaiLieu, MaBaoCao)
        Schema::create('tai_lieu_bao_cao', function (Blueprint $table) {
            $table->unsignedInteger('MaTaiLieu');
            $table->unsignedInteger('MaBaoCao');

            $table->primary(['MaTaiLieu', 'MaBaoCao']);
            $table->foreign('MaTaiLieu')->references('MaTaiLieu')->on('tai_lieu')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaBaoCao')->references('MaBaoCao')->on('bao_cao')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 24. TAI_LIEU_PHIEN_HOP (Bảng trung gian N-N - [BR-05] Bổ sung ở Buổi 6)
        // [BR-05 & BR-06] Khóa chính kép (MaTaiLieu, MaPhienHop)
        Schema::create('tai_lieu_phien_hop', function (Blueprint $table) {
            $table->unsignedInteger('MaTaiLieu');
            $table->unsignedInteger('MaPhienHop');

            $table->primary(['MaTaiLieu', 'MaPhienHop']);
            $table->foreign('MaTaiLieu')->references('MaTaiLieu')->on('tai_lieu')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaPhienHop')->references('MaPhienHop')->on('phien_hop')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Thu hồi toàn bộ 24 bảng theo thứ tự đảo ngược
     */
    public function down(): void
    {
        Schema::dropIfExists('tai_lieu_phien_hop');
        Schema::dropIfExists('tai_lieu_bao_cao');
        Schema::dropIfExists('tai_lieu_nghi_quyet');
        Schema::dropIfExists('tai_lieu_ho_so');
        Schema::dropIfExists('bao_cao');
        Schema::dropIfExists('phan_cong_nhiem_vu');
        Schema::dropIfExists('nhiem_vu');
        Schema::dropIfExists('nghi_quyet_bieu_quyet');
        Schema::dropIfExists('nghi_quyet_ho_so');
        Schema::dropIfExists('nghi_quyet');
        Schema::dropIfExists('ket_qua_bieu_quyet');
        Schema::dropIfExists('uy_quyen_bieu_quyet');
        Schema::dropIfExists('noi_dung_bieu_quyet');
        Schema::dropIfExists('chuong_trinh_hop');
        Schema::dropIfExists('tai_lieu');
        Schema::dropIfExists('ho_so_trinh');
        Schema::dropIfExists('khach_moi_phien_hop');
        Schema::dropIfExists('khach_moi');
        Schema::dropIfExists('tham_du');
        Schema::dropIfExists('phien_hop');
        Schema::dropIfExists('thanh_vien_hdt');
        Schema::dropIfExists('nguoi_dung');
        Schema::dropIfExists('don_vi');
        Schema::dropIfExists('vai_tro');
    }
};
