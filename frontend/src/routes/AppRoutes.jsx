import React from "react";
import { Routes, Route, Navigate, Outlet } from "react-router-dom";
import AppShell from "../components/layout/AppShell";
import ProtectedRoute from "./ProtectedRoute";

// Auth Pages
import LoginPage from "../features/auth/pages/LoginPage";
import UnauthorizedPage from "../pages/UnauthorizedPage";

// Dashboard Page
import DashboardPage from "../features/dashboard/pages/DashboardPage";
import ComponentShowcase from "../pages/ComponentShowcase";

// Meeting Management Feature Pages
import MeetingListPage from "../features/meetings/pages/MeetingListPage";
import MeetingFormPage from "../features/meetings/pages/MeetingFormPage";
import MeetingDetailPage from "../features/meetings/pages/MeetingDetailPage";

// Dossier Management Feature Pages
import DossierListPage from "../features/dossiers/pages/DossierListPage";

import PageHeader from "../components/layout/PageHeader";
import EmptyState from "../components/ui/EmptyState";
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
          element={<ModulePlaceholder title="Quản lý Hội đồng trường" description="Quản lý danh sách các khóa nhiệm kỳ và thành viên Hội đồng trường STU." />}
        />
        <Route
          path="/dossiers"
          element={<DossierListPage />}
        />
        <Route
          path="/voting"
          element={<ModulePlaceholder title="Quản lý Biểu quyết & Kiểm phiếu" description="Khởi tạo nội dung biểu quyết, bỏ phiếu kín/công khai và tự động kiểm phiếu." />}
        />
        <Route
          path="/resolutions"
          element={<ModulePlaceholder title="Biên bản & Nghị quyết HĐT" description="Quản lý biên bản họp và ban hành các Nghị quyết chính thức của trường STU." />}
        />
        <Route
          path="/tasks"
          element={<ModulePlaceholder title="Phân rã Nhiệm vụ & Theo dõi thực hiện" description="Tách nhiệm vụ từ nghị quyết, phân công đơn vị chủ trì và theo dõi tiến độ, minh chứng." />}
        />
        <Route
          path="/documents"
          element={<ModulePlaceholder title="Kho Văn bản & Tài liệu" description="Kho lưu trữ tập trung các văn bản quy chế, biểu mẫu và tài liệu HĐT STU." />}
        />
        <Route
          path="/users"
          element={<ModulePlaceholder title="Quản lý Người dùng & Phân quyền" description="Quản lý tài khoản, vai trò và ma trận phân quyền hệ thống." />}
        />

        {/* Trang thông báo Không đủ quyền truy cập (403 Forbidden) */}
        <Route path="/unauthorized" element={<UnauthorizedPage />} />
      </Route>

      {/* Catch-all redirect */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
