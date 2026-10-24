import React, { useState, useMemo } from "react";
import PageHeader from "../components/layout/PageHeader";
import StatCard from "../components/ui/StatCard";
import DataTable from "../components/ui/DataTable";
import StatusBadge from "../components/ui/StatusBadge";
import SearchInput from "../components/ui/SearchInput";
import FilterBar from "../components/ui/FilterBar";
import Pagination from "../components/ui/Pagination";
import ConfirmDialog from "../components/ui/ConfirmDialog";
import Alert from "../components/ui/Alert";
import { useToast } from "../components/ui/Toast";
import { Plus, Download, FileText, CheckCircle2, Eye, Trash2 } from "lucide-react";
import { PROTOTYPE_STAT_CARDS, PROTOTYPE_DOSSIER_ROWS } from "../mock/prototypeData";

export default function OverviewPage() {
  const toast = useToast();

  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState("ALL");
  const [departmentFilter, setDepartmentFilter] = useState("ALL");
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(10);
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [sortConfig, setSortConfig] = useState({ key: "submitted_date", direction: "desc" });

  // Dialog State
  const [confirmDialog, setConfirmDialog] = useState({ isOpen: false, item: null });

  // Lọc dữ liệu theo từ khóa và bộ lọc
  const filteredData = useMemo(() => {
    return PROTOTYPE_DOSSIER_ROWS.filter((item) => {
      const matchSearch =
        item.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.id.toLowerCase().includes(searchTerm.toLowerCase()) ||
        item.signer.toLowerCase().includes(searchTerm.toLowerCase());

      const matchStatus = statusFilter === "ALL" || item.status === statusFilter;
      const matchDept = departmentFilter === "ALL" || item.department === departmentFilter;

      return matchSearch && matchStatus && matchDept;
    });
  }, [searchTerm, statusFilter, departmentFilter]);

  // Phân trang
  const paginatedData = useMemo(() => {
    const startIndex = (currentPage - 1) * itemsPerPage;
    return filteredData.slice(startIndex, startIndex + itemsPerPage);
  }, [filteredData, currentPage, itemsPerPage]);

  const totalPages = Math.ceil(filteredData.length / itemsPerPage) || 1;

  const handleResetFilters = () => {
    setSearchTerm("");
    setStatusFilter("ALL");
    setDepartmentFilter("ALL");
    setCurrentPage(1);
    toast.info("Đã đặt lại các bộ lọc về mặc định.");
  };

  const handleSort = (key) => {
    setSortConfig((prev) => ({
      key,
      direction: prev.key === key && prev.direction === "asc" ? "desc" : "asc"
    }));
  };

  const handleDeleteRow = (row) => {
    setConfirmDialog({
      isOpen: true,
      item: row
    });
  };

  const handleConfirmDelete = () => {
    toast.success(`Đã xóa hồ sơ ${confirmDialog.item?.id} thành công.`);
    setConfirmDialog({ isOpen: false, item: null });
  };

  // Cấu hình các cột cho DataTable
  const columns = [
    {
      key: "id",
      title: "Mã hồ sơ",
      width: "120px",
      sortable: true,
      render: (val) => (
        <span style={{ fontWeight: "var(--font-weight-semibold)", color: "var(--color-primary)" }}>
          {val}
        </span>
      )
    },
    {
      key: "title",
      title: "Trích yếu nội dung đề xuất",
      render: (val, row) => (
        <div>
          <div style={{ fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)", marginBottom: "2px" }}>
            {val}
          </div>
          <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
            Đơn vị: {row.department} • Người ký: {row.signer} ({row.files_count} tệp đính kèm)
          </div>
        </div>
      )
    },
    {
      key: "submitted_date",
      title: "Ngày nộp",
      width: "120px",
      sortable: true
    },
    {
      key: "status",
      title: "Trạng thái",
      width: "160px",
      render: (val, row) => {
        const badgeMap = {
          approved: "success",
          in_review: "warning",
          revision_required: "warning",
          rejected: "danger",
          draft: "neutral"
        };
        return <StatusBadge status={badgeMap[val] || "neutral"} label={row.status_label} />;
      }
    },
    {
      key: "actions",
      title: "Thao tác",
      width: "100px",
      align: "right",
      render: (_, row) => (
        <div style={{ display: "flex", justifyContent: "flex-end", gap: "6px" }}>
          <button
            type="button"
            className="btn btn-secondary btn-sm"
            onClick={() => toast.info(`Xem chi tiết hồ sơ ${row.id}`)}
            title="Xem chi tiết"
          >
            <Eye size={14} />
          </button>
          <button
            type="button"
            className="btn btn-sm"
            onClick={() => handleDeleteRow(row)}
            title="Xóa hồ sơ"
            style={{ color: "var(--color-danger)", border: "1px solid var(--color-border)" }}
          >
            <Trash2 size={14} />
          </button>
        </div>
      )
    }
  ];

  return (
    <div>
      {/* Page Header */}
      <PageHeader
        title="Tổng quan Phiên họp & Thực hiện Nghị quyết"
        description="Bảng điều hành trực quan hỗ trợ Thư ký và Hội đồng trường theo dõi tiến độ thẩm tra hồ sơ, chuẩn bị kỳ họp và đôn đốc nhiệm vụ."
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Tổng quan", path: "/" }
        ]}
        actions={
          <>
            <button
              type="button"
              className="btn btn-secondary"
              onClick={() => toast.info("Đã xuất báo cáo tổng hợp dạng PDF (Prototype).")}
            >
              <Download size={15} />
              <span>Xuất báo cáo</span>
            </button>
            <button
              type="button"
              className="btn btn-primary"
              onClick={() => toast.success("Mở biểu mẫu tạo hồ sơ mới.")}
            >
              <Plus size={15} />
              <span>Tạo hồ sơ mới</span>
            </button>
          </>
        }
      />

      {/* Thông báo thông tin phiên họp sắp tới */}
      <Alert
        type="info"
        title="Thông báo chuẩn bị Phiên họp thứ 5 Hội đồng trường Khóa IV"
        message="Phiên họp dự kiến khai mạc vào ngày 15/11/2026. Hạn cuối tiếp nhận hồ sơ thẩm tra đưa vào chương trình nghị sự là 05/11/2026."
      />

      {/* Grid 4 Thẻ Thống kê StatCard */}
      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(240px, 1fr))",
          gap: "var(--space-4)",
          marginBottom: "var(--space-6)"
        }}
      >
        {PROTOTYPE_STAT_CARDS.map((stat) => (
          <StatCard
            key={stat.id}
            title={stat.title}
            value={stat.value}
            description={stat.description}
            trend={stat.trend}
            trendType={stat.trendType}
            statusColor={stat.statusColor}
            iconName={stat.iconName}
          />
        ))}
      </div>

      {/* Khối Bảng Dữ liệu Hồ sơ trình */}
      <div className="card">
        <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-4)" }}>
          <div>
            <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)" }}>
              Danh sách Hồ sơ trình thẩm tra
            </h2>
            <p style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
              Dữ liệu hiển thị phục vụ prototype kiểm thử các component giao diện
            </p>
          </div>
          {selectedRowKeys.length > 0 && (
            <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-primary)", fontWeight: "var(--font-weight-medium)" }}>
              Đã chọn: {selectedRowKeys.length} bản ghi
            </div>
          )}
        </div>

        {/* Thanh tìm kiếm và bộ lọc FilterBar */}
        <FilterBar
          searchComponent={
            <SearchInput
              value={searchTerm}
              onChange={setSearchTerm}
              onClear={() => setSearchTerm("")}
              placeholder="Tìm mã hồ sơ, tiêu đề, người nộp..."
            />
          }
          hasActiveFilters={searchTerm !== "" || statusFilter !== "ALL" || departmentFilter !== "ALL"}
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
            <option value="approved">Đạt thẩm tra</option>
            <option value="in_review">Đang thẩm tra</option>
            <option value="revision_required">Yêu cầu bổ sung</option>
            <option value="draft">Bản nháp</option>
            <option value="rejected">Từ chối</option>
          </select>

          <select
            className="form-select"
            value={departmentFilter}
            onChange={(e) => setDepartmentFilter(e.target.value)}
            style={{ width: "auto", fontSize: "var(--font-size-xs)", height: "38px" }}
            aria-label="Lọc theo đơn vị trình"
          >
            <option value="ALL">Tất cả đơn vị</option>
            <option value="Khoa Công nghệ Thông tin">Khoa CNTT</option>
            <option value="Phòng Kế hoạch - Tài chính">Phòng KHTC</option>
            <option value="Phòng Đào tạo">Phòng Đào tạo</option>
            <option value="Phòng Quản trị Thiết bị">Phòng QTTB</option>
            <option value="Phòng QLKH & Hợp tác Quốc tế">Phòng QLKH</option>
          </select>
        </FilterBar>

        {/* Bảng dữ liệu DataTable */}
        <DataTable
          columns={columns}
          data={paginatedData}
          selectable
          selectedRowKeys={selectedRowKeys}
          onSelectRowKeys={setSelectedRowKeys}
          sortConfig={sortConfig}
          onSort={handleSort}
          emptyMessage="Không tìm thấy hồ sơ trình nào phù hợp với điều kiện lọc."
        />

        {/* Phân trang Pagination */}
        <Pagination
          currentPage={currentPage}
          totalPages={totalPages}
          totalItems={filteredData.length}
          itemsPerPage={itemsPerPage}
          onPageChange={setCurrentPage}
          onItemsPerPageChange={(size) => {
            setItemsPerPage(size);
            setCurrentPage(1);
          }}
        />
      </div>

      {/* Confirm Dialog Xóa Hồ sơ */}
      <ConfirmDialog
        isOpen={confirmDialog.isOpen}
        title="Xác nhận xóa hồ sơ trình"
        description={`Bạn có chắc chắn muốn xóa hồ sơ "${confirmDialog.item?.title}" (${confirmDialog.item?.id})? Thao tác này sẽ xóa vĩnh viễn hồ sơ và các tệp đính kèm.`}
        confirmLabel="Xóa vĩnh viễn"
        cancelLabel="Hủy bỏ"
        variant="danger"
        onConfirm={handleConfirmDelete}
        onCancel={() => setConfirmDialog({ isOpen: false, item: null })}
      />
    </div>
  );
}
