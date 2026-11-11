import React, { useState, useEffect, useCallback } from "react";
import PageHeader from "../../../thanh-phan/bo-cuc/TieuDeTrang";
import LoadingSkeleton from "../../../thanh-phan/giao-dien/KhungTaiDuLieu";
import ErrorState from "../../../thanh-phan/giao-dien/TrangThaiLoi";
import { useToast } from "../../../thanh-phan/giao-dien/ThongBaoNhanh";
import WelcomeBanner from "../thanh-phan/BieuNguChaoMung";
import DashboardStats from "../thanh-phan/ThongKeBangDieuHanh";
import UrgentTasksSection from "../thanh-phan/KhuVucNhiemVuKhan";
import UpcomingMeetingsSection from "../thanh-phan/KhuVucPhienHopSapToi";
import PendingDossiersSection from "../thanh-phan/KhuVucHoSoChoDuyet";
import ResolutionProgressSection from "../thanh-phan/KhuVucTienDoNghiQuyet";
import { Plus, RotateCcw } from "lucide-react";
import { useAuth } from "../../xac-thuc/ngu-canh/NguCanhXacThuc";
import { useNavigate } from "react-router-dom";
import dashboardService from "../dich-vu/dichVuBangDieuHanh";

/**
 * DashboardPage - Màn hình Tổng quan Điều hành Hệ thống Hội đồng trường STU
 * Kết nối thời gian thực với cơ sở dữ liệu MySQL trên WampServer
 */
export default function DashboardPage() {
  const toast = useToast();
  const navigate = useNavigate();
  const { user } = useAuth();

  const [isLoading, setIsLoading] = useState(true);
  const [isError, setIsError] = useState(false);
  const [summaryData, setSummaryData] = useState(null);

  const currentRole = user || {
    key: "SECRETARY",
    name: "Thư ký Hội đồng trường",
    user_name: "TS. Trần Văn Thư Ký",
    avatar_initials: "TK",
    department: "Văn phòng Hội đồng trường",
    duties_badge: "Thẩm tra & Điều hành phiên họp"
  };

  // Tải dữ liệu bảng điều hành thực tế từ API Laravel
  const fetchDashboardData = useCallback(async () => {
    setIsLoading(true);
    setIsError(false);
    try {
      const res = await dashboardService.getSummary();
      if (res.success && res.data) {
        setSummaryData(res.data);
      } else {
        console.warn("Không lấy được dữ liệu dashboard:", res.message);
      }
    } catch (err) {
      console.warn("Lỗi kết nối dashboard:", err);
      setIsError(true);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchDashboardData();
  }, [fetchDashboardData]);

  const handleRefresh = async () => {
    await fetchDashboardData();
    toast.success("Dữ liệu bảng điều hành đã được đồng bộ từ MySQL!");
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
              onClick={handleRefresh}
              title="Đồng bộ dữ liệu thời gian thực từ cơ sở dữ liệu"
            >
              <RotateCcw size={13} className={isLoading ? "animate-spin" : ""} />
              <span>Làm mới</span>
            </button>
            <button
              type="button"
              className="btn btn-primary btn-sm"
              onClick={() => navigate("/meetings/create")}
            >
              <Plus size={13} />
              <span>Lập lịch họp</span>
            </button>
          </div>
        }
      />

      {/* 2. Lời chào và thông tin người dùng hiện tại */}
      <WelcomeBanner currentRole={currentRole} />

      {/* Xử lý Trạng thái Lỗi (ErrorState) */}
      {isError ? (
        <div className="card" style={{ padding: "var(--space-8)" }}>
          <ErrorState
            title="Không thể kết nối đến hệ thống máy chủ"
            description="Đã xảy ra sự cố gián đoạn kết nối hoặc phiên làm việc của bạn cần xác thực lại. Vui lòng bấm thử lại để tái nạp dữ liệu."
            onRetry={() => {
              setIsError(false);
              fetchDashboardData();
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
        /* 3. Nội dung Chính của Dashboard thời gian thực */
        <>
          {/* 6 Thẻ Thống kê Cốt lõi (Clickable StatCards) */}
          <DashboardStats stats={summaryData?.stats} />

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
              <UpcomingMeetingsSection meetings={summaryData?.upcoming_meetings} />
            </div>

            {/* Cột Phải 1: Công việc cần chú ý */}
            <div>
              <UrgentTasksSection
                tasks={summaryData?.urgent_tasks}
                currentRole={currentRole}
              />
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
              <PendingDossiersSection dossiers={summaryData?.pending_dossiers} />
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
