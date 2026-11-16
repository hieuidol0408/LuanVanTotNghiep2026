-- MySQL dump 10.13  Distrib 9.1.0, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: hoi_dong_truong_stu
-- ------------------------------------------------------
-- Server version	9.1.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `hoi_dong_truong_stu`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `hoi_dong_truong_stu` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ ;

USE `hoi_dong_truong_stu`;

--
-- Table structure for table `bao_cao`
--

DROP TABLE IF EXISTS `bao_cao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bao_cao` (
  `MaBaoCao` int unsigned NOT NULL AUTO_INCREMENT,
  `NgayBaoCao` date NOT NULL,
  `TienDo` int NOT NULL,
  `NoiDung` text NOT NULL,
  `NoiDungTienDo` text,
  `NoiDungKhoKhan` text,
  `TrangThaiXacNhan` varchar(255) NOT NULL,
  `NgayXacNhan` date DEFAULT NULL,
  `GhiChu` text,
  `MaPhanCong` int unsigned NOT NULL,
  `MaNguoiDung` int unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaBaoCao`),
  KEY `fk_bao_cao_phan_cong` (`MaPhanCong`),
  KEY `fk_bao_cao_nguoi_dung` (`MaNguoiDung`),
  CONSTRAINT `fk_bao_cao_nguoi_dung` FOREIGN KEY (`MaNguoiDung`) REFERENCES `nguoi_dung` (`MaNguoiDung`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_bao_cao_phan_cong` FOREIGN KEY (`MaPhanCong`) REFERENCES `phan_cong_nhiem_vu` (`MaPhanCong`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_tien_do_hop_le` CHECK (((`TienDo` >= 0) and (`TienDo` <= 100)))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bao_cao`
--

LOCK TABLES `bao_cao` WRITE;
/*!40000 ALTER TABLE `bao_cao` DISABLE KEYS */;
INSERT INTO `bao_cao` VALUES (1,'2026-11-10',65,'Đã hoàn thành giai đoạn 1 khảo sát thực tế và tổng hợp dữ liệu khảo sát từ các khoa.','Đã hoàn thành giai đoạn 1 khảo sát thực tế và tổng hợp dữ liệu khảo sát từ các khoa.','Cần hỗ trợ thêm tài chính và giải ngân theo tiến độ hợp đồng.','Chờ kiểm tra',NULL,NULL,1,3,NULL),(2,'2026-11-10',100,'Nhiệm vụ đã hoàn tất toàn bộ các hạng mục công việc nghiệm thu.',NULL,NULL,'Đạt','2026-11-10','Đạt chất lượng theo đúng chỉ đạo của Hội đồng trường.',7,3,NULL),(4,'2026-10-23',45,'Đã hoàn thành dự thảo khung 132 tín chỉ và lấy ý kiến hội đồng khoa học khoa.','Đã hoàn thành dự thảo khung 132 tín chỉ và lấy ý kiến hội đồng khoa học khoa.','Cần hỗ trợ kinh phí mời chuyên gia doanh nghiệp phản biện.','APPROVED','2026-10-24','Thư ký HĐT đã tiếp nhận và ghi nhận tiến độ.',10,7,NULL);
/*!40000 ALTER TABLE `bao_cao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chuong_trinh_hop`
--

DROP TABLE IF EXISTS `chuong_trinh_hop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chuong_trinh_hop` (
  `MaChuongTrinh` int unsigned NOT NULL AUTO_INCREMENT,
  `ThuTu` int NOT NULL,
  `TrangThai` varchar(255) NOT NULL,
  `GhiChu` text,
  `MaPhienHop` int unsigned NOT NULL,
  `MaHoSo` int unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaChuongTrinh`),
  KEY `fk_cth_phien_hop` (`MaPhienHop`),
  KEY `fk_cth_ho_so` (`MaHoSo`),
  CONSTRAINT `fk_cth_ho_so` FOREIGN KEY (`MaHoSo`) REFERENCES `ho_so_trinh` (`MaHoSo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cth_phien_hop` FOREIGN KEY (`MaPhienHop`) REFERENCES `phien_hop` (`MaPhienHop`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chuong_trinh_hop`
--

LOCK TABLES `chuong_trinh_hop` WRITE;
/*!40000 ALTER TABLE `chuong_trinh_hop` DISABLE KEYS */;
INSERT INTO `chuong_trinh_hop` VALUES (1,1,'Đã thông qua','A, B',1,1,NULL),(2,2,'Đang thảo luận','A, B',1,2,NULL),(3,3,'Chờ xem xét','A, B, C',1,1,NULL);
/*!40000 ALTER TABLE `chuong_trinh_hop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `don_vi`
--

DROP TABLE IF EXISTS `don_vi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `don_vi` (
  `MaDonVi` int unsigned NOT NULL AUTO_INCREMENT,
  `TenDonVi` varchar(191) NOT NULL,
  `TrangThai` varchar(255) NOT NULL DEFAULT 'Đang hoạt động',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaDonVi`),
  UNIQUE KEY `don_vi_tendonvi_unique` (`TenDonVi`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `don_vi`
--

LOCK TABLES `don_vi` WRITE;
/*!40000 ALTER TABLE `don_vi` DISABLE KEYS */;
INSERT INTO `don_vi` VALUES (1,'Văn phòng Hội đồng trường','Hoạt động',NULL),(2,'Văn phòng Hiệu trưởng STU','Hoạt động',NULL),(3,'Khoa Công nghệ Thông tin','Hoạt động',NULL),(4,'Khoa Điện - Điện tử','Hoạt động',NULL),(5,'Phòng Đào tạo','Hoạt động',NULL),(6,'Trung tâm CNTT & Truyền thông','Hoạt động',NULL);
/*!40000 ALTER TABLE `don_vi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ho_so_trinh`
--

DROP TABLE IF EXISTS `ho_so_trinh`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ho_so_trinh` (
  `MaHoSo` int unsigned NOT NULL AUTO_INCREMENT,
  `TieuDe` varchar(255) NOT NULL,
  `NoiDungTomTat` text NOT NULL,
  `NgayTrinh` date NOT NULL,
  `LinhVuc` varchar(255) NOT NULL,
  `TrangThai` varchar(255) NOT NULL,
  `GhiChu` text,
  `MaDonVi` int unsigned NOT NULL,
  `MaNguoiDung` int unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaHoSo`),
  KEY `fk_ho_so_don_vi` (`MaDonVi`),
  KEY `fk_ho_so_nguoi_dung` (`MaNguoiDung`),
  CONSTRAINT `fk_ho_so_don_vi` FOREIGN KEY (`MaDonVi`) REFERENCES `don_vi` (`MaDonVi`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ho_so_nguoi_dung` FOREIGN KEY (`MaNguoiDung`) REFERENCES `nguoi_dung` (`MaNguoiDung`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ho_so_trinh`
--

LOCK TABLES `ho_so_trinh` WRITE;
/*!40000 ALTER TABLE `ho_so_trinh` DISABLE KEYS */;
INSERT INTO `ho_so_trinh` VALUES (1,'Tờ trình về việc mở ngành đào tạo Công nghệ Vi mạch bán dẫn','Đề xuất thành lập ngành đào tạo chuyên sâu về thiết kế và kiểm thử vi mạch bán dẫn.','2026-10-24','Đào tạo & Công nghệ cao','Mới tạo','Hồ sơ chuẩn bị cho năm học 2026-2027',5,6,NULL),(2,'Tờ trình điều chỉnh mức học phí năm 2026','Điều chỉnh học phí theo lộ trình tự chủ','2026-10-24','Tài chính','Mới tạo',NULL,5,6,NULL),(3,'Tờ trình đã nộp','Nội dung','2026-10-24','Đào tạo','Mới tạo',NULL,5,6,NULL),(4,'Hồ sơ chờ duyệt trình phiên họp','Nội dung chờ duyệt','2026-10-24','Nhân sự','Mới tạo','[Thẩm tra]: Hồ sơ đầy đủ thủ tục pháp lý, thống nhất trình HĐT.',1,1,NULL),(5,'Hồ sơ kiểm tra phân quyền','Nội dung','2026-10-24','Tài chính','Mới tạo',NULL,5,6,NULL),(6,'Báo cáo tổng kết công tác tuyển sinh và định hướng năm học mới','Báo cáo tổng kết công tác tuyển sinh và định hướng năm học mới','2026-10-24','Chương trình họp','Đã xếp lịch',NULL,1,3,NULL),(7,'Hồ sơ bảo mật của đơn vị B 6aed7216e037a','Nội dung nội bộ của đơn vị B','2026-11-06','Tài chính & Kế hoạch ngân sách','Mới tạo',NULL,2,7,NULL),(8,'Hồ sơ bảo mật của đơn vị B 6aed729691fc5','Nội dung nội bộ của đơn vị B','2026-11-06','Tài chính & Kế hoạch ngân sách','Mới tạo',NULL,2,7,NULL),(9,'Hồ sơ bảo mật của đơn vị B 6aed72d3aa7f8','Nội dung nội bộ của đơn vị B','2026-11-06','Tài chính & Kế hoạch ngân sách','Mới tạo',NULL,2,7,NULL),(11,'Đề án mở ngành đào tạo Kỹ thuật Robot & Trí tuệ nhân tạo (AI) bậc Đại học','Mở ngành đào tạo mới đón đầu xu hướng công nghiệp 4.0 và nhu cầu nguồn nhân lực công nghệ cao.','2026-11-05','Đào tạo & Đảm bảo chất lượng','Mới tạo','A, B',5,6,NULL);
/*!40000 ALTER TABLE `ho_so_trinh` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ket_qua_bieu_quyet`
--

DROP TABLE IF EXISTS `ket_qua_bieu_quyet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ket_qua_bieu_quyet` (
  `MaKetQua` int unsigned NOT NULL AUTO_INCREMENT,
  `LuaChon` varchar(255) NOT NULL,
  `ThoiDiem` datetime NOT NULL,
  `MaNoiDung` int unsigned NOT NULL,
  `MaThamDu` int unsigned NOT NULL,
  `MaUyQuyen` int unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaKetQua`),
  KEY `fk_kqbq_noi_dung` (`MaNoiDung`),
  KEY `fk_kqbq_tham_du` (`MaThamDu`),
  KEY `fk_kqbq_uy_quyen` (`MaUyQuyen`),
  CONSTRAINT `fk_kqbq_noi_dung` FOREIGN KEY (`MaNoiDung`) REFERENCES `noi_dung_bieu_quyet` (`MaNoiDung`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kqbq_tham_du` FOREIGN KEY (`MaThamDu`) REFERENCES `tham_du` (`MaThamDu`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kqbq_uy_quyen` FOREIGN KEY (`MaUyQuyen`) REFERENCES `uy_quyen_bieu_quyet` (`MaUyQuyen`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ket_qua_bieu_quyet`
--

LOCK TABLES `ket_qua_bieu_quyet` WRITE;
/*!40000 ALTER TABLE `ket_qua_bieu_quyet` DISABLE KEYS */;
INSERT INTO `ket_qua_bieu_quyet` VALUES (1,'Tán thành','2026-11-15 09:15:00',1,1,NULL,NULL),(2,'Tán thành','2026-11-15 09:15:00',1,2,NULL,NULL);
/*!40000 ALTER TABLE `ket_qua_bieu_quyet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `khach_moi`
--

DROP TABLE IF EXISTS `khach_moi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `khach_moi` (
  `MaKhachMoi` int unsigned NOT NULL AUTO_INCREMENT,
  `HoTen` varchar(255) NOT NULL,
  `ChucVu` varchar(255) DEFAULT NULL,
  `DonViCongTac` varchar(255) DEFAULT NULL,
  `Email` varchar(191) DEFAULT NULL,
  `SoDienThoai` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaKhachMoi`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `khach_moi`
--

LOCK TABLES `khach_moi` WRITE;
/*!40000 ALTER TABLE `khach_moi` DISABLE KEYS */;
INSERT INTO `khach_moi` VALUES (1,'GS.TS. Hoàng Khách Mời','Chuyên gia Cố vấn Trí tuệ Nhân tạo','Hội đồng Khoa học & Công nghệ TP.HCM','expert@hcm.edu.vn','0912345678',NULL),(2,'ThS. Nguyễn Khách Mời 6adcd3e3d5436','ThS','Sở Giáo dục & Đào tạo TP.HCM','guest_6adcd3e3d5436@hcm.edu.vn','0988776655',NULL),(3,'ThS. Nguyễn Khách Mời 6adcd418e5a9e','ThS','Sở Giáo dục & Đào tạo TP.HCM','guest_6adcd418e5a9e@hcm.edu.vn','0988776655',NULL),(4,'ThS. Nguyễn Khách Mời 6adef1858ec0b','ThS','Sở Giáo dục & Đào tạo TP.HCM','guest_6adef1858ec0b@hcm.edu.vn','0988776655',NULL);
/*!40000 ALTER TABLE `khach_moi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `khach_moi_phien_hop`
--

DROP TABLE IF EXISTS `khach_moi_phien_hop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `khach_moi_phien_hop` (
  `MaMoi` int unsigned NOT NULL AUTO_INCREMENT,
  `NoiDungMoi` text NOT NULL,
  `TrangThaiThamDu` varchar(255) NOT NULL,
  `MaKhachMoi` int unsigned NOT NULL,
  `MaPhienHop` int unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaMoi`),
  UNIQUE KEY `khach_moi_phien_hop_unique` (`MaKhachMoi`,`MaPhienHop`),
  KEY `fk_kmph_phien_hop` (`MaPhienHop`),
  CONSTRAINT `fk_kmph_khach_moi` FOREIGN KEY (`MaKhachMoi`) REFERENCES `khach_moi` (`MaKhachMoi`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kmph_phien_hop` FOREIGN KEY (`MaPhienHop`) REFERENCES `phien_hop` (`MaPhienHop`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `khach_moi_phien_hop`
--

LOCK TABLES `khach_moi_phien_hop` WRITE;
/*!40000 ALTER TABLE `khach_moi_phien_hop` DISABLE KEYS */;
INSERT INTO `khach_moi_phien_hop` VALUES (1,'Báo cáo tham luận và tư vấn khung chương trình đào tạo ngành Robot & AI','Xác nhận',1,1,NULL),(2,'Mời tham vấn chuyên môn về kiểm định chất lượng','Xác nhận',2,1,NULL),(3,'Mời tham vấn chuyên môn về kiểm định chất lượng','Xác nhận',3,1,NULL),(4,'Mời tham vấn chuyên môn về kiểm định chất lượng','Xác nhận',4,1,NULL);
/*!40000 ALTER TABLE `khach_moi_phien_hop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_tao_bang_nguoi_dung',1),(2,'0001_01_01_000001_tao_bang_bo_nho_dem',1),(3,'0001_01_01_000002_tao_bang_hang_doi',1),(4,'2026_10_24_164851_tao_bang_ma_truy_cap_ca_nhan',1),(5,'2026_10_24_165042_tao_bang_don_vi_va_cap_nhat_nguoi_dung',1),(6,'2026_10_24_180000_tao_cac_bang_quan_ly_hoi_dong_truong',1),(7,'2026_11_11_154000_bo_sung_cot_deleted_at_toan_he_thong',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nghi_quyet`
--

DROP TABLE IF EXISTS `nghi_quyet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nghi_quyet` (
  `MaNghiQuyet` int unsigned NOT NULL AUTO_INCREMENT,
  `SoNghiQuyet` varchar(191) NOT NULL,
  `NgayBanHanh` date NOT NULL,
  `TrichYeu` varchar(255) NOT NULL,
  `NoiDung` text NOT NULL,
  `TrangThai` varchar(255) NOT NULL,
  `MaPhienHop` int unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaNghiQuyet`),
  UNIQUE KEY `nghi_quyet_songhiquyet_unique` (`SoNghiQuyet`),
  KEY `fk_nghi_quyet_phien_hop` (`MaPhienHop`),
  CONSTRAINT `fk_nghi_quyet_phien_hop` FOREIGN KEY (`MaPhienHop`) REFERENCES `phien_hop` (`MaPhienHop`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nghi_quyet`
--

LOCK TABLES `nghi_quyet` WRITE;
/*!40000 ALTER TABLE `nghi_quyet` DISABLE KEYS */;
INSERT INTO `nghi_quyet` VALUES (1,'01/NQ-HĐT-2026','2026-11-10','Về việc phê duyệt chủ trương mở ngành đào tạo Kỹ thuật Robot & Trí tuệ nhân tạo (AI)','Hội đồng trường Trường Đại học Công nghệ Sài Gòn quyết nghị thông qua chủ trương mở ngành đào tạo trình độ đại học Kỹ thuật Robot & Trí tuệ nhân tạo từ năm học 2026-2027.','Đã công bố hiệu lực',1,NULL),(2,'02/NQ-HĐT-2026','2026-10-22','Về việc ban hành Quy chế chi tiêu nội bộ và định mức tài chính năm 2026','Hội đồng trường thông qua dự thảo Quy chế chi tiêu nội bộ áp dụng thống nhất trong toàn trường từ ngày 01/01/2026.','Đã ký ban hành',1,NULL),(95,'03/NQ-HĐT-2026','2026-10-15','Về việc thông qua kế hoạch đầu tư phòng thí nghiệm Robot và cơ sở vật chất năm 2026','Hội đồng trường phê duyệt kế hoạch đầu tư nâng cấp cơ sở vật chất, trang bị hệ thống máy tính và robot đào tạo.','Đã công bố',1,NULL),(96,'04/NQ-HĐT-2026','2026-10-05','Về công tác kiểm định chất lượng 04 chương trình đào tạo đại học theo tiêu chuẩn Bộ GD&ĐT','Giao Hiệu trưởng chỉ đạo các Khoa hoàn tất hồ sơ tự đánh giá và đón đoàn đánh giá ngoài.','Đã công bố',1,NULL);
/*!40000 ALTER TABLE `nghi_quyet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nghi_quyet_bieu_quyet`
--

DROP TABLE IF EXISTS `nghi_quyet_bieu_quyet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nghi_quyet_bieu_quyet` (
  `MaNghiQuyet` int unsigned NOT NULL,
  `MaNoiDung` int unsigned NOT NULL,
  PRIMARY KEY (`MaNghiQuyet`,`MaNoiDung`),
  KEY `fk_nqbq_noi_dung` (`MaNoiDung`),
  CONSTRAINT `fk_nqbq_nghi_quyet` FOREIGN KEY (`MaNghiQuyet`) REFERENCES `nghi_quyet` (`MaNghiQuyet`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_nqbq_noi_dung` FOREIGN KEY (`MaNoiDung`) REFERENCES `noi_dung_bieu_quyet` (`MaNoiDung`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nghi_quyet_bieu_quyet`
--

LOCK TABLES `nghi_quyet_bieu_quyet` WRITE;
/*!40000 ALTER TABLE `nghi_quyet_bieu_quyet` DISABLE KEYS */;
INSERT INTO `nghi_quyet_bieu_quyet` VALUES (1,1);
/*!40000 ALTER TABLE `nghi_quyet_bieu_quyet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nghi_quyet_ho_so`
--

DROP TABLE IF EXISTS `nghi_quyet_ho_so`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nghi_quyet_ho_so` (
  `MaNghiQuyet` int unsigned NOT NULL,
  `MaHoSo` int unsigned NOT NULL,
  PRIMARY KEY (`MaNghiQuyet`,`MaHoSo`),
  KEY `fk_nqhs_ho_so` (`MaHoSo`),
  CONSTRAINT `fk_nqhs_ho_so` FOREIGN KEY (`MaHoSo`) REFERENCES `ho_so_trinh` (`MaHoSo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_nqhs_nghi_quyet` FOREIGN KEY (`MaNghiQuyet`) REFERENCES `nghi_quyet` (`MaNghiQuyet`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nghi_quyet_ho_so`
--

LOCK TABLES `nghi_quyet_ho_so` WRITE;
/*!40000 ALTER TABLE `nghi_quyet_ho_so` DISABLE KEYS */;
INSERT INTO `nghi_quyet_ho_so` VALUES (1,1),(2,2),(1,11);
/*!40000 ALTER TABLE `nghi_quyet_ho_so` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nguoi_dung`
--

DROP TABLE IF EXISTS `nguoi_dung`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nguoi_dung` (
  `MaNguoiDung` int unsigned NOT NULL AUTO_INCREMENT,
  `HoTen` varchar(255) NOT NULL,
  `email` varchar(191) NOT NULL,
  `MatKhau` varchar(255) NOT NULL,
  `TrangThai` varchar(255) NOT NULL DEFAULT 'Cho phép',
  `MaDonVi` int unsigned NOT NULL,
  `MaVaiTro` int unsigned NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaNguoiDung`),
  UNIQUE KEY `nguoi_dung_email_unique` (`email`),
  KEY `fk_nguoi_dung_don_vi` (`MaDonVi`),
  KEY `fk_nguoi_dung_vai_tro` (`MaVaiTro`),
  CONSTRAINT `fk_nguoi_dung_don_vi` FOREIGN KEY (`MaDonVi`) REFERENCES `don_vi` (`MaDonVi`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_nguoi_dung_vai_tro` FOREIGN KEY (`MaVaiTro`) REFERENCES `vai_tro` (`MaVaiTro`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nguoi_dung`
--

LOCK TABLES `nguoi_dung` WRITE;
/*!40000 ALTER TABLE `nguoi_dung` DISABLE KEYS */;
INSERT INTO `nguoi_dung` VALUES (1,'Kỹ sư Quản Trị Hệ Thống','admin@stu.edu.vn','$2y$12$YtzQXEaQgnmVU.kVEzjD/ej34wWZTPgR3nY8EN3O6P4sAjxMAtu5y','Hoạt động',6,1,'MbaFWEC00ojbuLoZbhjGmM9ok7eoCjyocSUTWkYi755AcQsfHxEeMEBPs0Xe','2026-10-24 15:50:39','2026-11-11 08:47:49',NULL),(2,'PGS.TS. Trần Văn Chủ Tịch','chutich_hdt@stu.edu.vn','$2y$12$YtzQXEaQgnmVU.kVEzjD/ej34wWZTPgR3nY8EN3O6P4sAjxMAtu5y','Hoạt động',1,2,'vmhm3fGSADA6v4O6u0AX6d5382jZNQyrwnM6JlcPpQTyI0bPUmnniSjZPRRs','2026-10-24 15:50:39','2026-11-11 08:47:49',NULL),(3,'ThS. Nguyễn Thị Thư Ký','thuky_hdt@stu.edu.vn','$2y$12$YtzQXEaQgnmVU.kVEzjD/ej34wWZTPgR3nY8EN3O6P4sAjxMAtu5y','Hoạt động',1,3,'KIGni1WduOXC7lsU4hj0Xep33oXwJBIbPubStqRJLkS5jUckoM06YoDR8R4Y','2026-10-24 15:50:39','2026-11-11 08:47:49',NULL),(4,'TS. Lê Văn Thành Viên','thanhvien_hdt@stu.edu.vn','$2y$12$YtzQXEaQgnmVU.kVEzjD/ej34wWZTPgR3nY8EN3O6P4sAjxMAtu5y','Hoạt động',4,4,'182y42cAfMWzRiQZwXkhyAcdXR4IuNS2obLPIWS5ZvlyuseE8iR3x4kN1b9f','2026-10-24 15:50:39','2026-11-11 08:47:49',NULL),(5,'Văn phòng Hiệu trưởng STU','vphieutruong@stu.edu.vn','$2y$12$YtzQXEaQgnmVU.kVEzjD/ej34wWZTPgR3nY8EN3O6P4sAjxMAtu5y','Hoạt động',2,5,NULL,'2026-10-24 15:50:39','2026-11-11 08:47:49',NULL),(6,'ThS. Hoàng Đơn Vị Trình','daotao@stu.edu.vn','$2y$12$YtzQXEaQgnmVU.kVEzjD/ej34wWZTPgR3nY8EN3O6P4sAjxMAtu5y','Hoạt động',5,6,NULL,'2026-10-24 15:50:39','2026-11-11 08:47:49',NULL),(7,'KS. Phạm Đơn Vị Thi Hành','cntt@stu.edu.vn','$2y$12$YtzQXEaQgnmVU.kVEzjD/ej34wWZTPgR3nY8EN3O6P4sAjxMAtu5y','Hoạt động',3,7,NULL,'2026-10-24 15:50:39','2026-11-11 08:47:49',NULL),(8,'Nguyễn Văn Test','test_user_6adcd3e7773c3@stu.edu.vn','$2y$04$DgsxNwL0E8L2UUlaZgc2H.s55jDSyeXZl16YQCXwRf7RcS/f058cC','Hoạt động',1,1,NULL,'2026-10-24 15:51:03','2026-10-24 15:51:03',NULL),(9,'Họ Tên Đã Cập Nhật','temp_update_6adcd3e7a8152@stu.edu.vn','$2y$04$/EsZkG2n2mJNVsGVqztbCuA6RHOHR.PukdeP8J8u5QdJHqeyaPWoO','Khóa',1,1,NULL,'2026-10-24 15:51:03','2026-10-24 15:51:03',NULL),(10,'Nguyễn Văn Test','test_user_6adcd41acafaa@stu.edu.vn','$2y$04$2qTB7YpDR7QxfjNVZV33LuBsqEo.CSwmZPPzFpfufHrNNlP1.4/Ca','Hoạt động',1,1,NULL,'2026-10-24 15:51:54','2026-10-24 15:51:54',NULL),(11,'Họ Tên Đã Cập Nhật','temp_update_6adcd41b0dacb@stu.edu.vn','$2y$04$ICqXn/4SDNKsjKydO/9DOOKdGcoE445iNfcky0xbcIHPhaV0koIiy','Khóa',1,1,NULL,'2026-10-24 15:51:55','2026-10-24 15:51:55',NULL),(12,'Nguyễn Văn Test','test_user_6adef1888cde1@stu.edu.vn','$2y$04$TiBF38UgU0AzfNcqsPkTLePdmWm98kYuid/yvY3d93GdWS5oi4By2','Hoạt động',1,1,NULL,'2026-10-26 06:22:00','2026-10-26 06:22:00',NULL),(13,'Họ Tên Đã Cập Nhật','temp_update_6adef188c1e4f@stu.edu.vn','$2y$04$KLMHwrfBEG3Q0aWdtVmwSuFXQO8CfxW6zJ10ezoIagU6ge3TsmtIS','Khóa',1,1,NULL,'2026-10-26 06:22:00','2026-11-11 08:47:53',NULL);
/*!40000 ALTER TABLE `nguoi_dung` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nhiem_vu`
--

DROP TABLE IF EXISTS `nhiem_vu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nhiem_vu` (
  `MaNhiemVu` int unsigned NOT NULL AUTO_INCREMENT,
  `TenNhiemVu` varchar(255) NOT NULL,
  `NoiDung` text NOT NULL,
  `NgayGiaoNhiemVu` date DEFAULT NULL,
  `NgayGiao` date DEFAULT NULL,
  `NgayHoanThanhNhiemVu` date DEFAULT NULL,
  `NgayHoanThanh` date DEFAULT NULL,
  `TrangThai` varchar(255) NOT NULL,
  `NgayXacNhanHoanThanh` date DEFAULT NULL,
  `MaNghiQuyet` int unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaNhiemVu`),
  KEY `fk_nhiem_vu_nghi_quyet` (`MaNghiQuyet`),
  CONSTRAINT `fk_nhiem_vu_nghi_quyet` FOREIGN KEY (`MaNghiQuyet`) REFERENCES `nghi_quyet` (`MaNghiQuyet`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nhiem_vu`
--

LOCK TABLES `nhiem_vu` WRITE;
/*!40000 ALTER TABLE `nhiem_vu` DISABLE KEYS */;
INSERT INTO `nhiem_vu` VALUES (1,'Xây dựng Đề án mở ngành Trí tuệ nhân tạo (AI) và Robot','Chủ trì phối hợp cùng Phòng Đào tạo hoàn thiện hồ sơ đề án mở ngành đào tạo mới theo Thông tư 02/2022/TT-BGDĐT.','2026-10-21','2026-10-21','2026-12-31','2026-12-31','Đang thực hiện',NULL,1,NULL),(2,'Nâng cấp phòng thí nghiệm Viễn thông và Mạng máy tính cơ sở 2','Khảo sát hiện trạng, lập dự toán mua sắm trang thiết bị phòng thực hành đáp ứng chuẩn AUN-QA.','2026-10-22','2026-10-22','2026-11-25','2026-11-25','Đang thực hiện',NULL,95,NULL),(3,'Thẩm định định mức học phí khối ngành Kỹ thuật - Công nghệ năm 2026','Xây dựng đề xuất khung học phí mới theo Nghị định 97/2023/NĐ-CP trình Hội đồng trường xem xét.','2026-10-25','2026-10-25','2026-11-30','2026-11-30','Đang thực hiện',NULL,2,NULL),(4,'Rà soát và ban hành chuẩn đầu ra theo Khung trình độ quốc gia','Cập nhật đề cương chi tiết học phần, lấy ý kiến khảo sát từ doanh nghiệp và cựu sinh viên.','2026-09-15','2026-09-15','2026-10-25','2026-10-25','Đang thực hiện',NULL,96,NULL),(5,'Triển khai hệ thống chữ ký số văn bản và lưu trữ điện tử','Cung cấp USB Token và chứng thư số chuyên dùng cho các đơn vị trực thuộc trường.','2026-09-01','2026-09-01','2026-10-15','2026-10-15','Đã hoàn thành','2026-10-14',95,NULL),(6,'Hoàn thiện Quy chế chi tiêu nội bộ năm học 2026 - 2027','Tổng hợp ý kiến đóng góp từ các Khoa/Phòng và xin ý kiến Thường trực Hội đồng trường.','2026-09-10','2026-09-10','2026-10-20','2026-10-20','Đã hoàn thành','2026-10-19',2,NULL),(7,'Nhiệm vụ hoàn thành 100%','Nội dung thực hiện nhiệm vụ hoàn thành 100%',NULL,'2026-11-10',NULL,'2026-11-15','Đã hoàn thành','2026-11-10',1,NULL),(9,'Xây dựng Đề án mở ngành Robot & AI và ban hành chuẩn đầu ra','Chủ trì phối hợp cùng Phòng Đào tạo hoàn thiện hồ sơ đề án mở ngành đào tạo mới theo Thông tư 02/2022/TT-BGDĐT.','2026-10-21','2026-10-21','2026-12-31','2026-12-31','IN_PROGRESS',NULL,1,NULL);
/*!40000 ALTER TABLE `nhiem_vu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `noi_dung_bieu_quyet`
--

DROP TABLE IF EXISTS `noi_dung_bieu_quyet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `noi_dung_bieu_quyet` (
  `MaNoiDung` int unsigned NOT NULL AUTO_INCREMENT,
  `NoiDung` text NOT NULL,
  `ThoiGianMo` datetime DEFAULT NULL,
  `ThoiGianDong` datetime DEFAULT NULL,
  `TrangThai` varchar(255) NOT NULL,
  `KetQuaBieuQuyet` varchar(255) DEFAULT NULL,
  `KetQua` varchar(255) DEFAULT NULL,
  `GhiChu` text,
  `MaChuongTrinh` int unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaNoiDung`),
  KEY `fk_ndbq_chuong_trinh` (`MaChuongTrinh`),
  CONSTRAINT `fk_ndbq_chuong_trinh` FOREIGN KEY (`MaChuongTrinh`) REFERENCES `chuong_trinh_hop` (`MaChuongTrinh`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `noi_dung_bieu_quyet`
--

LOCK TABLES `noi_dung_bieu_quyet` WRITE;
/*!40000 ALTER TABLE `noi_dung_bieu_quyet` DISABLE KEYS */;
INSERT INTO `noi_dung_bieu_quyet` VALUES (1,'Biểu quyết thông qua Đề án thành lập Viện Nghiên cứu Ứng dụng Trí tuệ Nhân tạo STU','2026-11-15 09:00:00','2026-11-15 09:30:00','Đã đóng',NULL,NULL,NULL,1,NULL),(2,'Thông qua Đề án đầu tư nâng cấp phòng thí nghiệm AI & Khoa học dữ liệu năm 2026','2026-11-11 13:48:41','2026-11-13 15:48:41','Đang mở biểu quyết',NULL,NULL,'A, B, C',1,NULL);
/*!40000 ALTER TABLE `noi_dung_bieu_quyet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=274 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',3,'stu_access_token','3a15a8346339379b5fe686025ce351fb503fc03609e5ac45c313768f43834f01','[\"*\"]',NULL,NULL,'2026-10-24 15:50:52','2026-10-24 15:50:52'),(2,'App\\Models\\User',2,'test_token','04bae86725288643275a3301a4311f79be3b7b880d9f23812b1a474b04650ffe','[\"*\"]','2026-10-24 15:50:53',NULL,'2026-10-24 15:50:53','2026-10-24 15:50:53'),(4,'App\\Models\\User',1,'stu_access_token','4a19197ad186ffd20bf0589f99760773ddccabd64afbd338ea95cc1b2e15d712','[\"*\"]',NULL,NULL,'2026-10-24 15:50:54','2026-10-24 15:50:54'),(5,'App\\Models\\User',2,'stu_access_token','350d7050fa342312026793a58e67fec9ac64eb484731ab944e3a2b31795ae1fd','[\"*\"]',NULL,NULL,'2026-10-24 15:50:54','2026-10-24 15:50:54'),(6,'App\\Models\\User',3,'stu_access_token','11d2488e583ec71ed44a0f9b2a0b8698efaa5ab837b8ed84431aab4b69a7218e','[\"*\"]',NULL,NULL,'2026-10-24 15:50:54','2026-10-24 15:50:54'),(7,'App\\Models\\User',4,'stu_access_token','158b9aef584582e67ad07f29131a15b54204783905198d468c4ab2d94146d76e','[\"*\"]',NULL,NULL,'2026-10-24 15:50:55','2026-10-24 15:50:55'),(8,'App\\Models\\User',5,'stu_access_token','6957e6e8e89170a18d70e77b92a449c3ce33a7e32a21f4bdd7a5a1c2883584fd','[\"*\"]',NULL,NULL,'2026-10-24 15:50:55','2026-10-24 15:50:55'),(9,'App\\Models\\User',6,'stu_access_token','f8d14bdbff62204649872ed1a1272eddcbdeb32f29e783d753de0c2adcc1c862','[\"*\"]',NULL,NULL,'2026-10-24 15:50:55','2026-10-24 15:50:55'),(10,'App\\Models\\User',7,'stu_access_token','aade207e94bdd221e2117d2268f97c601f5c92300ab4110c8878040a11063bd4','[\"*\"]',NULL,NULL,'2026-10-24 15:50:56','2026-10-24 15:50:56'),(11,'App\\Models\\User',3,'stu_access_token','0aea94b4a8ebd51d6cf7d14662f0a52f4c3e793ad27e3371cc8a5036a3e8a67a','[\"*\"]',NULL,NULL,'2026-10-24 15:51:47','2026-10-24 15:51:47'),(12,'App\\Models\\User',2,'test_token','2720f42fd9949d39d3caa22b3454e4986f7433a7699fe25f187bb5a5e818982b','[\"*\"]','2026-10-24 15:51:48',NULL,'2026-10-24 15:51:48','2026-10-24 15:51:48'),(14,'App\\Models\\User',1,'stu_access_token','3a280fe9a982abf36e2794e0cd95c8cf8d11e75e2b8c3b381e5d4daaae007200','[\"*\"]',NULL,NULL,'2026-10-24 15:51:48','2026-10-24 15:51:48'),(15,'App\\Models\\User',2,'stu_access_token','9cfcd826e449d62f9c6ef28d88033d4c2ad376125dba9821845f8da1a9ecb5e3','[\"*\"]',NULL,NULL,'2026-10-24 15:51:49','2026-10-24 15:51:49'),(16,'App\\Models\\User',3,'stu_access_token','d97389c89ed36805a6c51139c476c31b5edff441c6d8f7dd736eb332e00d8291','[\"*\"]',NULL,NULL,'2026-10-24 15:51:49','2026-10-24 15:51:49'),(17,'App\\Models\\User',4,'stu_access_token','ba7893a8198c2610c5120a2324efa3715eb3375082647d7ad38a0d45f7148146','[\"*\"]',NULL,NULL,'2026-10-24 15:51:49','2026-10-24 15:51:49'),(18,'App\\Models\\User',5,'stu_access_token','5cc621b22af811af9d2f120040f1969acd4099506da266ee031940cfb6312c6b','[\"*\"]',NULL,NULL,'2026-10-24 15:51:49','2026-10-24 15:51:49'),(19,'App\\Models\\User',6,'stu_access_token','c8f83059861011e3e55e712bd6eac575c0ba93490be094622bed5adf918d91f8','[\"*\"]',NULL,NULL,'2026-10-24 15:51:50','2026-10-24 15:51:50'),(20,'App\\Models\\User',7,'stu_access_token','5a1d95c94de87482c6ff809ca2c4e77719e0f542fc33925c99a422cf0cebfe04','[\"*\"]',NULL,NULL,'2026-10-24 15:51:50','2026-10-24 15:51:50'),(24,'App\\Models\\User',3,'stu_access_token','871eac7a6b86a4b56644de81907316e58ff2401f955ac26e535b6d032f26f159','[\"*\"]',NULL,NULL,'2026-10-26 06:21:45','2026-10-26 06:21:45'),(25,'App\\Models\\User',2,'test_token','51fcdc03ca2ee11668ece39987fbc12a179e4d22aae17889b04ea95745c73b51','[\"*\"]','2026-10-26 06:21:47',NULL,'2026-10-26 06:21:47','2026-10-26 06:21:47'),(27,'App\\Models\\User',1,'stu_access_token','e629426ca53c4151f2c3aef3084c7dede04972125d37b450124e52da6c36d570','[\"*\"]',NULL,NULL,'2026-10-26 06:21:48','2026-10-26 06:21:48'),(28,'App\\Models\\User',2,'stu_access_token','dd94a2e41ba163a7e798c117304bf5e0aaf19c3d6102b082b5f122f6f7ad03c0','[\"*\"]',NULL,NULL,'2026-10-26 06:21:48','2026-10-26 06:21:48'),(29,'App\\Models\\User',3,'stu_access_token','bca593ff83beccdeec3690c21df518a0d8bd5efedbb7cae17c97bd5b3c2094c7','[\"*\"]',NULL,NULL,'2026-10-26 06:21:49','2026-10-26 06:21:49'),(30,'App\\Models\\User',4,'stu_access_token','c78edef4b32868f4e9fba135077ea3dd9032874682623c57921d62e960228a82','[\"*\"]',NULL,NULL,'2026-10-26 06:21:49','2026-10-26 06:21:49'),(31,'App\\Models\\User',5,'stu_access_token','7ea336686f62b898dc6e2735629861d4d2dedcc0449beae669271ee6c20e32c1','[\"*\"]',NULL,NULL,'2026-10-26 06:21:49','2026-10-26 06:21:49'),(32,'App\\Models\\User',6,'stu_access_token','bcba3d0081f99ddc4bfd190466669feca00d06a3c4742ab19d5f5eaa65d4b16a','[\"*\"]',NULL,NULL,'2026-10-26 06:21:50','2026-10-26 06:21:50'),(33,'App\\Models\\User',7,'stu_access_token','d7fe5c49b534cba149bb82006832a6f17578798f15f0f972eb8ee60a7348b457','[\"*\"]',NULL,NULL,'2026-10-26 06:21:50','2026-10-26 06:21:50'),(37,'App\\Models\\User',1,'stu_access_token','a97d1649461002a0b08e5a7f4fd7251f570d0e72e2bfb1a4ef8d41fa60687785','[\"*\"]','2026-10-26 09:37:47',NULL,'2026-10-26 08:39:00','2026-10-26 09:37:47'),(45,'App\\Models\\User',1,'stu_access_token','e875aff40173dfd1a3d5c1d0020c32fe5105ee7fa21b98dd845192a437dcc330','[\"*\"]','2026-10-27 04:17:25',NULL,'2026-10-27 03:58:02','2026-10-27 04:17:25'),(46,'App\\Models\\User',1,'stu_access_token','d632490257e76d9459fa1414c3502376eb4f8e2b0f12fb6043ec99850439967f','[\"*\"]','2026-10-28 03:32:57',NULL,'2026-10-28 03:27:40','2026-10-28 03:32:57'),(47,'App\\Models\\User',1,'stu_access_token','f9fd48c04aa289e7fc7080f296ce66de2ada2d7013dd518078ff7f4b1faeeb80','[\"*\"]','2026-10-28 05:40:45',NULL,'2026-10-28 04:05:53','2026-10-28 05:40:45'),(122,'App\\Models\\User',1,'stu_access_token','39abede1c71441abfb86f038b17784b324babdf1dabc07407ee35ed86f80f6c8','[\"*\"]','2026-11-06 07:05:16',NULL,'2026-11-06 07:05:04','2026-11-06 07:05:16'),(163,'App\\Models\\User',3,'stu_access_token','ac1bb0b8066ebf205d313fa476735207177e42c6d3c4ba8706cd773d10ae9dec','[\"*\"]','2026-11-07 08:40:30',NULL,'2026-11-07 08:40:10','2026-11-07 08:40:30');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phan_cong_nhiem_vu`
--

DROP TABLE IF EXISTS `phan_cong_nhiem_vu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phan_cong_nhiem_vu` (
  `MaPhanCong` int unsigned NOT NULL AUTO_INCREMENT,
  `VaiTroThucHien` varchar(255) NOT NULL,
  `TrangThaiThucHien` varchar(255) NOT NULL,
  `TenNguoiPhuTrach` varchar(255) NOT NULL DEFAULT 'Người phụ trách đơn vị',
  `MaNhiemVu` int unsigned NOT NULL,
  `MaDonVi` int unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaPhanCong`),
  KEY `fk_pcnv_nhiem_vu` (`MaNhiemVu`),
  KEY `fk_pcnv_don_vi` (`MaDonVi`),
  CONSTRAINT `fk_pcnv_don_vi` FOREIGN KEY (`MaDonVi`) REFERENCES `don_vi` (`MaDonVi`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pcnv_nhiem_vu` FOREIGN KEY (`MaNhiemVu`) REFERENCES `nhiem_vu` (`MaNhiemVu`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phan_cong_nhiem_vu`
--

LOCK TABLES `phan_cong_nhiem_vu` WRITE;
/*!40000 ALTER TABLE `phan_cong_nhiem_vu` DISABLE KEYS */;
INSERT INTO `phan_cong_nhiem_vu` VALUES (1,'Đơn vị chủ trì','IN_PROGRESS','TS. Nguyễn Hoàng Nam',1,3,NULL),(2,'Đơn vị chủ trì','IN_PROGRESS','ThS. Lê Hoàng Long',2,4,NULL),(3,'Đơn vị chủ trì','IN_PROGRESS','ThS. Phan Đình Trí',3,2,NULL),(4,'Đơn vị chủ trì','IN_PROGRESS','TS. Vũ Hải Đăng',4,5,NULL),(5,'Đơn vị chủ trì','COMPLETED','ThS. Phạm Quốc Tuấn',5,6,NULL),(6,'Đơn vị chủ trì','COMPLETED','ThS. Trần Văn Thư Ký',6,1,NULL),(7,'CHỦ TRÌ','Hoàn tất','Trưởng ban',7,1,NULL),(10,'CHỦ TRÌ','Đang thực hiện','KS. Phạm Đơn Vị Thi Hành',9,3,NULL);
/*!40000 ALTER TABLE `phan_cong_nhiem_vu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phien_hop`
--

DROP TABLE IF EXISTS `phien_hop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phien_hop` (
  `MaPhienHop` int unsigned NOT NULL AUTO_INCREMENT,
  `TenPhienHop` varchar(255) NOT NULL,
  `ThoiGianBatDau` datetime NOT NULL,
  `ThoiGianKetThuc` datetime NOT NULL,
  `NgayHop` date NOT NULL,
  `DiaDiem` varchar(255) NOT NULL,
  `HinhThuc` varchar(255) NOT NULL,
  `TrangThai` varchar(255) NOT NULL,
  `GhiChu` text,
  `MaNguoiDung` int unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaPhienHop`),
  KEY `phien_hop_manguoidung_foreign` (`MaNguoiDung`),
  CONSTRAINT `phien_hop_manguoidung_foreign` FOREIGN KEY (`MaNguoiDung`) REFERENCES `nguoi_dung` (`MaNguoiDung`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phien_hop`
--

LOCK TABLES `phien_hop` WRITE;
/*!40000 ALTER TABLE `phien_hop` DISABLE KEYS */;
INSERT INTO `phien_hop` VALUES (1,'Phiên họp lần thứ 5 Hội đồng trường STU (Khóa IV)','2026-11-15 08:30:00','2026-11-15 11:45:00','2026-11-15','Phòng họp A101, Nhà A - Trụ sở chính STU','Trực tiếp','Sắp diễn ra','A, B, C',3,NULL),(2,'Phiên họp bất thường Hội đồng trường STU tháng 12/2026','2026-12-20 09:00:00','2026-12-20 11:30:00','2026-12-20','Phòng họp Hội đồng trường A202','Trực tiếp','Đang chuẩn bị','A, B, C',3,NULL),(3,'Phiên họp chuyên đề Hội đồng trường Quý IV/2026','2026-11-25 08:30:00','2026-11-25 11:30:00','2026-11-25','Phòng họp Hội đồng trường - Lầu 2 Khu A','Trực tiếp','Đang chuẩn bị','A, B, C',1,NULL);
/*!40000 ALTER TABLE `phien_hop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tai_lieu`
--

DROP TABLE IF EXISTS `tai_lieu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tai_lieu` (
  `MaTaiLieu` int unsigned NOT NULL AUTO_INCREMENT,
  `TenTaiLieu` varchar(255) NOT NULL,
  `LoaiTaiLieu` varchar(255) NOT NULL,
  `DuongDan` varchar(255) NOT NULL,
  `DinhDang` varchar(255) NOT NULL,
  `DungLuong` int DEFAULT NULL,
  `NgayTaiLen` datetime NOT NULL,
  `MaNguoiDung` int unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaTaiLieu`),
  KEY `fk_tai_lieu_nguoi_dung` (`MaNguoiDung`),
  CONSTRAINT `fk_tai_lieu_nguoi_dung` FOREIGN KEY (`MaNguoiDung`) REFERENCES `nguoi_dung` (`MaNguoiDung`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tai_lieu`
--

LOCK TABLES `tai_lieu` WRITE;
/*!40000 ALTER TABLE `tai_lieu` DISABLE KEYS */;
INSERT INTO `tai_lieu` VALUES (1,'Quy_che_to_chuc_va_hoat_dong_HDT_STU.pdf','Quy chế','documents/regulations/Quy_che_to_chuc_va_hoat_dong_HDT_STU.pdf','application/pdf',1572864,'2026-01-10 09:00:00',3,NULL),(2,'Chuong_trinh_hop_phien_5_chinh_thuc.pdf','Chương trình họp','documents/meetings/Chuong_trinh_hop_phien_5_chinh_thuc.pdf','application/pdf',460800,'2026-11-01 14:30:00',3,NULL),(3,'To_trinh_kien_toan_nhan_su_mat.pdf','Tài liệu nội bộ','documents/internal/To_trinh_kien_toan_nhan_su_mat.pdf','application/pdf',798720,'2026-11-05 10:15:00',3,NULL),(4,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1793946134_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-06 13:22:14',3,NULL),(5,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1793946323_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-06 13:25:23',3,NULL),(6,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1793946360_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-06 13:26:00',3,NULL),(7,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1793946587_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-06 13:29:47',3,NULL),(8,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1793948384_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-06 13:59:44',3,NULL),(9,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1793950201_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-06 14:30:01',3,NULL),(10,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1793951032_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-06 14:43:52',3,NULL),(11,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1793951807_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-06 14:56:47',3,NULL),(12,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794039381_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-07 15:16:21',3,NULL),(13,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794041490_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-07 15:51:30',3,NULL),(14,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794043270_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-07 16:21:10',3,NULL),(15,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794043367_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-07 16:22:47',3,NULL),(16,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794043631_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-07 16:27:11',3,NULL),(17,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794044672_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-07 16:44:32',3,NULL),(18,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794045166_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-07 16:52:46',3,NULL),(19,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794046136_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-07 17:08:56',3,NULL),(20,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794047774_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-07 17:36:14',3,NULL),(21,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794048374_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-07 17:46:14',3,NULL),(22,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794049868_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-07 18:11:08',3,NULL),(23,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794052325_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-07 18:52:05',3,NULL),(24,'MAU_LVTN_Đợt T10.2026.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794291701_MAU_LVTN______t_T10.2026.pdf','pdf',914898,'2026-11-10 13:21:41',2,NULL),(25,'to_trinh_thi_nghiem.pdf','Tờ trình đính kèm','/storage/documents/submissions/1794291808_to_trinh_thi_nghiem.pdf','pdf',1048576,'2026-11-10 13:23:28',3,NULL);
/*!40000 ALTER TABLE `tai_lieu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tai_lieu_bao_cao`
--

DROP TABLE IF EXISTS `tai_lieu_bao_cao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tai_lieu_bao_cao` (
  `MaTaiLieu` int unsigned NOT NULL,
  `MaBaoCao` int unsigned NOT NULL,
  PRIMARY KEY (`MaTaiLieu`,`MaBaoCao`),
  KEY `fk_tlbc_bao_cao` (`MaBaoCao`),
  CONSTRAINT `fk_tlbc_bao_cao` FOREIGN KEY (`MaBaoCao`) REFERENCES `bao_cao` (`MaBaoCao`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tlbc_tai_lieu` FOREIGN KEY (`MaTaiLieu`) REFERENCES `tai_lieu` (`MaTaiLieu`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tai_lieu_bao_cao`
--

LOCK TABLES `tai_lieu_bao_cao` WRITE;
/*!40000 ALTER TABLE `tai_lieu_bao_cao` DISABLE KEYS */;
/*!40000 ALTER TABLE `tai_lieu_bao_cao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tai_lieu_ho_so`
--

DROP TABLE IF EXISTS `tai_lieu_ho_so`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tai_lieu_ho_so` (
  `MaTaiLieu` int unsigned NOT NULL,
  `MaHoSo` int unsigned NOT NULL,
  PRIMARY KEY (`MaTaiLieu`,`MaHoSo`),
  KEY `fk_tlhs_ho_so` (`MaHoSo`),
  CONSTRAINT `fk_tlhs_ho_so` FOREIGN KEY (`MaHoSo`) REFERENCES `ho_so_trinh` (`MaHoSo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tlhs_tai_lieu` FOREIGN KEY (`MaTaiLieu`) REFERENCES `tai_lieu` (`MaTaiLieu`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tai_lieu_ho_so`
--

LOCK TABLES `tai_lieu_ho_so` WRITE;
/*!40000 ALTER TABLE `tai_lieu_ho_so` DISABLE KEYS */;
INSERT INTO `tai_lieu_ho_so` VALUES (1,1),(2,2),(3,3),(4,4),(5,5),(6,6),(7,7),(8,8),(9,9);
/*!40000 ALTER TABLE `tai_lieu_ho_so` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tai_lieu_nghi_quyet`
--

DROP TABLE IF EXISTS `tai_lieu_nghi_quyet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tai_lieu_nghi_quyet` (
  `MaTaiLieu` int unsigned NOT NULL,
  `MaNghiQuyet` int unsigned NOT NULL,
  PRIMARY KEY (`MaTaiLieu`,`MaNghiQuyet`),
  KEY `fk_tlnq_nghi_quyet` (`MaNghiQuyet`),
  CONSTRAINT `fk_tlnq_nghi_quyet` FOREIGN KEY (`MaNghiQuyet`) REFERENCES `nghi_quyet` (`MaNghiQuyet`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tlnq_tai_lieu` FOREIGN KEY (`MaTaiLieu`) REFERENCES `tai_lieu` (`MaTaiLieu`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tai_lieu_nghi_quyet`
--

LOCK TABLES `tai_lieu_nghi_quyet` WRITE;
/*!40000 ALTER TABLE `tai_lieu_nghi_quyet` DISABLE KEYS */;
INSERT INTO `tai_lieu_nghi_quyet` VALUES (1,1);
/*!40000 ALTER TABLE `tai_lieu_nghi_quyet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tai_lieu_phien_hop`
--

DROP TABLE IF EXISTS `tai_lieu_phien_hop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tai_lieu_phien_hop` (
  `MaTaiLieu` int unsigned NOT NULL,
  `MaPhienHop` int unsigned NOT NULL,
  PRIMARY KEY (`MaTaiLieu`,`MaPhienHop`),
  KEY `fk_tlph_phien_hop` (`MaPhienHop`),
  CONSTRAINT `fk_tlph_phien_hop` FOREIGN KEY (`MaPhienHop`) REFERENCES `phien_hop` (`MaPhienHop`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tlph_tai_lieu` FOREIGN KEY (`MaTaiLieu`) REFERENCES `tai_lieu` (`MaTaiLieu`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tai_lieu_phien_hop`
--

LOCK TABLES `tai_lieu_phien_hop` WRITE;
/*!40000 ALTER TABLE `tai_lieu_phien_hop` DISABLE KEYS */;
INSERT INTO `tai_lieu_phien_hop` VALUES (2,1),(3,1);
/*!40000 ALTER TABLE `tai_lieu_phien_hop` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tham_du`
--

DROP TABLE IF EXISTS `tham_du`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tham_du` (
  `MaThamDu` int unsigned NOT NULL AUTO_INCREMENT,
  `TrangThaiThamDu` varchar(255) NOT NULL,
  `ThoiDiemXacNhan` datetime DEFAULT NULL,
  `GhiChu` text,
  `MaPhienHop` int unsigned NOT NULL,
  `MaThanhVien` int unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaThamDu`),
  UNIQUE KEY `tham_du_phien_hop_thanh_vien_unique` (`MaPhienHop`,`MaThanhVien`),
  KEY `fk_tham_du_thanh_vien` (`MaThanhVien`),
  CONSTRAINT `fk_tham_du_phien_hop` FOREIGN KEY (`MaPhienHop`) REFERENCES `phien_hop` (`MaPhienHop`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tham_du_thanh_vien` FOREIGN KEY (`MaThanhVien`) REFERENCES `thanh_vien_hdt` (`MaThanhVien`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=160 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tham_du`
--

LOCK TABLES `tham_du` WRITE;
/*!40000 ALTER TABLE `tham_du` DISABLE KEYS */;
INSERT INTO `tham_du` VALUES (1,'Có mặt','2026-11-15 08:15:00','Có mặt đúng giờ',1,1,NULL),(2,'Có mặt','2026-10-26 13:22:02','Có mặt đúng giờ',1,2,NULL),(4,'Có mặt',NULL,NULL,2,1,NULL),(5,'Có mặt',NULL,NULL,2,2,NULL),(159,'Có mặt','2026-11-15 08:15:00','Có mặt đúng giờ',1,6,NULL);
/*!40000 ALTER TABLE `tham_du` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thanh_vien_hdt`
--

DROP TABLE IF EXISTS `thanh_vien_hdt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `thanh_vien_hdt` (
  `MaThanhVien` int unsigned NOT NULL AUTO_INCREMENT,
  `ChucDanh` varchar(255) NOT NULL DEFAULT 'Thành viên',
  `ChucDanhHoiDongTruong` varchar(255) DEFAULT NULL,
  `NhiemKy` varchar(255) NOT NULL,
  `NgayBatDauThamGia` date NOT NULL,
  `NgayBatDau` date DEFAULT NULL,
  `NgayKetThuc` date DEFAULT NULL,
  `CoQuyenBieuQuyet` tinyint(1) NOT NULL DEFAULT '1',
  `MaNguoiDung` int unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaThanhVien`),
  KEY `fk_thanh_vien_nguoi_dung` (`MaNguoiDung`),
  CONSTRAINT `fk_thanh_vien_nguoi_dung` FOREIGN KEY (`MaNguoiDung`) REFERENCES `nguoi_dung` (`MaNguoiDung`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thanh_vien_hdt`
--

LOCK TABLES `thanh_vien_hdt` WRITE;
/*!40000 ALTER TABLE `thanh_vien_hdt` DISABLE KEYS */;
INSERT INTO `thanh_vien_hdt` VALUES (1,'Chủ tịch Hội đồng trường','Chủ tịch Hội đồng trường','2020-2025','2020-09-01','2020-09-01','2025-08-31',1,2,NULL),(2,'Thư ký Hội đồng trường','Thư ký Hội đồng trường','2020-2025','2020-09-01','2020-09-01','2025-08-31',1,3,NULL),(6,'Thành viên Hội đồng trường','Thành viên Hội đồng trường','2020-2025','2020-09-01','2020-09-01','2025-08-31',1,4,NULL);
/*!40000 ALTER TABLE `thanh_vien_hdt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uy_quyen_bieu_quyet`
--

DROP TABLE IF EXISTS `uy_quyen_bieu_quyet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `uy_quyen_bieu_quyet` (
  `MaUyQuyen` int unsigned NOT NULL AUTO_INCREMENT,
  `NgayUyQuyen` date NOT NULL,
  `PhamViUyQuyen` varchar(255) NOT NULL,
  `TaiLieuUyQuyen` varchar(255) NOT NULL,
  `TrangThai` varchar(255) NOT NULL,
  `MaPhienHop` int unsigned NOT NULL,
  `MaThanhVienUyQuyen` int unsigned NOT NULL,
  `MaThanhVienNhanUyQuyen` int unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaUyQuyen`),
  KEY `fk_uq_phien_hop` (`MaPhienHop`),
  KEY `fk_uq_tv_uy_quyen` (`MaThanhVienUyQuyen`),
  KEY `fk_uq_tv_nhan_uy_quyen` (`MaThanhVienNhanUyQuyen`),
  CONSTRAINT `fk_uq_phien_hop` FOREIGN KEY (`MaPhienHop`) REFERENCES `phien_hop` (`MaPhienHop`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_uq_tv_nhan_uy_quyen` FOREIGN KEY (`MaThanhVienNhanUyQuyen`) REFERENCES `thanh_vien_hdt` (`MaThanhVien`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_uq_tv_uy_quyen` FOREIGN KEY (`MaThanhVienUyQuyen`) REFERENCES `thanh_vien_hdt` (`MaThanhVien`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `chk_thanh_vien_khac_nhau` CHECK ((`MaThanhVienUyQuyen` <> `MaThanhVienNhanUyQuyen`))
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uy_quyen_bieu_quyet`
--

LOCK TABLES `uy_quyen_bieu_quyet` WRITE;
/*!40000 ALTER TABLE `uy_quyen_bieu_quyet` DISABLE KEYS */;
INSERT INTO `uy_quyen_bieu_quyet` VALUES (1,'2026-10-24','Ủy quyền biểu quyết toàn bộ phiên họp','giay_uy_quyen_test.pdf','Hợp lệ',1,1,2,NULL),(2,'2026-10-24','Ủy quyền biểu quyết toàn bộ phiên họp','giay_uy_quyen_test.pdf','Hợp lệ',1,1,2,NULL),(3,'2026-10-26','Ủy quyền biểu quyết toàn bộ phiên họp','giay_uy_quyen_test.pdf','Hợp lệ',1,1,2,NULL);
/*!40000 ALTER TABLE `uy_quyen_bieu_quyet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vai_tro`
--

DROP TABLE IF EXISTS `vai_tro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vai_tro` (
  `MaVaiTro` int unsigned NOT NULL AUTO_INCREMENT,
  `TenVaiTro` varchar(191) NOT NULL,
  `MoTa` text,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MaVaiTro`),
  UNIQUE KEY `vai_tro_tenvaitro_unique` (`TenVaiTro`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vai_tro`
--

LOCK TABLES `vai_tro` WRITE;
/*!40000 ALTER TABLE `vai_tro` DISABLE KEYS */;
INSERT INTO `vai_tro` VALUES (1,'ADMIN','Quản trị viên hệ thống',NULL),(2,'CHAIR','Chủ tịch Hội đồng trường',NULL),(3,'SECRETARY','Thư ký Hội đồng trường',NULL),(4,'MEMBER','Thành viên Hội đồng trường',NULL),(5,'RECTOR_OFFICE','Văn phòng Hiệu trưởng',NULL),(6,'UNIT_REP','Đại diện đơn vị trình hồ sơ',NULL),(7,'EXECUTING_UNIT','Đơn vị thực hiện nghị quyết',NULL);
/*!40000 ALTER TABLE `vai_tro` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-16 13:35:13
