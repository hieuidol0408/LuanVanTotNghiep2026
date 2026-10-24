import React, { useState, useEffect, useCallback } from "react";
import {
  FileText,
  Search,
  Filter,
  Plus,
  RefreshCw,
  Download,
  Lock,
  Unlock,
  Trash2,
  Calendar,
  Layers,
  ShieldCheck,
  ShieldAlert,
  FileCheck
} from "lucide-react";
import PageHeader from "../../../components/layout/PageHeader";
import StatusBadge from "../../../components/ui/StatusBadge";
import EmptyState from "../../../components/ui/EmptyState";
import ConfirmDialog from "../../../components/ui/ConfirmDialog";
import { useAuth } from "../../auth/context/AuthContext";
import { useToast } from "../../../components/ui/Toast";
import documentService from "../services/documentService";
import meetingService from "../../meetings/services/meetingService";

export default function DocumentListPage() {
  const { user } = useAuth();
  const toast = useToast();

  const [documents, setDocuments] = useState([]);
  const [meetings, setMeetings] = useState([]);
  const [loading, setLoading] = useState(false);
  const [meta, setMeta] = useState({ total: 0, current_page: 1, total_pages: 1 });

  // Filters
  const [search, setSearch] = useState("");
  const [typeFilter, setTypeFilter] = useState("ALL");
  const [meetingFilter, setMeetingFilter] = useState("ALL");
  const [page, setPage] = useState(1);

  // Upload Modal
  const [uploadModalOpen, setUploadModalOpen] = useState(false);
  const [formData, setFormData] = useState({
    file_name: "",
    access_level: "PUBLIC_AGENDA",
    meeting_id: "",
    file_size: 1048576,
    file_type: "application/pdf"
  });

  // Confirm Dialog
  const [confirmDialog, setConfirmDialog] = useState({
    isOpen: false,
    title: "",
    message: "",
    confirmLabel: "Xác nhận",
    confirmVariant: "primary",
    onConfirm: () => {}
  });

  const isCouncilMember = user && ["CHAIR", "VICE_CHAIR", "SECRETARY", "MEMBER", "ADMIN"].includes(user.role);
  const isSecretaryOrChair = user && ["CHAIR", "SECRETARY", "ADMIN"].includes(user.role);

  // 1. Tải danh sách phiên họp cho bộ lọc
  const fetchMeetings = useCallback(async () => {
    try {
      const res = await meetingService.getMeetings({ perPage: 50 });
      if (res.success && res.data) {
        setMeetings(res.data);
      }
    } catch (err) {
      console.warn("Lỗi tải phiên họp:", err);
    }
  }, []);

  useEffect(() => {
    fetchMeetings();
  }, [fetchMeetings]);

  // 2. Tải danh sách tài liệu
  const fetchDocuments = useCallback(async () => {
    setLoading(true);
    try {
      const res = await documentService.getDocuments({
        search,
        type: typeFilter,
        meeting_id: meetingFilter,
        page,
        perPage: 12
      });

      if (res.success) {
        setDocuments(res.data);
        if (res.meta) setMeta(res.meta);
      } else {
        toast.error(res.message || "Không thể tải danh sách tài liệu");
      }
    } catch (err) {
      console.warn("Lỗi tải tài liệu:", err);
      toast.error("Lỗi kết nối khi tải danh sách tài liệu");
    } finally {
      setLoading(false);
    }
  }, [search, typeFilter, meetingFilter, page, toast]);

  useEffect(() => {
    fetchDocuments();
  }, [fetchDocuments]);

  // Handler: Tải xuống an toàn
  const handleDownload = async (doc) => {
    if (doc.access_level === "INTERNAL" && !isCouncilMember) {
      toast.error("Bạn không có quyền truy cập tài liệu nội bộ bảo mật của Hội đồng trường.");
      return;
    }

    toast.info(`Đang chuẩn bị tải tệp "${doc.file_name}"...`);
    const res = await documentService.downloadDocument(doc.id, doc.file_name);
    if (res.success) {
      toast.success(`Đã tải xuống thành công: ${doc.file_name}`);
    } else {
      toast.error(res.message || "Lỗi khi tải tệp tài liệu");
    }
  };

  // Handler: Xóa tài liệu
  const handleDelete = (id, fileName) => {
    setConfirmDialog({
      isOpen: true,
      title: "Xóa tài liệu khỏi kho",
      message: `Bạn có chắc chắn muốn xóa tài liệu "${fileName}"?`,
      confirmLabel: "Xóa tài liệu",
      confirmVariant: "danger",
      onConfirm: async () => {
        try {
          const res = await documentService.deleteDocument(id);
          if (res.success) {
            toast.success(res.message);
            fetchDocuments();
          } else {
            toast.error(res.message || "Không thể xóa tài liệu");
          }
        } catch (err) {
          console.warn("Lỗi khi xóa tài liệu:", err);
          toast.error("Lỗi khi xóa tài liệu");
        }
      }
    });
  };

  // Handler: Tải lên tài liệu mới
  const handleUpload = async (e) => {
    e.preventDefault();
    if (!formData.file_name.trim()) {
      toast.error("Vui lòng nhập tên tệp tài liệu");
      return;
    }

    try {
      const res = await documentService.uploadDocument(formData);
      if (res.success) {
        toast.success(res.message);
        setUploadModalOpen(false);
        setFormData({
          file_name: "",
          access_level: "PUBLIC_AGENDA",
          meeting_id: "",
          file_size: 1048576,
          file_type: "application/pdf"
        });
        fetchDocuments();
      } else {
        toast.error(res.message || "Không thể tải lên tài liệu");
      }
    } catch (err) {
      console.warn("Lỗi tải lên tài liệu:", err);
      toast.error("Lỗi khi tải lên tài liệu");
    }
  };

  return (
    <div>
      <PageHeader
        title="Kho Văn bản & Tài liệu an toàn"
        description="Kho lưu trữ tập trung các văn bản quy chế, chương trình họp, hồ sơ trình và tài liệu bảo mật của Hội đồng trường STU."
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Tài liệu an toàn", path: "" }
        ]}
        actions={
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
            <button
              type="button"
              className="btn btn-secondary"
              onClick={fetchDocuments}
              title="Tải lại dữ liệu"
            >
              <RefreshCw size={16} />
              <span>Làm mới</span>
            </button>

            {isSecretaryOrChair && (
              <button
                type="button"
                className="btn btn-primary"
                onClick={() => setUploadModalOpen(true)}
              >
                <Plus size={16} />
                <span>Tải lên tài liệu</span>
              </button>
            )}
          </div>
        }
      />

      {/* 1. Bộ lọc & Tìm kiếm */}
      <div
        className="card"
        style={{
          padding: "var(--space-4)",
          marginBottom: "var(--space-4)",
          display: "flex",
          flexWrap: "wrap",
          alignItems: "center",
          justifyContent: "space-between",
          gap: "var(--space-3)"
        }}
      >
        <div style={{ display: "flex", flex: 1, minWidth: "260px", position: "relative" }}>
          <Search
            size={18}
            style={{
              position: "absolute",
              left: "12px",
              top: "50%",
              transform: "translateY(-50%)",
              color: "var(--color-text-muted)"
            }}
          />
          <input
            type="text"
            className="form-control"
            placeholder="Tìm theo tên tệp tài liệu..."
            style={{ paddingLeft: "38px" }}
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setPage(1);
            }}
          />
        </div>

        <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)", flexWrap: "wrap" }}>
          {/* Lọc cấp độ bảo mật */}
          <select
            className="form-control"
            style={{ width: "auto" }}
            value={typeFilter}
            onChange={(e) => {
              setTypeFilter(e.target.value);
              setPage(1);
            }}
          >
            <option value="ALL">Tất cả cấp độ truy cập</option>
            <option value="INTERNAL">Nội bộ HĐT (Bảo mật)</option>
            <option value="PUBLIC_AGENDA">Công khai cho đại biểu</option>
            <option value="REGULATION">Văn bản quy chế</option>
          </select>

          {/* Lọc theo phiên họp */}
          <select
            className="form-control"
            style={{ width: "auto", maxWidth: "240px" }}
            value={meetingFilter}
            onChange={(e) => {
              setMeetingFilter(e.target.value);
              setPage(1);
            }}
          >
            <option value="ALL">Tất cả phiên họp</option>
            {meetings.map((m) => (
              <option key={m.id} value={m.id}>
                {m.title}
              </option>
            ))}
          </select>
        </div>
      </div>

      {/* 2. Danh sách tài liệu dạng lưới (Grid) */}
      {loading ? (
        <div className="card" style={{ padding: "var(--space-8)", textAlign: "center", color: "var(--color-text-muted)" }}>
          Đang tải kho tài liệu...
        </div>
      ) : documents.length === 0 ? (
        <div className="card" style={{ padding: "var(--space-8)" }}>
          <EmptyState
            icon={FileText}
            title="Không tìm thấy tài liệu nào"
            description="Chưa có tài liệu nào phù hợp với bộ lọc tìm kiếm hiện tại."
          />
        </div>
      ) : (
        <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(320px, 1fr))", gap: "var(--space-3)" }}>
          {documents.map((doc) => {
            const isInternal = doc.access_level === "INTERNAL";
            const isRegulation = doc.access_level === "REGULATION";
            const canAccess = !isInternal || isCouncilMember;

            return (
              <div
                key={doc.id}
                className="card"
                style={{
                  padding: "var(--space-4)",
                  display: "flex",
                  flexDirection: "column",
                  justifyContent: "space-between",
                  gap: "var(--space-3)",
                  borderTop: isInternal ? "3px solid var(--color-warning)" : isRegulation ? "3px solid var(--color-primary)" : "3px solid var(--color-success)"
                }}
              >
                <div>
                  <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-2)" }}>
                    <span
                      style={{
                        fontSize: "11px",
                        fontWeight: "var(--font-weight-bold)",
                        display: "inline-flex",
                        alignItems: "center",
                        gap: "4px",
                        color: isInternal ? "var(--color-warning)" : isRegulation ? "var(--color-primary)" : "var(--color-success)"
                      }}
                    >
                      {isInternal ? <Lock size={12} /> : <Unlock size={12} />}
                      {doc.access_level_label}
                    </span>

                    <span style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>
                      {doc.file_size_formatted}
                    </span>
                  </div>

                  <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)", marginBottom: "4px", wordBreak: "break-word" }}>
                    {doc.file_name}
                  </div>

                  <div style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>
                    Người tải: {doc.uploaded_by} • {doc.uploaded_at || "Vừa xong"}
                  </div>
                </div>

                <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", borderTop: "1px solid var(--color-border)", paddingTop: "var(--space-3)" }}>
                  <div>
                    {!canAccess ? (
                      <span style={{ fontSize: "11px", color: "var(--color-warning)", display: "flex", alignItems: "center", gap: "4px" }}>
                        <Lock size={12} /> Cần quyền HĐT
                      </span>
                    ) : (
                      <span style={{ fontSize: "11px", color: "var(--color-success)", display: "flex", alignItems: "center", gap: "4px" }}>
                        <ShieldCheck size={12} /> Đủ quyền tải
                      </span>
                    )}
                  </div>

                  <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
                    <button
                      type="button"
                      className="btn btn-secondary btn-sm"
                      onClick={() => handleDownload(doc)}
                      title={canAccess ? "Tải xuống tài liệu an toàn" : "Bạn không có quyền tải tệp nội bộ này"}
                    >
                      <Download size={14} />
                      <span>Tải về</span>
                    </button>

                    {isSecretaryOrChair && (
                      <button
                        type="button"
                        className="btn btn-danger btn-sm"
                        onClick={() => handleDelete(doc.id, doc.file_name)}
                        title="Xóa tài liệu"
                      >
                        <Trash2 size={14} />
                      </button>
                    )}
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {/* Modal: Tải lên tài liệu mới */}
      {uploadModalOpen && (
        <div
          style={{
            position: "fixed",
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            backgroundColor: "rgba(0, 0, 0, 0.5)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            zIndex: 9999,
            padding: "var(--space-4)"
          }}
        >
          <div
            className="card"
            style={{
              width: "100%",
              maxWidth: "520px",
              padding: "var(--space-5)",
              display: "flex",
              flexDirection: "column",
              gap: "var(--space-4)"
            }}
          >
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
              <h3 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-bold)" }}>
                Tải lên tài liệu mới vào Kho
              </h3>
              <button
                type="button"
                className="btn btn-secondary btn-sm"
                onClick={() => setUploadModalOpen(false)}
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleUpload} style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
              {/* Tên tệp tài liệu */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Tên tệp tài liệu *
                </label>
                <input
                  type="text"
                  required
                  placeholder="Ví dụ: Quy_che_chi_tieu_noi_bo_2026.pdf"
                  className="form-control"
                  value={formData.file_name}
                  onChange={(e) => setFormData({ ...formData, file_name: e.target.value })}
                />
              </div>

              {/* Mức độ bảo mật truy cập */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Mức độ bảo mật & Cấp quyền truy cập *
                </label>
                <select
                  className="form-control"
                  value={formData.access_level}
                  onChange={(e) => setFormData({ ...formData, access_level: e.target.value })}
                >
                  <option value="PUBLIC_AGENDA">Công khai cho đại biểu (PUBLIC_AGENDA)</option>
                  <option value="INTERNAL">Nội bộ HĐT - Tuyệt mật (INTERNAL)</option>
                  <option value="REGULATION">Văn bản quy chế chung (REGULATION)</option>
                </select>
                <div style={{ fontSize: "11px", color: "var(--color-text-muted)", marginTop: "4px" }}>
                  * Tài liệu INTERNAL chỉ dành riêng cho các thành viên chính thức của Hội đồng trường.
                </div>
              </div>

              {/* Liên kết phiên họp */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Liên kết phiên họp (tùy chọn)
                </label>
                <select
                  className="form-control"
                  value={formData.meeting_id}
                  onChange={(e) => setFormData({ ...formData, meeting_id: e.target.value })}
                >
                  <option value="">-- Không liên kết phiên họp --</option>
                  {meetings.map((m) => (
                    <option key={m.id} value={m.id}>
                      {m.title}
                    </option>
                  ))}
                </select>
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-2)", marginTop: "var(--space-3)" }}>
                <button
                  type="button"
                  className="btn btn-secondary"
                  onClick={() => setUploadModalOpen(false)}
                >
                  Hủy bỏ
                </button>
                <button
                  type="submit"
                  className="btn btn-primary"
                >
                  Tải lên tài liệu
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Lifecycle Confirm Dialog */}
      <ConfirmDialog
        isOpen={confirmDialog.isOpen}
        title={confirmDialog.title}
        message={confirmDialog.message}
        confirmLabel={confirmDialog.confirmLabel}
        confirmVariant={confirmDialog.confirmVariant}
        onConfirm={() => {
          confirmDialog.onConfirm();
          setConfirmDialog((prev) => ({ ...prev, isOpen: false }));
        }}
        onCancel={() => setConfirmDialog((prev) => ({ ...prev, isOpen: false }))}
      />
    </div>
  );
}
