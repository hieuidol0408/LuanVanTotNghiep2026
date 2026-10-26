import React, { useState, useEffect, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import PageHeader from "../../../components/layout/PageHeader";
import StatCard from "../../../components/ui/StatCard";
import DataTable from "../../../components/ui/DataTable";
import StatusBadge from "../../../components/ui/StatusBadge";
import SearchInput from "../../../components/ui/SearchInput";
import FilterBar from "../../../components/ui/FilterBar";
import Pagination from "../../../components/ui/Pagination";
import ConfirmDialog from "../../../components/ui/ConfirmDialog";
import { useToast } from "../../../components/ui/Toast";
import { Plus, Eye, Edit, Trash2, Calendar, MapPin, Clock, Video } from "lucide-react";
import { meetingService } from "../services/meetingService";

export default function MeetingListPage() {
  const navigate = useNavigate();
  const toast = useToast();

  const [meetings, setMeetings] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState("ALL");
  const [typeFilter, setTypeFilter] = useState("ALL");
  const [formatFilter, setFormatFilter] = useState("ALL");
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(10);
  const [meta, setMeta] = useState({ total: 0, total_pages: 1 });

  // Dialog State
  const [confirmDialog, setConfirmDialog] = useState({ isOpen: false, item: null });

  const fetchMeetings = async () => {
    setIsLoading(true);
    const res = await meetingService.getMeetings({
      search: searchTerm,
      status: statusFilter,
      type: typeFilter,
      format: formatFilter,
      page: currentPage,
      perPage: itemsPerPage
    });
    if (res.success) {
      setMeetings(res.data);
      setMeta(res.meta);
    }
    setIsLoading(false);
  };

  useEffect(() => {
    fetchMeetings();
  }, [searchTerm, statusFilter, typeFilter, formatFilter, currentPage, itemsPerPage]);

  const handleResetFilters = () => {
    setSearchTerm("");
    setStatusFilter("ALL");
    setTypeFilter("ALL");
    setFormatFilter("ALL");
    setCurrentPage(1);
    toast.info("Đã đặt lại bộ lọc tìm kiếm.");
  };

  const handleDeleteMeeting = (meeting) => {
    setConfirmDialog({
      isOpen: true,
      item: meeting
    });
  };

  const handleConfirmDelete = async () => {
    if (confirmDialog.item) {
      const res = await meetingService.deleteMeeting(confirmDialog.item.id);
      if (res.success) {
        toast.success(res.message);
        setConfirmDialog({ isOpen: false, item: null });
        fetchMeetings();
      }
    }
  };

  // 4 Thống kê nhanh
  const stats = useMemo(() => {
    return [
      {
        id: "total",
        title: "Tổng số kỳ họp",
        value: "08",
        description: "Khóa IV (2020-2025)",
        statusColor: "info",
        iconName: "Calendar"
      },
      {
        id: "scheduled",
        title: "Đã phát giấy triệu tập",
        value: "01",
        description: "Khai mạc 15/11/2026",
        statusColor: "success",
        iconName: "Send"
      },
      {
        id: "preparing",
        title: "Đang chuẩn bị Agenda",
        value: "01",
        description: "Kỳ họp chuyên đề",
        statusColor: "warning",
        iconName: "Clock"
      },
      {
        id: "completed",
        title: "Đã bế mạc & Ban hành NQ",
        value: "06",
        description: "Đã chốt biên bản họp",
        statusColor: "info",
        iconName: "CheckCircle2"
      }
    ];
  }, []);

  const columns = [
    {
      key: "id",
      title: "Mã phiên",
      width: "120px",
      render: (val) => (
        <span style={{ fontWeight: "var(--font-weight-semibold)", color: "var(--color-primary)" }}>
          {val}
        </span>
      )
    },
    {
      key: "title",
      title: "Tên phiên họp & Quy mô",
      render: (val, row) => (
        <div>
          <div style={{ fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)", lineHeight: 1.4 }}>
            {val}
          </div>
          <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginTop: "2px", display: "flex", alignItems: "center", gap: "8px" }}>
            <span>Loại: {row.meeting_type_label}</span>
            <span>•</span>
            <span>Chủ tọa: {row.chairperson}</span>
          </div>
        </div>
      )
    },
    {
      key: "meeting_date",
      title: "Ngày & Thời gian",
      width: "160px",
      render: (val, row) => (
        <div style={{ fontSize: "var(--font-size-xs)" }}>
          <div style={{ fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)", display: "flex", alignItems: "center", gap: "4px" }}>
            <Calendar size={13} style={{ color: "var(--color-primary)" }} />
            {val}
          </div>
          <div style={{ color: "var(--color-text-muted)", marginTop: "2px", display: "flex", alignItems: "center", gap: "4px" }}>
            <Clock size={12} />
            {row.start_time} {row.end_time ? `- ${row.end_time}` : ""}
          </div>
        </div>
      )
    },
    {
      key: "location",
      title: "Hình thức & Địa điểm",
      width: "220px",
      render: (val, row) => (
        <div style={{ fontSize: "var(--font-size-xs)" }}>
          <div style={{ display: "flex", alignItems: "center", gap: "4px", marginBottom: "2px" }}>
            {row.meeting_format === "ONLINE" ? (
              <span style={{ display: "inline-flex", alignItems: "center", gap: "3px", color: "var(--color-info)", fontWeight: "var(--font-weight-semibold)" }}>
                <Video size={13} /> {row.meeting_format_label}
              </span>
            ) : (
              <span style={{ display: "inline-flex", alignItems: "center", gap: "3px", color: "var(--color-primary)", fontWeight: "var(--font-weight-medium)" }}>
                <MapPin size={13} /> {row.meeting_format_label}
              </span>
            )}
          </div>
          <div style={{ color: "var(--color-text-secondary)", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
            {val}
          </div>
        </div>
      )
    },
    {
      key: "status",
      title: "Trạng thái",
      width: "140px",
      render: (val, row) => {
        const badgeMap = {
          SCHEDULED: "success",
          IN_PROGRESS: "info",
          PREPARING: "warning",
          COMPLETED: "neutral",
          CANCELLED: "danger"
        };
        return <StatusBadge status={badgeMap[val] || "neutral"} label={row.status_label} size="sm" />;
      }
    },
    {
      key: "actions",
      title: "Thao tác",
      width: "120px",
      align: "right",
      render: (_, row) => (
        <div style={{ display: "flex", justifyContent: "flex-end", gap: "4px" }}>
          <button
            type="button"
            className="btn btn-secondary btn-sm"
            onClick={() => navigate(`/meetings/${row.id}`)}
            title="Xem chi tiết & Điều hành"
            style={{ padding: "4px 8px" }}
          >
            <Eye size={13} />
          </button>
          <button
            type="button"
            className="btn btn-secondary btn-sm"
            onClick={() => navigate(`/meetings/${row.id}/edit`)}
            title="Chỉnh sửa thông tin"
            style={{ padding: "4px 8px" }}
          >
            <Edit size={13} />
          </button>
          <button
            type="button"
            className="btn btn-sm"
            onClick={() => handleDeleteMeeting(row)}
            title="Xóa phiên họp"
            style={{ color: "var(--color-danger)", border: "1px solid var(--color-border)", padding: "4px 8px" }}
          >
            <Trash2 size={13} />
          </button>
        </div>
      )
    }
  ];

  return (
    <div>
      {/* 1. Page Header */}
      <PageHeader
        title="Quản lý Phiên họp Hội đồng trường"
        description="Lập lịch kỳ họp định kỳ và chuyên đề, quản lý chương trình nghị sự, kiểm soát thành phần triệu tập và phát hành tài liệu bảo mật."
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Phiên họp & Tài liệu", path: "/meetings" }
        ]}
        actions={
          <button
            type="button"
            className="btn btn-primary"
            onClick={() => navigate("/meetings/create")}
            style={{ gap: "6px" }}
          >
            <Plus size={16} />
            <span>Lập phiên họp mới</span>
          </button>
        }
      />

      {/* 2. Stat Cards Grid */}
      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))",
          gap: "var(--space-4)",
          marginBottom: "var(--space-6)"
        }}
      >
        {stats.map((s) => (
          <StatCard
            key={s.id}
            title={s.title}
            value={s.value}
            description={s.description}
            statusColor={s.statusColor}
            iconName={s.iconName}
          />
        ))}
      </div>

      {/* 3. Main Data Card */}
      <div className="card">
        {/* FilterBar */}
        <FilterBar
          searchComponent={
            <SearchInput
              value={searchTerm}
              onChange={setSearchTerm}
              onClear={() => setSearchTerm("")}
              placeholder="Tìm theo mã, tên phiên họp, địa điểm, chủ tọa..."
            />
          }
          hasActiveFilters={searchTerm !== "" || statusFilter !== "ALL" || typeFilter !== "ALL" || formatFilter !== "ALL"}
          onReset={handleResetFilters}
        >
          <select
            className="form-select"
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            style={{ width: "auto", fontSize: "var(--font-size-xs)", height: "38px" }}
            aria-label="Lọc theo trạng thái"
          >
            <option value="ALL">Tất cả trạng thái</option>
            <option value="PREPARING">Đang chuẩn bị</option>
            <option value="SCHEDULED">Đã triệu tập</option>
            <option value="IN_PROGRESS">Đang diễn ra</option>
            <option value="COMPLETED">Đã bế mạc</option>
            <option value="CANCELLED">Đã hủy</option>
          </select>

          <select
            className="form-select"
            value={typeFilter}
            onChange={(e) => setTypeFilter(e.target.value)}
            style={{ width: "auto", fontSize: "var(--font-size-xs)", height: "38px" }}
            aria-label="Lọc theo loại kỳ họp"
          >
            <option value="ALL">Tất cả loại họp</option>
            <option value="REGULAR">Họp định kỳ</option>
            <option value="EXTRAORDINARY">Họp chuyên đề</option>
          </select>

          <select
            className="form-select"
            value={formatFilter}
            onChange={(e) => setFormatFilter(e.target.value)}
            style={{ width: "auto", fontSize: "var(--font-size-xs)", height: "38px" }}
            aria-label="Lọc theo hình thức họp"
          >
            <option value="ALL">Tất cả hình thức</option>
            <option value="IN_PERSON">Trực tiếp</option>
            <option value="ONLINE">Trực tuyến</option>
            <option value="HYBRID">Kết hợp</option>
          </select>
        </FilterBar>

        {/* DataTable */}
        <DataTable
          columns={columns}
          data={meetings}
          keyField="id"
          isLoading={isLoading}
          emptyMessage="Không tìm thấy phiên họp nào phù hợp với điều kiện tìm kiếm."
        />

        {/* Pagination */}
        <Pagination
          currentPage={currentPage}
          totalPages={meta.total_pages}
          totalItems={meta.total}
          itemsPerPage={itemsPerPage}
          onPageChange={setCurrentPage}
          onItemsPerPageChange={(size) => {
            setItemsPerPage(size);
            setCurrentPage(1);
          }}
        />
      </div>

      {/* ConfirmDialog */}
      <ConfirmDialog
        isOpen={confirmDialog.isOpen}
        title="Xác nhận xóa phiên họp"
        description={`Bạn có chắc chắn muốn xóa phiên họp "${confirmDialog.item?.title}" (${confirmDialog.item?.id})? Toàn bộ danh mục chương trình, danh sách triệu tập và tài liệu liên quan sẽ bị gỡ bỏ.`}
        confirmLabel="Xóa vĩnh viễn"
        cancelLabel="Hủy bỏ"
        variant="danger"
        onConfirm={handleConfirmDelete}
        onCancel={() => setConfirmDialog({ isOpen: false, item: null })}
      />
    </div>
  );
}
