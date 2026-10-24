import React from "react";
import { Routes, Route, Navigate } from "react-router-dom";
import DashboardPage from "../features/dashboard/pages/DashboardPage";
import ComponentShowcase from "../pages/ComponentShowcase";
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

export default function AppRoutes() {
  return (
    <Routes>
      {/* Route Màn hình Dashboard Tổng quan */}
      <Route path="/" element={<DashboardPage />} />
      
      {/* Route Kho thành phần giao diện dùng chung */}
      <Route path="/components" element={<ComponentShowcase />} />

      {/* Các Tuyến đường Nghiệp vụ (Sẵn sàng kết nối ở các giai đoạn sau) */}
      <Route
        path="/councils"
        element={<ModulePlaceholder title="Quản lý Hội đồng trường" description="Quản lý danh sách các khóa nhiệm kỳ và thành viên Hội đồng trường STU." />}
      />
      <Route
        path="/dossiers"
        element={<ModulePlaceholder title="Quản lý Hồ sơ trình" description="Quy trình nộp, tiếp nhận và thẩm tra hồ sơ tờ trình từ các đơn vị." />}
      />
      <Route
        path="/meetings"
        element={<ModulePlaceholder title="Quản lý Phiên họp & Tài liệu" description="Lập lịch họp, xây dựng chương trình nghị sự (Agenda), điểm danh và tài liệu bảo mật." />}
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

      {/* Catch-all redirect */}
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
