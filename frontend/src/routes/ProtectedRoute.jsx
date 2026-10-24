import React from "react";
import { Navigate, useLocation, Outlet } from "react-router-dom";
import { useAuth } from "../features/auth/context/AuthContext";
import LoadingSkeleton from "../components/ui/LoadingSkeleton";
import UnauthorizedPage from "../pages/UnauthorizedPage";

export default function ProtectedRoute({ allowedRoles, children }) {
  const { isAuthenticated, isLoading, role, canAccessRoute } = useAuth();
  const location = useLocation();

  // 1. Trạng thái đang tải dữ liệu phiên làm việc
  if (isLoading) {
    return (
      <div style={{ padding: "var(--space-8)", maxWidth: "1200px", margin: "0 auto" }}>
        <LoadingSkeleton height="60px" borderRadius="var(--radius-lg)" />
        <div style={{ marginTop: "var(--space-4)" }}>
          <LoadingSkeleton height="200px" borderRadius="var(--radius-lg)" />
        </div>
        <div style={{ marginTop: "var(--space-4)" }}>
          <LoadingSkeleton height="350px" borderRadius="var(--radius-lg)" />
        </div>
      </div>
    );
  }

  // 2. Chưa đăng nhập -> Chuyển hướng sang màn hình đăng nhập kèm thông tin trang trước đó
  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  // 3. Kiểm tra vai trò nếu route có yêu cầu danh sách vai trò cụ thể
  if (allowedRoles && allowedRoles.length > 0 && !allowedRoles.includes(role)) {
    return <UnauthorizedPage />;
  }

  // 4. Kiểm tra quyền truy cập theo ma trận phân quyền URL
  if (!canAccessRoute(location.pathname)) {
    return <UnauthorizedPage />;
  }

  // 5. Thỏa mãn điều kiện bảo mật -> Render nội dung trang
  return children ? children : <Outlet />;
}
