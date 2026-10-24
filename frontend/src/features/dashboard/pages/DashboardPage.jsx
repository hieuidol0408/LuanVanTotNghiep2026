import React, { useState } from "react";
import PageHeader from "../../../components/layout/PageHeader";
import LoadingSkeleton from "../../../components/ui/LoadingSkeleton";
import ErrorState from "../../../components/ui/ErrorState";
import { useToast } from "../../../components/ui/Toast";
import WelcomeBanner from "../components/WelcomeBanner";
import DashboardStats from "../components/DashboardStats";
import UrgentTasksSection from "../components/UrgentTasksSection";
import UpcomingMeetingsSection from "../components/UpcomingMeetingsSection";
import PendingDossiersSection from "../components/PendingDossiersSection";
import ResolutionProgressSection from "../components/ResolutionProgressSection";
import { Plus, Calendar, RotateCcw, AlertTriangle, Layers } from "lucide-react";
import { useAuth } from "../../auth/context/AuthContext";

/**
 * DashboardPage - Màn hình Tổng quan Điều hành Hệ thống Hội đồng trường STU
 */
export default function DashboardPage() {
  const toast = useToast();
  const { user, switchRole } = useAuth();

  const [isLoading, setIsLoading] = useState(false);
  const [isError, setIsError] = useState(false);

  const currentRole = user || {
    key: "SECRETARY",
    name: "Thư ký Hội đồng trường",
    user_name: "TS. Trần Văn Thư Ký",
    avatar_initials: "TK",
    department: "Văn phòng Hội đồng trường",
    duties_badge: "Thẩm tra & Điều hành phiên họp"
  };

  const handleRoleChange = (newRole) => {
    switchRole(newRole.key);
    toast.info(`Đã chuyển góc nhìn sang vai trò: ${newRole.name}`);
  };

  const handleToggleLoading = () => {
    setIsLoading(true);
    setIsError(false);
    setTimeout(() => {
      setIsLoading(false);
      toast.success("Dữ liệu bảng điều hành đã được làm mới!");
    }, 1200);
  };

  const handleToggleError = () => {
    setIsError(true);
    setIsLoading(false);
    toast.error("Giả lập lỗi kết nối máy chủ để kiểm tra ErrorState.");
  };

  return (
    <div>
      {/* 1. Header & Breadcrumb */}
      <PageHeader
        title="Tổng quan"
        description="Hệ thống thông tin hỗ trợ quản lý các kỳ họp, giám sát tiến độ thực hiện nghị quyết và điều hành công việc Hội đồng trường STU."
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Tổng quan", path: "/" }
        ]}
        actions={
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
            <button
              type="button"
              className="btn btn-secondary btn-sm"
              onClick={handleToggleLoading}
              title="Mô phỏng trạng thái tải dữ liệu thời gian thực"
            >
              <RotateCcw size={13} />
              <span>Làm mới</span>
            </button>
            <button
              type="button"
              className="btn btn-secondary btn-sm"
              onClick={handleToggleError}
              title="Mô phỏng kiểm tra ErrorState"
              style={{ color: "var(--color-danger)" }}
            >
              <AlertTriangle size={13} />
              <span>Thử lỗi</span>
            </button>
            <button
              type="button"
              className="btn btn-primary btn-sm"
              onClick={() => toast.info("Mở biểu mẫu lập kế hoạch phiên họp (Prototype)")}
            >
              <Plus size={13} />
              <span>Lập lịch họp</span>
            </button>
          </div>
        }
      />

      {/* 2. Lời chào và thông tin người dùng hiện tại */}
      <WelcomeBanner
        currentRole={currentRole}
        onRoleChange={handleRoleChange}
      />

      {/* Xử lý Trạng thái Lỗi (ErrorState) */}
      {isError ? (
        <div className="card" style={{ padding: "var(--space-8)" }}>
          <ErrorState
            title="Không thể kết nối đến hệ thống máy chủ"
            description="Đã xảy ra sự cố gián đoạn kết nối hoặc phiên làm việc của bạn cần xác thực lại. Vui lòng bấm thử lại để tái nạp dữ liệu."
            onRetry={() => {
              setIsError(false);
              handleToggleLoading();
            }}
          />
        </div>
      ) : isLoading ? (
        /* Xử lý Trạng thái Đang tải (LoadingSkeleton) */
        <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-6)" }}>
          <LoadingSkeleton type="card" count={6} />
          <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(400px, 1fr))", gap: "var(--space-6)" }}>
            <LoadingSkeleton type="table" count={3} />
            <LoadingSkeleton type="table" count={3} />
          </div>
        </div>
      ) : (
        /* 3. Nội dung Chính của Dashboard */
        <>
          {/* 6 Thẻ Thống kê Cốt lõi (Clickable StatCards) */}
          <DashboardStats />

          {/* Bố cục 2 Cột khoa học cho Nghiệp vụ */}
          <div
            style={{
              display: "grid",
              gridTemplateColumns: "repeat(auto-fit, minmax(min(100%, 420px), 1fr))",
              gap: "var(--space-6)",
              marginBottom: "var(--space-6)"
            }}
          >
            {/* Cột Trái 1: Lịch phiên họp sắp tới */}
            <div>
              <UpcomingMeetingsSection />
            </div>

            {/* Cột Phải 1: Công việc cần chú ý */}
            <div>
              <UrgentTasksSection currentRole={currentRole} />
            </div>
          </div>

          <div
            style={{
              display: "grid",
              gridTemplateColumns: "repeat(auto-fit, minmax(min(100%, 420px), 1fr))",
              gap: "var(--space-6)"
            }}
          >
            {/* Cột Trái 2: Hồ sơ trình chờ xử lý */}
            <div>
              <PendingDossiersSection />
            </div>

            {/* Cột Phải 2: Tình hình thực hiện nghị quyết */}
            <div>
              <ResolutionProgressSection />
            </div>
          </div>
        </>
      )}
    </div>
  );
}
