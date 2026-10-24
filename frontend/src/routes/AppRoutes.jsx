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

// Voting Management Feature Pages
import VotingRoomPage from "../features/voting/pages/VotingRoomPage";

// Resolution Management Feature Pages
import ResolutionListPage from "../features/resolutions/pages/ResolutionListPage";

// Task Management Feature Pages
import TaskListPage from "../features/tasks/pages/TaskListPage";

// Council Management Feature Pages
import CouncilMemberListPage from "../features/councils/pages/CouncilMemberListPage";

// Document Management Feature Pages
import DocumentListPage from "../features/documents/pages/DocumentListPage";

// User & Role Management Feature Pages (UC6)
import UserListPage from "../features/users/pages/UserListPage";

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
