import React, { useState, useEffect } from "react";
import PageHeader from "../../../components/layout/PageHeader";
import StatCard from "../../../components/ui/StatCard";
import DataTable from "../../../components/ui/DataTable";
import StatusBadge from "../../../components/ui/StatusBadge";
import SearchInput from "../../../components/ui/SearchInput";
import FilterBar from "../../../components/ui/FilterBar";
import Pagination from "../../../components/ui/Pagination";
import ConfirmDialog from "../../../components/ui/ConfirmDialog";
import { useToast } from "../../../components/ui/Toast";
import { useAuth } from "../../auth/context/AuthContext";
import { dossierService } from "../services/dossierService";
import {
  FileText,
  Plus,
  Eye,
  Edit,
  Trash2,
  Send,
  CheckCircle2,
  XCircle,
  Clock,
  Layers,
  HelpCircle,
  FileCheck2
} from "lucide-react";

export default function DossierListPage() {
  const toast = useToast();
  const { user } = useAuth();
  const userRole = user?.role || "MEMBER";

  const [dossiers, setDossiers] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState("ALL");
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(10);
  const [meta, setMeta] = useState({ total: 0, total_pages: 1 });

  // Dialogs & Modals state
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [isReviewModalOpen, setIsReviewModalOpen] = useState(false);
  const [selectedDossier, setSelectedDossier] = useState(null);
  const [confirmDialog, setConfirmDialog] = useState({ isOpen: false, type: "", item: null });

  // Form states
  const [formData, setFormData] = useState({
    title: "",
    summary: "",
    field: "Đào tạo & Công nghệ",
    notes: ""
  });

  const [reviewData, setReviewData] = useState({
    decision: "APPROVED",
    comments: ""
  });

  const fetchDossiers = async () => {
    setIsLoading(true);
    const res = await dossierService.getDossiers({
      search: searchTerm,
      status: statusFilter,
      page: currentPage,
      perPage: itemsPerPage
    });
    if (res.success) {
      setDossiers(res.data);
      setMeta(res.meta);
    }
    setIsLoading(false);
  };

  useEffect(() => {
    fetchDossiers();
  }, [searchTerm, statusFilter, currentPage, itemsPerPage]);

  // Thống kê nhanh
  const stats = {
    total: meta.total || dossiers.length,
    submitted: dossiers.filter((d) => d.status === "SUBMITTED").length,
    approved: dossiers.filter((d) => d.status === "APPROVED_FOR_MEETING").length,
    revision: dossiers.filter((d) => d.status === "REVISION_REQUIRED").length
  };

  // Tạo hồ sơ mới
  const handleCreateDossier = async (e) => {
    e.preventDefault();
    if (!formData.title || !formData.summary) {
      toast.error("Vui lòng nhập đầy đủ tiêu đề và nội dung tóm tắt.");
      return;
    }

    const res = await dossierService.createDossier(formData);
    if (res.success) {
      toast.success("Tạo hồ sơ dự thảo thành công!");
      setIsCreateModalOpen(false);
      setFormData({ title: "", summary: "", field: "Đào tạo & Công nghệ", notes: "" });
      fetchDossiers();
    } else {
      toast.error(res.message || "Lỗi khi tạo hồ sơ");
    }
  };

  // Nộp hồ sơ (SUBMIT)
  const handleSubmitDossier = async (dossier) => {
    const res = await dossierService.submitDossier(dossier.id);
    if (res.success) {
      toast.success("Đã nộp hồ sơ chính thức lên Thư ký HĐT!");
      fetchDossiers();
    } else {
      toast.error(res.message || "Lỗi khi nộp hồ sơ");
    }
  };

  // Mở modal thẩm tra (REVIEW)
  const handleOpenReview = (dossier) => {
    setSelectedDossier(dossier);
    setReviewData({
      decision: "APPROVED",
      comments: ""
    });
    setIsReviewModalOpen(true);
  };

  // Thực hiện thẩm tra
  const handleExecuteReview = async (e) => {
    e.preventDefault();
    if (!selectedDossier) return;

    const res = await dossierService.reviewDossier(selectedDossier.id, reviewData);
    if (res.success) {
      toast.success(res.message);
      setIsReviewModalOpen(false);
      setSelectedDossier(null);
      fetchDossiers();
    } else {
      toast.error(res.message || "Lỗi khi thẩm tra hồ sơ");
    }
  };

  // Xóa hồ sơ
  const handleConfirmDelete = async () => {
    if (confirmDialog.item) {
      const res = await dossierService.deleteDossier(confirmDialog.item.id);
      if (res.success) {
        toast.success("Đã xóa hồ sơ dự thảo thành công.");
        fetchDossiers();
      } else {
        toast.error(res.message || "Lỗi khi xóa hồ sơ");
      }
      setConfirmDialog({ isOpen: false, type: "", item: null });
    }
  };

  const columns = [
    {
      key: "code",
      title: "Mã hồ sơ",
      width: "120px",
      render: (val) => (
        <span style={{ fontWeight: "var(--font-weight-semibold)", color: "var(--color-primary)" }}>
          {val}
        </span>
      )
    },
    {
      key: "title",
      title: "Tiêu đề & Trích yếu đề xuất",
      render: (val, row) => (
        <div>
          <div style={{ fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)", fontSize: "14px" }}>
            {val}
          </div>
          <div style={{ fontSize: "12px", color: "var(--color-text-muted)", marginTop: "3px" }}>
            {row.summary}
          </div>
          <div style={{ fontSize: "11px", color: "var(--color-primary)", marginTop: "4px" }}>
            Đơn vị: {row.department} • Người nộp: {row.submitter}
          </div>
        </div>
      )
    },
    {
      key: "field",
      title: "Lĩnh vực",
      width: "150px",
      render: (val) => <span className="badge badge-secondary">{val}</span>
    },
    {
      key: "submitted_date",
      title: "Ngày trình",
      width: "110px",
      align: "center"
    },
    {
      key: "status",
      title: "Trạng thái",
      width: "160px",
      render: (val, row) => <StatusBadge status={val} label={row.status_label} />
    },
    {
      key: "actions",
      title: "Thao tác",
      width: "150px",
      align: "right",
      render: (_, row) => (
        <div style={{ display: "flex", alignItems: "center", justifyContent: "flex-end", gap: "6px" }}>
          {/* Nộp hồ sơ nếu là DRAFT hoặc REVISION_REQUIRED */}
          {["DRAFT", "REVISION_REQUIRED"].includes(row.status) && (
            <button
              type="button"
              className="btn btn-primary btn-sm"
              onClick={() => handleSubmitDossier(row)}
              title="Nộp hồ sơ lên Thư ký HĐT"
              style={{ padding: "4px 8px" }}
            >
              <Send size={13} />
              <span>Nộp</span>
            </button>
          )}

          {/* Thẩm tra nếu là Thư ký/Chủ tịch và hồ sơ đang ở SUBMITTED */}
          {row.status === "SUBMITTED" && ["SECRETARY", "CHAIR", "ADMIN"].includes(userRole) && (
            <button
              type="button"
              className="btn btn-warning btn-sm"
              onClick={() => handleOpenReview(row)}
              title="Thẩm tra hồ sơ"
              style={{ padding: "4px 8px" }}
            >
              <FileCheck2 size={13} />
              <span>Thẩm tra</span>
            </button>
          )}

          {/* Xóa nếu ở DRAFT */}
          {row.status === "DRAFT" && (
            <button
              type="button"
              className="btn btn-danger btn-sm"
              onClick={() => setConfirmDialog({ isOpen: true, type: "delete", item: row })}
              title="Xóa hồ sơ"
              style={{ padding: "4px 8px" }}
            >
              <Trash2 size={13} />
            </button>
          )}
        </div>
      )
    }
  ];

  return (
    <div>
      <PageHeader
        title="Quản lý Hồ sơ tờ trình"
        description="Quy trình nộp, tiếp nhận và thẩm tra hồ sơ đề xuất trình Hội đồng trường STU"
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Hồ sơ trình", path: "/dossiers" }
        ]}
        actions={
          ["UNIT_REP", "SECRETARY", "ADMIN"].includes(userRole) ? (
            <button
              type="button"
              className="btn btn-primary"
              onClick={() => setIsCreateModalOpen(true)}
              style={{ gap: "6px" }}
            >
              <Plus size={16} />
              <span>Tạo hồ sơ mới</span>
            </button>
          ) : null
        }
      />

      {/* Thẻ thống kê */}
      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(220px, 1fr))",
          gap: "var(--space-4)",
          marginBottom: "var(--space-5)"
        }}
      >
        <StatCard
          icon={Layers}
          title="Tổng số hồ sơ"
          value={stats.total}
          subtitle="Hồ sơ đã ghi nhận"
          variant="primary"
        />
        <StatCard
          icon={Clock}
          title="Chờ thẩm tra"
          value={stats.submitted}
          subtitle="Đang chờ Thư ký duyệt"
          variant="warning"
        />
        <StatCard
          icon={CheckCircle2}
          title="Đạt chuẩn họp"
          value={stats.approved}
          subtitle="Sẵn sàng xếp lịch họp"
          variant="success"
        />
        <StatCard
          icon={HelpCircle}
          title="Cần chỉnh sửa"
          value={stats.revision}
          subtitle="Yêu cầu đơn vị bổ sung"
          variant="danger"
        />
      </div>

      {/* Bảng dữ liệu và Bộ lọc */}
      <div className="card" style={{ padding: "var(--space-5)" }}>
        <FilterBar onReset={() => { setSearchTerm(""); setStatusFilter("ALL"); }}>
          <div style={{ flex: 1, minWidth: "240px" }}>
            <SearchInput
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="Tìm kiếm theo mã, tiêu đề hoặc lĩnh vực..."
            />
          </div>

          <div style={{ minWidth: "200px" }}>
            <select
              className="form-control"
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
            >
              <option value="ALL">Tất cả trạng thái</option>
              <option value="DRAFT">Dự thảo</option>
              <option value="SUBMITTED">Đã nộp (Chờ thẩm tra)</option>
              <option value="APPROVED_FOR_MEETING">Đã duyệt (Sẵn sàng xếp lịch)</option>
              <option value="REVISION_REQUIRED">Yêu cầu bổ sung</option>
              <option value="REJECTED">Từ chối tiếp nhận</option>
              <option value="SCHEDULED">Đã xếp lịch họp</option>
            </select>
          </div>
        </FilterBar>

        <DataTable
          columns={columns}
          data={dossiers}
          isLoading={isLoading}
          keyField="id"
          emptyMessage="Không tìm thấy hồ sơ tờ trình nào."
        />

        {meta.total_pages > 1 && (
          <div style={{ marginTop: "var(--space-4)" }}>
            <Pagination
              currentPage={currentPage}
              totalPages={meta.total_pages}
              onPageChange={setCurrentPage}
            />
          </div>
        )}
      </div>

      {/* Modal Tạo hồ sơ mới */}
      {isCreateModalOpen && (
        <div className="modal-backdrop" style={{ position: "fixed", inset: 0, backgroundColor: "rgba(0,0,0,0.5)", zIndex: 100, display: "flex", alignItems: "center", justifyContent: "center" }}>
          <div className="card" style={{ width: "100%", maxWidth: "600px", backgroundColor: "#fff", padding: "var(--space-6)" }}>
            <h3 style={{ fontSize: "18px", fontWeight: "var(--font-weight-bold)", marginBottom: "var(--space-4)" }}>
              Tạo mới hồ sơ tờ trình
            </h3>
            <form onSubmit={handleCreateDossier}>
              <div style={{ marginBottom: "var(--space-4)" }}>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontWeight: "bold" }}>Tiêu đề hồ sơ *</label>
                <input
                  type="text"
                  className="form-control"
                  style={{ width: "100%", padding: "8px", border: "1px solid #ccc", borderRadius: "4px" }}
                  placeholder="Ví dụ: Tờ trình về việc điều chỉnh quy chế chi tiêu..."
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  required
                />
              </div>

              <div style={{ marginBottom: "var(--space-4)" }}>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontWeight: "bold" }}>Lĩnh vực</label>
                <select
                  className="form-control"
                  style={{ width: "100%", padding: "8px", border: "1px solid #ccc", borderRadius: "4px" }}
                  value={formData.field}
                  onChange={(e) => setFormData({ ...formData, field: e.target.value })}
                >
                  <option value="Đào tạo & Công nghệ">Đào tạo & Công nghệ</option>
                  <option value="Tài chính & Cơ sở vật chất">Tài chính & Cơ sở vật chất</option>
                  <option value="Nhân sự & Tổ chức">Nhân sự & Tổ chức</option>
                  <option value="Hợp tác quốc tế & NCKH">Hợp tác quốc tế & NCKH</option>
                  <option value="Quy chế & Chiến lược">Quy chế & Chiến lược</option>
                </select>
              </div>

              <div style={{ marginBottom: "var(--space-4)" }}>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontWeight: "bold" }}>Nội dung tóm tắt & Căn cứ đề xuất *</label>
                <textarea
                  className="form-control"
                  rows={4}
                  style={{ width: "100%", padding: "8px", border: "1px solid #ccc", borderRadius: "4px" }}
                  placeholder="Trình bày tóm tắt mục tiêu, căn cứ pháp lý và nội dung kính trình Hội đồng trường..."
                  value={formData.summary}
                  onChange={(e) => setFormData({ ...formData, summary: e.target.value })}
                  required
                />
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-3)", marginTop: "var(--space-6)" }}>
                <button type="button" className="btn btn-secondary" onClick={() => setIsCreateModalOpen(false)}>
                  Hủy bỏ
                </button>
                <button type="submit" className="btn btn-primary">
                  Lưu dự thảo
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal Thẩm tra hồ sơ */}
      {isReviewModalOpen && selectedDossier && (
        <div className="modal-backdrop" style={{ position: "fixed", inset: 0, backgroundColor: "rgba(0,0,0,0.5)", zIndex: 100, display: "flex", alignItems: "center", justifyContent: "center" }}>
          <div className="card" style={{ width: "100%", maxWidth: "600px", backgroundColor: "#fff", padding: "var(--space-6)" }}>
            <h3 style={{ fontSize: "18px", fontWeight: "var(--font-weight-bold)", marginBottom: "var(--space-2)" }}>
              Thẩm tra hồ sơ tờ trình
            </h3>
            <p style={{ fontSize: "13px", color: "var(--color-text-muted)", marginBottom: "var(--space-4)" }}>
              Hồ sơ: <strong>{selectedDossier.code} - {selectedDossier.title}</strong>
            </p>

            <form onSubmit={handleExecuteReview}>
              <div style={{ marginBottom: "var(--space-4)" }}>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontWeight: "bold" }}>Quyết định thẩm tra *</label>
                <select
                  className="form-control"
                  style={{ width: "100%", padding: "8px", border: "1px solid #ccc", borderRadius: "4px" }}
                  value={reviewData.decision}
                  onChange={(e) => setReviewData({ ...reviewData, decision: e.target.value })}
                >
                  <option value="APPROVED">Duyệt đạt (Sẵn sàng xếp lịch họp HĐT)</option>
                  <option value="REVISE">Yêu cầu đơn vị bổ sung / chỉnh sửa hồ sơ</option>
                  <option value="REJECT">Từ chối tiếp nhận (Không thuộc thẩm quyền HĐT)</option>
                </select>
              </div>

              <div style={{ marginBottom: "var(--space-4)" }}>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontWeight: "bold" }}>Ý kiến thẩm tra / Yêu cầu cụ thể</label>
                <textarea
                  className="form-control"
                  rows={4}
                  style={{ width: "100%", padding: "8px", border: "1px solid #ccc", borderRadius: "4px" }}
                  placeholder="Ghi nhận căn cứ pháp lý, nhận xét thể thức hoặc nội dung cần đơn vị bổ sung..."
                  value={reviewData.comments}
                  onChange={(e) => setReviewData({ ...reviewData, comments: e.target.value })}
                />
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-3)", marginTop: "var(--space-6)" }}>
                <button type="button" className="btn btn-secondary" onClick={() => setIsReviewModalOpen(false)}>
                  Đóng
                </button>
                <button type="submit" className="btn btn-primary">
                  Lưu kết quả thẩm tra
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Confirm Delete Dialog */}
      <ConfirmDialog
        isOpen={confirmDialog.isOpen}
        title="Xóa hồ sơ dự thảo"
        message={`Bạn có chắc chắn muốn xóa hồ sơ "${confirmDialog.item?.title}"? Thao tác này không thể hoàn tác.`}
        confirmText="Xác nhận xóa"
        confirmVariant="danger"
        onConfirm={handleConfirmDelete}
        onCancel={() => setConfirmDialog({ isOpen: false, type: "", item: null })}
      />
    </div>
  );
}
