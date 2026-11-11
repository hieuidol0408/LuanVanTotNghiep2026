import React from "react";
import { Routes, Route, Navigate, Outlet } from "react-router-dom";
import AppShell from "../thanh-phan/bo-cuc/KhungUngDung";
import ProtectedRoute from "./TuyenBaoVe";

// Auth Pages
import LoginPage from "../tinh-nang/xac-thuc/trang/TrangDangNhap";
import UnauthorizedPage from "../trang/TrangKhongCoQuyen";

// Dashboard Page
import DashboardPage from "../tinh-nang/bang-dieu-hanh/trang/TrangBangDieuHanh";
import ComponentShowcase from "../trang/TrangThuVienThanhPhan";

// Meeting Management Feature Pages
import MeetingListPage from "../tinh-nang/phien-hop/trang/TrangDanhSachPhienHop";
import MeetingFormPage from "../tinh-nang/phien-hop/trang/TrangTaoPhienHop";
import MeetingDetailPage from "../tinh-nang/phien-hop/trang/TrangChiTietPhienHop";

// Dossier Management Feature Pages
import DossierListPage from "../tinh-nang/ho-so-trinh/trang/TrangDanhSachHoSo";

// Voting Management Feature Pages
import VotingRoomPage from "../tinh-nang/bieu-quyet/trang/TrangPhongBieuQuyet";

// Resolution Management Feature Pages
import ResolutionListPage from "../tinh-nang/nghi-quyet/trang/TrangDanhSachNghiQuyet";

// Task Management Feature Pages
import TaskListPage from "../tinh-nang/nhiem-vu/trang/TrangDanhSachNhiemVu";

// Council Management Feature Pages
import CouncilMemberListPage from "../tinh-nang/hoi-dong/trang/TrangDanhSachThanhVien";

// Document Management Feature Pages
import DocumentListPage from "../tinh-nang/tai-lieu/trang/TrangDanhSachTaiLieu";

// User & Role Management Feature Pages (UC6)
import UserListPage from "../tinh-nang/nguoi-dung/trang/TrangDanhSachNguoiDung";

import PageHeader from "../thanh-phan/bo-cuc/TieuDeTrang";
import EmptyState from "../thanh-phan/giao-dien/TrangThaiTrong";
import { Construction } from "lucide-react";

function ModulePlaceholder({ title, description }) {
  return (
    <div>
      <PageHeader
        title={title}
        description={description}
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: title, path: "" }
        ]}
      />
      <div className="card">
        <EmptyState
          icon={Construction}
          title={`Phân hệ ${title}`}
          description="Nền tảng giao diện đã sẵn sàng. Nghiệp vụ chi tiết của phân hệ này sẽ được tích hợp trong giai đoạn Sprint nghiệp vụ tiếp theo."
        />
      </div>
    </div>
  );
}

// Layout bọc các trang được bảo vệ bằng ProtectedRoute & AppShell
function AuthenticatedLayout() {
  return (
    <ProtectedRoute>
      <AppShell>
        <Outlet />
      </AppShell>
    </ProtectedRoute>
  );
}

export default function AppRoutes() {
  return (
    <Routes>
      {/* Tuyến đường Công khai: Đăng nhập */}
      <Route path="/login" element={<LoginPage />} />

      {/* Tuyến đường Bảo vệ: Yêu cầu đăng nhập & kiểm tra thẩm quyền RBAC */}
      <Route element={<AuthenticatedLayout />}>
        {/* Route Bảng điều hành Tổng quan */}
        <Route path="/" element={<DashboardPage />} />

        {/* Route Kho thành phần giao diện dùng chung */}
        <Route path="/components" element={<ComponentShowcase />} />

        {/* Phân hệ Quản lý Phiên họp Hội đồng trường */}
        <Route path="/meetings" element={<MeetingListPage />} />
        <Route path="/meetings/create" element={<MeetingFormPage />} />
        <Route path="/meetings/:id" element={<MeetingDetailPage />} />
        <Route path="/meetings/:id/edit" element={<MeetingFormPage />} />

        {/* Các Tuyến đường Nghiệp vụ theo vai trò */}
        <Route
          path="/councils"
          element={<CouncilMemberListPage />}
        />
        <Route
          path="/dossiers"
          element={<DossierListPage />}
        />
        <Route
          path="/voting"
          element={<VotingRoomPage />}
        />
        <Route
          path="/resolutions"
          element={<ResolutionListPage />}
        />
        <Route
          path="/tasks"
          element={<TaskListPage />}
        />
        <Route
          path="/documents"
          element={<DocumentListPage />}
        />
        <Route
          path="/users"
          element={<UserListPage />}
        />

        {/* Trang thông báo Không đủ quyền truy cập (403 Forbidden) */}
        <Route path="/unauthorized" element={<UnauthorizedPage />} />
      </Route>

      {/* Catch-all redirect */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
