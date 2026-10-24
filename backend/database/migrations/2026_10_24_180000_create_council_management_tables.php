<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Khởi tạo toàn bộ 24 bảng theo Lược đồ quan hệ Luận văn tốt nghiệp Hội đồng trường STU
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
            $table->string('TrangThai', 255)->default('Hoạt động');
        });

        // 3. NGUOI_DUNG
        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->increments('MaNguoiDung');
            $table->string('HoTen', 255);
            $table->string('email', 191)->unique();
            $table->string('MatKhau', 255);
            $table->string('TrangThai', 255)->default('Hoạt động');
            $table->unsignedInteger('MaVaiTro');
            $table->unsignedInteger('MaDonVi');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('MaVaiTro')->references('MaVaiTro')->on('vai_tro')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('MaDonVi')->references('MaDonVi')->on('don_vi')->cascadeOnUpdate()->restrictOnDelete();
        });

        // 4. THANH_VIEN_HDT
        Schema::create('thanh_vien_hdt', function (Blueprint $table) {
            $table->increments('MaThanhVien');
            $table->string('ChucDanhHoiDongTruong', 255);
            $table->string('NhiemKy', 255);
            $table->date('NgayBatDau');
            $table->date('NgayKetThuc')->nullable();
            $table->integer('CoQuyenBieuQuyet')->default(1);
            $table->unsignedInteger('MaNguoiDung')->unique();

            $table->foreign('MaNguoiDung')->references('MaNguoiDung')->on('nguoi_dung')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 5. PHIEN_HOP
        Schema::create('phien_hop', function (Blueprint $table) {
            $table->increments('MaPhienHop');
            $table->string('TenPhienHop', 255);
            $table->dateTime('ThoiGianBatDau');
            $table->dateTime('ThoiGianKetThuc')->nullable();
            $table->date('NgayHop');
            $table->string('DiaDiem', 255);
            $table->string('HinhThuc', 255);
            $table->string('TrangThai', 255);
            $table->text('GhiChu')->nullable();
        });

        // 6. THAM_DU
        Schema::create('tham_du', function (Blueprint $table) {
            $table->increments('MaThamDu');
            $table->string('TrangThaiThamDu', 255);
            $table->dateTime('ThoiDiemXacNhan');
            $table->text('GhiChu')->nullable();
            $table->unsignedInteger('MaPhienHop');
            $table->unsignedInteger('MaThanhVien');

            $table->foreign('MaPhienHop')->references('MaPhienHop')->on('phien_hop')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaThanhVien')->references('MaThanhVien')->on('thanh_vien_hdt')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 7. KHACH_MOI
        Schema::create('khach_moi', function (Blueprint $table) {
            $table->increments('MaKhachMoi');
            $table->string('HoTen', 255);
            $table->string('ChucVu', 255);
            $table->string('DonViCongTac', 255)->nullable();
            $table->string('Email', 191)->nullable();
            $table->string('SoDienThoai', 255)->nullable();
        });

        // 8. KHACH_MOI_PHIEN_HOP
        Schema::create('khach_moi_phien_hop', function (Blueprint $table) {
            $table->increments('MaMoi');
            $table->text('NoiDungMoi');
            $table->string('TrangThaiThamDu', 255);
            $table->unsignedInteger('MaKhachMoi');
            $table->unsignedInteger('MaPhienHop');

            $table->foreign('MaKhachMoi')->references('MaKhachMoi')->on('khach_moi')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaPhienHop')->references('MaPhienHop')->on('phien_hop')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 9. HO_SO_TRINH
        Schema::create('ho_so_trinh', function (Blueprint $table) {
            $table->increments('MaHoSo');
            $table->string('TieuDe', 255);
            $table->text('NoiDungTomTat');
            $table->date('NgayTrinh');
            $table->string('LinhVuc', 255);
            $table->string('TrangThai', 255);
            $table->text('GhiChu')->nullable();
            $table->unsignedInteger('MaDonVi');
            $table->unsignedInteger('MaNguoiDung');

            $table->foreign('MaDonVi')->references('MaDonVi')->on('don_vi')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('MaNguoiDung')->references('MaNguoiDung')->on('nguoi_dung')->cascadeOnUpdate()->restrictOnDelete();
        });

        // 10. TAI_LIEU
        Schema::create('tai_lieu', function (Blueprint $table) {
            $table->increments('MaTaiLieu');
            $table->string('TenTaiLieu', 255);
            $table->string('LoaiTaiLieu', 255);
            $table->string('DuongDan', 255);
            $table->string('DinhDang', 255);
            $table->integer('DungLuong');
            $table->dateTime('NgayTaiLen');
            $table->unsignedInteger('MaNguoiDung');

            $table->foreign('MaNguoiDung')->references('MaNguoiDung')->on('nguoi_dung')->cascadeOnUpdate()->restrictOnDelete();
        });

        // 11. CHUONG_TRINH_HOP
        Schema::create('chuong_trinh_hop', function (Blueprint $table) {
            $table->increments('MaChuongTrinh');
            $table->integer('ThuTu');
            $table->string('TrangThai', 255);
            $table->string('GhiChu', 255)->nullable();
            $table->unsignedInteger('MaPhienHop');
            $table->unsignedInteger('MaHoSo');

            $table->foreign('MaPhienHop')->references('MaPhienHop')->on('phien_hop')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaHoSo')->references('MaHoSo')->on('ho_so_trinh')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 12. NOI_DUNG_BIEU_QUYET
        Schema::create('noi_dung_bieu_quyet', function (Blueprint $table) {
            $table->increments('MaNoiDung');
            $table->text('NoiDung');
            $table->dateTime('ThoiGianMo')->nullable();
            $table->dateTime('ThoiGianDong');
            $table->string('TrangThai', 255);
            $table->string('KetQua', 255)->nullable();
            $table->text('GhiChu')->nullable();
            $table->unsignedInteger('MaChuongTrinh');

            $table->foreign('MaChuongTrinh')->references('MaChuongTrinh')->on('chuong_trinh_hop')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 13. UY_QUYEN_BIEU_QUYET
        Schema::create('uy_quyen_bieu_quyet', function (Blueprint $table) {
            $table->increments('MaUyQuyen');
            $table->date('NgayUyQuyen');
            $table->string('PhamViUyQuyen', 255);
            $table->string('TaiLieuUyQuyen', 255)->nullable();
            $table->string('TrangThai', 255);
            $table->unsignedInteger('MaPhienHop');
            $table->unsignedInteger('MaThanhVienUyQuyen');
            $table->unsignedInteger('MaThanhVienNhanUyQuyen');

            $table->foreign('MaPhienHop')->references('MaPhienHop')->on('phien_hop')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaThanhVienUyQuyen')->references('MaThanhVien')->on('thanh_vien_hdt')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaThanhVienNhanUyQuyen')->references('MaThanhVien')->on('thanh_vien_hdt')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 14. KET_QUA_BIEU_QUYET
        Schema::create('ket_qua_bieu_quyet', function (Blueprint $table) {
            $table->increments('MaKetQua');
            $table->string('LuaChon', 255);
            $table->dateTime('ThoiDiem');
            $table->unsignedInteger('MaNoiDung');
            $table->unsignedInteger('MaThamDu');
            $table->unsignedInteger('MaUyQuyen')->nullable();

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
            $table->string('TrangThai', 255);
            $table->unsignedInteger('MaPhienHop');

            $table->foreign('MaPhienHop')->references('MaPhienHop')->on('phien_hop')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 16. NGHI_QUYET_HO_SO (Bảng trung gian N-N)
        Schema::create('nghi_quyet_ho_so', function (Blueprint $table) {
            $table->unsignedInteger('MaNghiQuyet');
            $table->unsignedInteger('MaHoSo');

            $table->primary(['MaNghiQuyet', 'MaHoSo']);
            $table->foreign('MaNghiQuyet')->references('MaNghiQuyet')->on('nghi_quyet')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaHoSo')->references('MaHoSo')->on('ho_so_trinh')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 17. NGHI_QUYET_BIEU_QUYET (Bảng trung gian N-N)
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
            $table->date('NgayGiao');
            $table->date('NgayHoanThanh');
            $table->string('TrangThai', 255);
            $table->date('NgayXacNhanHoanThanh')->nullable();
            $table->unsignedInteger('MaNghiQuyet');

            $table->foreign('MaNghiQuyet')->references('MaNghiQuyet')->on('nghi_quyet')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 19. PHAN_CONG_NHIEM_VU
        Schema::create('phan_cong_nhiem_vu', function (Blueprint $table) {
            $table->increments('MaPhanCong');
            $table->string('VaiTroThucHien', 255);
            $table->string('TrangThaiThucHien', 255);
            $table->string('TenNguoiPhuTrach', 255)->nullable();
            $table->unsignedInteger('MaNhiemVu');
            $table->unsignedInteger('MaDonVi');

            $table->foreign('MaNhiemVu')->references('MaNhiemVu')->on('nhiem_vu')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaDonVi')->references('MaDonVi')->on('don_vi')->cascadeOnUpdate()->restrictOnDelete();
        });

        // 20. BAO_CAO
        Schema::create('bao_cao', function (Blueprint $table) {
            $table->increments('MaBaoCao');
            $table->date('NgayBaoCao');
            $table->integer('TienDo');
            $table->text('NoiDungTienDo');
            $table->text('NoiDungKhoKhan')->nullable();
            $table->string('TrangThaiXacNhan', 255);
            $table->date('NgayXacNhan')->nullable();
            $table->text('GhiChu')->nullable();
            $table->unsignedInteger('MaPhanCong');
            $table->unsignedInteger('MaNguoiDung');

            $table->foreign('MaPhanCong')->references('MaPhanCong')->on('phan_cong_nhiem_vu')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaNguoiDung')->references('MaNguoiDung')->on('nguoi_dung')->cascadeOnUpdate()->restrictOnDelete();
        });

        // 21. TAI_LIEU_HO_SO (Bảng trung gian N-N)
        Schema::create('tai_lieu_ho_so', function (Blueprint $table) {
            $table->unsignedInteger('MaTaiLieu');
            $table->unsignedInteger('MaHoSo');

            $table->primary(['MaTaiLieu', 'MaHoSo']);
            $table->foreign('MaTaiLieu')->references('MaTaiLieu')->on('tai_lieu')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaHoSo')->references('MaHoSo')->on('ho_so_trinh')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 22. TAI_LIEU_NGHI_QUYET (Bảng trung gian N-N)
        Schema::create('tai_lieu_nghi_quyet', function (Blueprint $table) {
            $table->unsignedInteger('MaTaiLieu');
            $table->unsignedInteger('MaNghiQuyet');

            $table->primary(['MaTaiLieu', 'MaNghiQuyet']);
            $table->foreign('MaTaiLieu')->references('MaTaiLieu')->on('tai_lieu')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaNghiQuyet')->references('MaNghiQuyet')->on('nghi_quyet')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 23. TAI_LIEU_BAO_CAO (Bảng trung gian N-N)
        Schema::create('tai_lieu_bao_cao', function (Blueprint $table) {
            $table->unsignedInteger('MaTaiLieu');
            $table->unsignedInteger('MaBaoCao');

            $table->primary(['MaTaiLieu', 'MaBaoCao']);
            $table->foreign('MaTaiLieu')->references('MaTaiLieu')->on('tai_lieu')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('MaBaoCao')->references('MaBaoCao')->on('bao_cao')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // 24. TAI_LIEU_PHIEN_HOP (Bảng trung gian N-N)
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
