import React, { useState, useEffect, useCallback } from "react";
import {
  ScrollText,
  Search,
  Filter,
  Plus,
  RefreshCw,
  Eye,
  Edit,
  Trash2,
  CheckCircle2,
  FileCheck,
  Send,
  Calendar,
  Layers,
  FileText,
  Vote,
  ExternalLink
} from "lucide-react";
import PageHeader from "../../../components/layout/PageHeader";
import StatusBadge from "../../../components/ui/StatusBadge";
import EmptyState from "../../../components/ui/EmptyState";
import ConfirmDialog from "../../../components/ui/ConfirmDialog";
import { useAuth } from "../../auth/context/AuthContext";
import { useToast } from "../../../components/ui/Toast";
import resolutionService from "../services/resolutionService";
import meetingService from "../../meetings/services/meetingService";

export default function ResolutionListPage() {
  const { user } = useAuth();
  const toast = useToast();

  const [resolutions, setResolutions] = useState([]);
  const [meetings, setMeetings] = useState([]);
  const [loading, setLoading] = useState(false);
  const [meta, setMeta] = useState({ total: 0, current_page: 1, total_pages: 1 });

  // Filters
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("ALL");
  const [meetingFilter, setMeetingFilter] = useState("ALL");
  const [page, setPage] = useState(1);

  // Selected Resolution Detail Modal
  const [selectedResolution, setSelectedResolution] = useState(null);
  const [detailModalOpen, setDetailModalOpen] = useState(false);

  // Create Modal
  const [createModalOpen, setCreateModalOpen] = useState(false);
  const [formData, setFormData] = useState({
    resolution_number: "",
    title: "",
    content: "",
    meeting_id: "",
    issued_date: new Date().toISOString().split("T")[0],
    status: "DRAFT"
  });

  // Confirm dialog state
  const [confirmDialog, setConfirmDialog] = useState({
    isOpen: false,
    title: "",
    message: "",
    confirmLabel: "Xác nhận",
    confirmVariant: "primary",
    onConfirm: () => {}
  });

  const isSecretaryOrChair = user && ["CHAIR", "SECRETARY", "ADMIN"].includes(user.role);
  const isChair = user && ["CHAIR", "ADMIN"].includes(user.role);

  // 1. Tải danh sách phiên họp cho bộ lọc & form
  const fetchMeetings = useCallback(async () => {
    try {
      const res = await meetingService.getMeetings({ perPage: 50 });
      if (res.success && res.data) {
        setMeetings(res.data);
      }
    } catch (err) {
      console.warn("Lỗi tải danh sách phiên họp:", err);
    }
  }, []);

  useEffect(() => {
    fetchMeetings();
  }, [fetchMeetings]);

  // 2. Tải danh sách Nghị quyết
  const fetchResolutions = useCallback(async () => {
    setLoading(true);
    try {
      const res = await resolutionService.getResolutions({
        search,
        status: statusFilter,
        meeting_id: meetingFilter,
        page,
        perPage: 10
      });

      if (res.success) {
        setResolutions(res.data);
        if (res.meta) setMeta(res.meta);
      } else {
        toast.error(res.message || "Không thể tải danh sách Nghị quyết");
      }
    } catch (err) {
      console.warn("Lỗi khi tải Nghị quyết:", err);
      toast.error("Lỗi kết nối khi tải danh sách Nghị quyết");
    } finally {
      setLoading(false);
    }
  }, [search, statusFilter, meetingFilter, page, toast]);

  useEffect(() => {
    fetchResolutions();
  }, [fetchResolutions]);

  // Xem chi tiết
  const handleViewDetail = async (id) => {
    try {
      const res = await resolutionService.getResolutionById(id);
      if (res.success) {
        setSelectedResolution(res.data);
        setDetailModalOpen(true);
      } else {
        toast.error(res.message || "Không thể lấy chi tiết Nghị quyết");
      }
    } catch (err) {
      console.warn("Lỗi xem chi tiết:", err);
      toast.error("Lỗi khi lấy chi tiết Nghị quyết");
    }
  };

  // Ký ban hành (Chủ tịch)
  const handleSign = (id, number) => {
    setConfirmDialog({
      isOpen: true,
      title: "Ký ban hành Nghị quyết",
      message: `Xác nhận Chủ tịch Hội đồng trường ký ban hành Nghị quyết số "${number}"? Văn bản sẽ chuyển sang trạng thái Đã ký ban hành (SIGNED).`,
      confirmLabel: "Ký ban hành",
      confirmVariant: "primary",
      onConfirm: async () => {
        try {
          const res = await resolutionService.signResolution(id);
          if (res.success) {
            toast.success(res.message);
            fetchResolutions();
            if (detailModalOpen && selectedResolution?.id === id) {
              handleViewDetail(id);
            }
          } else {
            toast.error(res.message || "Không thể ký ban hành");
          }
        } catch (err) {
          console.warn("Lỗi ký ban hành:", err);
          toast.error("Lỗi kết nối khi ký ban hành");
        }
      }
    });
  };

  // Công bố hiệu lực (Thư ký / Chủ tịch)
  const handlePublish = (id, number) => {
    setConfirmDialog({
      isOpen: true,
      title: "Công bố Nghị quyết chính thức",
      message: `Nghị quyết số "${number}" sẽ được công bố chính thức đến toàn trường và sẵn sàng để phân rã nhiệm vụ cho các đơn vị. Bạn có chắc chắn?`,
      confirmLabel: "Công bố hiệu lực",
      confirmVariant: "primary",
      onConfirm: async () => {
        try {
          const res = await resolutionService.publishResolution(id);
          if (res.success) {
            toast.success(res.message);
            fetchResolutions();
            if (detailModalOpen && selectedResolution?.id === id) {
              handleViewDetail(id);
            }
          } else {
            toast.error(res.message || "Không thể công bố Nghị quyết");
          }
        } catch (err) {
          console.warn("Lỗi công bố:", err);
          toast.error("Lỗi khi công bố Nghị quyết");
        }
      }
    });
  };

  // Xóa dự thảo
  const handleDelete = (id, number) => {
    setConfirmDialog({
      isOpen: true,
      title: "Xóa dự thảo Nghị quyết",
      message: `Bạn có chắc chắn muốn xóa dự thảo Nghị quyết "${number}"? Hành động này không thể hoàn tác.`,
      confirmLabel: "Xóa dự thảo",
      confirmVariant: "danger",
      onConfirm: async () => {
        try {
          const res = await resolutionService.deleteResolution(id);
          if (res.success) {
            toast.success(res.message);
            fetchResolutions();
          } else {
            toast.error(res.message || "Không thể xóa dự thảo");
          }
        } catch (err) {
          console.warn("Lỗi khi xóa:", err);
          toast.error("Lỗi khi xóa dự thảo Nghị quyết");
        }
      }
    });
  };

  // Tạo mới
  const handleCreate = async (e) => {
    e.preventDefault();
    if (!formData.meeting_id) {
      toast.error("Vui lòng chọn phiên họp thông qua Nghị quyết");
      return;
    }
    if (!formData.resolution_number.trim()) {
      toast.error("Vui lòng nhập số hiệu Nghị quyết");
      return;
    }
    if (!formData.title.trim()) {
      toast.error("Vui lòng nhập trích yếu Nghị quyết");
      return;
    }
    if (!formData.content.trim()) {
      toast.error("Vui lòng nhập toàn văn quyết nghị");
      return;
    }

    try {
      const res = await resolutionService.createResolution(formData);
      if (res.success) {
        toast.success(res.message);
        setCreateModalOpen(false);
        setFormData({
          resolution_number: "",
          title: "",
          content: "",
          meeting_id: "",
          issued_date: new Date().toISOString().split("T")[0],
          status: "DRAFT"
        });
        fetchResolutions();
      } else {
        toast.error(res.message || "Không thể tạo Nghị quyết");
      }
    } catch (err) {
      console.warn("Lỗi tạo nghị quyết:", err);
      toast.error("Lỗi khi tạo Nghị quyết mới");
    }
  };

  return (
    <div>
      <PageHeader
        title="Quản lý Biên bản & Nghị quyết Hội đồng trường"
        description="Theo dõi toàn bộ các Nghị quyết chính thức của Hội đồng trường STU, quản lý quy trình ký ban hành và công bố hiệu lực."
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Nghị quyết HĐT", path: "" }
        ]}
        actions={
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
            <button
              type="button"
              className="btn btn-secondary"
              onClick={fetchResolutions}
              title="Tải lại dữ liệu"
            >
              <RefreshCw size={16} />
              <span>Làm mới</span>
            </button>

            {isSecretaryOrChair && (
              <button
                type="button"
                className="btn btn-primary"
                onClick={() => setCreateModalOpen(true)}
              >
                <Plus size={16} />
                <span>Soạn dự thảo Nghị quyết</span>
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
            placeholder="Tìm theo số nghị quyết, trích yếu hoặc nội dung..."
            style={{ paddingLeft: "38px" }}
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setPage(1);
            }}
          />
        </div>

        <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)", flexWrap: "wrap" }}>
          {/* Lọc Trạng thái */}
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
            <Filter size={16} style={{ color: "var(--color-text-muted)" }} />
            <select
              className="form-control"
              style={{ width: "auto" }}
              value={statusFilter}
              onChange={(e) => {
                setStatusFilter(e.target.value);
                setPage(1);
              }}
            >
              <option value="ALL">Tất cả trạng thái</option>
              <option value="DRAFT">Dự thảo</option>
              <option value="SIGNED">Đã ký ban hành</option>
              <option value="PUBLISHED">Đã công bố hiệu lực</option>
            </select>
          </div>

          {/* Lọc Phiên họp */}
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

      {/* 2. Bảng danh sách Nghị quyết */}
      <div className="card" style={{ padding: 0, overflow: "hidden" }}>
        {loading ? (
          <div style={{ padding: "var(--space-8)", textAlign: "center", color: "var(--color-text-muted)" }}>
            Đang tải dữ liệu Nghị quyết...
          </div>
        ) : resolutions.length === 0 ? (
          <div style={{ padding: "var(--space-8)" }}>
            <EmptyState
              icon={ScrollText}
              title="Không tìm thấy Nghị quyết nào"
              description="Chưa có Nghị quyết nào phù hợp với điều kiện tìm kiếm hoặc phiên họp chưa ban hành nghị quyết."
            />
          </div>
        ) : (
          <div className="table-responsive">
            <table className="table" style={{ width: "100%", borderCollapse: "collapse" }}>
              <thead>
                <tr style={{ backgroundColor: "var(--color-bg-muted)", borderBottom: "1px solid var(--color-border)" }}>
                  <th style={{ padding: "var(--space-3) var(--space-4)", textAlign: "left", fontSize: "12px", textTransform: "uppercase" }}>Số hiệu</th>
                  <th style={{ padding: "var(--space-3) var(--space-4)", textAlign: "left", fontSize: "12px", textTransform: "uppercase" }}>Trích yếu nội dung</th>
                  <th style={{ padding: "var(--space-3) var(--space-4)", textAlign: "left", fontSize: "12px", textTransform: "uppercase" }}>Phiên họp & Ngày</th>
                  <th style={{ padding: "var(--space-3) var(--space-4)", textAlign: "center", fontSize: "12px", textTransform: "uppercase" }}>Trạng thái</th>
                  <th style={{ padding: "var(--space-3) var(--space-4)", textAlign: "center", fontSize: "12px", textTransform: "uppercase" }}>Tiến độ thực hiện</th>
                  <th style={{ padding: "var(--space-3) var(--space-4)", textAlign: "right", fontSize: "12px", textTransform: "uppercase" }}>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                {resolutions.map((nq) => {
                  return (
                    <tr
                      key={nq.id}
                      style={{ borderBottom: "1px solid var(--color-border)", transition: "background-color 0.2s" }}
                    >
                      <td style={{ padding: "var(--space-3) var(--space-4)", fontWeight: "var(--font-weight-bold)", color: "var(--color-primary)", whiteSpace: "nowrap" }}>
                        {nq.resolution_number}
                      </td>
                      <td style={{ padding: "var(--space-3) var(--space-4)", maxWidth: "360px" }}>
                        <div style={{ fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)", marginBottom: "2px" }}>
                          {nq.title}
                        </div>
                      </td>
                      <td style={{ padding: "var(--space-3) var(--space-4)", whiteSpace: "nowrap" }}>
                        <div style={{ fontSize: "13px", fontWeight: "var(--font-weight-medium)" }}>{nq.meeting_title}</div>
                        <div style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>{nq.issued_date || "Chưa ấn định"}</div>
                      </td>
                      <td style={{ padding: "var(--space-3) var(--space-4)", textAlign: "center" }}>
                        <StatusBadge
                          status={nq.status === "PUBLISHED" ? "success" : nq.status === "SIGNED" ? "primary" : "neutral"}
                          label={nq.status_label}
                          size="sm"
                        />
                      </td>
                      <td style={{ padding: "var(--space-3) var(--space-4)", textAlign: "center", minWidth: "140px" }}>
                        <div style={{ fontSize: "12px", fontWeight: "var(--font-weight-bold)", marginBottom: "4px" }}>
                          {nq.total_tasks > 0 ? `${nq.progress_percentage}% (${nq.completed_tasks}/${nq.total_tasks} NV)` : "Chưa phân rã NV"}
                        </div>
                        {nq.total_tasks > 0 && (
                          <div style={{ height: "6px", backgroundColor: "var(--color-bg-muted)", borderRadius: "var(--radius-full)", overflow: "hidden" }}>
                            <div
                              style={{
                                height: "100%",
                                width: `${nq.progress_percentage}%`,
                                backgroundColor: nq.progress_percentage === 100 ? "var(--color-success)" : "var(--color-primary)"
                              }}
                            />
                          </div>
                        )}
                      </td>
                      <td style={{ padding: "var(--space-3) var(--space-4)", textAlign: "right", whiteSpace: "nowrap" }}>
                        <div style={{ display: "inline-flex", alignItems: "center", gap: "var(--space-2)" }}>
                          <button
                            type="button"
                            className="btn btn-secondary btn-sm"
                            onClick={() => handleViewDetail(nq.id)}
                            title="Xem chi tiết toàn văn"
                          >
                            <Eye size={14} />
                            <span>Xem</span>
                          </button>

                          {nq.status === "DRAFT" && isChair && (
                            <button
                              type="button"
                              className="btn btn-primary btn-sm"
                              onClick={() => handleSign(nq.id, nq.resolution_number)}
                              title="Chủ tịch ký ban hành"
                            >
                              <FileCheck size={14} />
                              <span>Ký ban hành</span>
                            </button>
                          )}

                          {nq.status === "SIGNED" && isSecretaryOrChair && (
                            <button
                              type="button"
                              className="btn btn-primary btn-sm"
                              onClick={() => handlePublish(nq.id, nq.resolution_number)}
                              title="Công bố hiệu lực toàn trường"
                            >
                              <Send size={14} />
                              <span>Công bố</span>
                            </button>
                          )}

                          {nq.status === "DRAFT" && isSecretaryOrChair && (
                            <button
                              type="button"
                              className="btn btn-danger btn-sm"
                              onClick={() => handleDelete(nq.id, nq.resolution_number)}
                              title="Xóa dự thảo"
                            >
                              <Trash2 size={14} />
                            </button>
                          )}
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}

        {/* Phân trang */}
        {meta.total_pages > 1 && (
          <div
            style={{
              padding: "var(--space-3) var(--space-4)",
              borderTop: "1px solid var(--color-border)",
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              fontSize: "var(--font-size-sm)"
            }}
          >
            <span style={{ color: "var(--color-text-muted)" }}>
              Tổng cộng {meta.total} Nghị quyết (Trang {meta.current_page}/{meta.total_pages})
            </span>
            <div style={{ display: "flex", gap: "var(--space-2)" }}>
              <button
                type="button"
                className="btn btn-secondary btn-sm"
                disabled={page <= 1}
                onClick={() => setPage((p) => p - 1)}
              >
                Trang trước
              </button>
              <button
                type="button"
                className="btn btn-secondary btn-sm"
                disabled={page >= meta.total_pages}
                onClick={() => setPage((p) => p + 1)}
              >
                Trang sau
              </button>
            </div>
          </div>
        )}
      </div>

      {/* Modal: Chi tiết toàn văn Nghị quyết */}
      {detailModalOpen && selectedResolution && (
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
              maxWidth: "760px",
              maxHeight: "90vh",
              overflowY: "auto",
              padding: "var(--space-6)",
              display: "flex",
              flexDirection: "column",
              gap: "var(--space-4)"
            }}
          >
            {/* Header Modal */}
            <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", borderBottom: "1px solid var(--color-border)", paddingBottom: "var(--space-3)" }}>
              <div>
                <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", marginBottom: "4px" }}>
                  <span style={{ fontSize: "14px", fontWeight: "var(--font-weight-bold)", color: "var(--color-primary)" }}>
                    {selectedResolution.resolution_number}
                  </span>
                  <StatusBadge
                    status={selectedResolution.status === "PUBLISHED" ? "success" : selectedResolution.status === "SIGNED" ? "primary" : "neutral"}
                    label={selectedResolution.status_label}
                    size="sm"
                  />
                </div>
                <h3 style={{ fontSize: "var(--font-size-lg)", fontWeight: "var(--font-weight-bold)", color: "var(--color-text-primary)" }}>
                  {selectedResolution.title}
                </h3>
                <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
                  Phiên họp: <strong>{selectedResolution.meeting_title}</strong> • Ban hành: {selectedResolution.issued_date || "Dự thảo"}
                </div>
              </div>

              <button
                type="button"
                className="btn btn-secondary btn-sm"
                onClick={() => setDetailModalOpen(false)}
              >
                ✕
              </button>
            </div>

            {/* Toàn văn Quyết nghị */}
            <div>
              <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-bold)", color: "var(--color-text-primary)", marginBottom: "var(--space-2)" }}>
                TOÀN VĂN QUYẾT NGHỊ
              </div>
              <div
                style={{
                  padding: "var(--space-4)",
                  backgroundColor: "var(--color-bg-muted)",
                  borderRadius: "var(--radius-md)",
                  fontSize: "var(--font-size-sm)",
                  lineHeight: 1.6,
                  whiteSpace: "pre-wrap"
                }}
              >
                {selectedResolution.content}
              </div>
            </div>

            {/* Căn cứ Hồ sơ & Biểu quyết liên quan */}
            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "var(--space-3)" }}>
              <div style={{ border: "1px solid var(--color-border)", borderRadius: "var(--radius-md)", padding: "var(--space-3)" }}>
                <div style={{ fontSize: "12px", fontWeight: "var(--font-weight-bold)", display: "flex", alignItems: "center", gap: "6px", marginBottom: "8px" }}>
                  <FileText size={14} style={{ color: "var(--color-primary)" }} />
                  <span>HỒ SƠ TRÌNH CĂN CỨ ({selectedResolution.related_dossiers?.length || 0})</span>
                </div>
                {selectedResolution.related_dossiers?.length > 0 ? (
                  selectedResolution.related_dossiers.map((hs) => (
                    <div key={hs.id} style={{ fontSize: "12px", color: "var(--color-text-secondary)", marginBottom: "4px" }}>
                      • {hs.title} ({hs.department})
                    </div>
                  ))
                ) : (
                  <div style={{ fontSize: "12px", color: "var(--color-text-muted)" }}>Không có liên kết trực tiếp</div>
                )}
              </div>

              <div style={{ border: "1px solid var(--color-border)", borderRadius: "var(--radius-md)", padding: "var(--space-3)" }}>
                <div style={{ fontSize: "12px", fontWeight: "var(--font-weight-bold)", display: "flex", alignItems: "center", gap: "6px", marginBottom: "8px" }}>
                  <Vote size={14} style={{ color: "var(--color-success)" }} />
                  <span>KẾT QUẢ BIỂU QUYẾT ({selectedResolution.related_votings?.length || 0})</span>
                </div>
                {selectedResolution.related_votings?.length > 0 ? (
                  selectedResolution.related_votings.map((v) => (
                    <div key={v.id} style={{ fontSize: "12px", color: "var(--color-text-secondary)", marginBottom: "4px" }}>
                      • {v.title} — <strong style={{ color: "var(--color-success)" }}>{v.result_label}</strong>
                    </div>
                  ))
                ) : (
                  <div style={{ fontSize: "12px", color: "var(--color-text-muted)" }}>Không có liên kết trực tiếp</div>
                )}
              </div>
            </div>

            {/* Danh sách nhiệm vụ phân rã */}
            <div>
              <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-bold)", color: "var(--color-text-primary)", marginBottom: "var(--space-2)" }}>
                NHIỆM VỤ PHÂN RÃ THỰC HIỆN ({selectedResolution.tasks?.length || 0})
              </div>
              {selectedResolution.tasks?.length > 0 ? (
                <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-2)" }}>
                  {selectedResolution.tasks.map((task) => (
                    <div
                      key={task.id}
                      style={{
                        padding: "var(--space-3)",
                        border: "1px solid var(--color-border)",
                        borderRadius: "var(--radius-sm)",
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "space-between"
                      }}
                    >
                      <div>
                        <div style={{ fontWeight: "var(--font-weight-medium)", fontSize: "13px" }}>{task.title}</div>
                        <div style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>
                          Đơn vị chủ trì: <strong>{task.lead_department}</strong> • Hạn hoàn thành: {task.due_date || "Chưa ấn định"}
                        </div>
                      </div>
                      <StatusBadge status="primary" label={task.status} size="sm" />
                    </div>
                  ))}
                </div>
              ) : (
                <div style={{ fontSize: "12px", color: "var(--color-text-muted)" }}>
                  Chưa có nhiệm vụ nào được phân rã từ Nghị quyết này. (Phân hệ Phân rã nhiệm vụ sẽ hỗ trợ tách nhiệm vụ sau khi công bố).
                </div>
              )}
            </div>

            {/* Footer buttons */}
            <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-2)", borderTop: "1px solid var(--color-border)", paddingTop: "var(--space-3)" }}>
              {selectedResolution.status === "DRAFT" && isChair && (
                <button
                  type="button"
                  className="btn btn-primary"
                  onClick={() => handleSign(selectedResolution.id, selectedResolution.resolution_number)}
                >
                  <FileCheck size={16} />
                  <span>Ký ban hành</span>
                </button>
              )}

              {selectedResolution.status === "SIGNED" && isSecretaryOrChair && (
                <button
                  type="button"
                  className="btn btn-primary"
                  onClick={() => handlePublish(selectedResolution.id, selectedResolution.resolution_number)}
                >
                  <Send size={16} />
                  <span>Công bố hiệu lực</span>
                </button>
              )}

              <button
                type="button"
                className="btn btn-secondary"
                onClick={() => setDetailModalOpen(false)}
              >
                Đóng
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modal: Soạn dự thảo Nghị quyết mới */}
      {createModalOpen && (
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
              maxWidth: "640px",
              padding: "var(--space-5)",
              display: "flex",
              flexDirection: "column",
              gap: "var(--space-4)"
            }}
          >
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
              <h3 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-bold)" }}>
                Soạn thảo Dự thảo Nghị quyết Hội đồng trường
              </h3>
              <button
                type="button"
                className="btn btn-secondary btn-sm"
                onClick={() => setCreateModalOpen(false)}
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleCreate} style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
              {/* Phiên họp thông qua */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Phiên họp Hội đồng trường thông qua *
                </label>
                <select
                  className="form-control"
                  required
                  value={formData.meeting_id}
                  onChange={(e) => setFormData({ ...formData, meeting_id: e.target.value })}
                >
                  <option value="">-- Chọn phiên họp --</option>
                  {meetings.map((m) => (
                    <option key={m.id} value={m.id}>
                      {m.title} ({m.meeting_date})
                    </option>
                  ))}
                </select>
              </div>

              {/* Số hiệu & Ngày ban hành */}
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "var(--space-3)" }}>
                <div>
                  <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                    Số hiệu Nghị quyết *
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="Ví dụ: 03/NQ-HĐT-2026"
                    className="form-control"
                    value={formData.resolution_number}
                    onChange={(e) => setFormData({ ...formData, resolution_number: e.target.value })}
                  />
                </div>

                <div>
                  <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                    Ngày ký ban hành dự kiến
                  </label>
                  <input
                    type="date"
                    className="form-control"
                    value={formData.issued_date}
                    onChange={(e) => setFormData({ ...formData, issued_date: e.target.value })}
                  />
                </div>
              </div>

              {/* Trích yếu */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Trích yếu nội dung Nghị quyết *
                </label>
                <input
                  type="text"
                  required
                  placeholder="Về việc phê duyệt chủ trương..."
                  className="form-control"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                />
              </div>

              {/* Toàn văn quyết nghị */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Toàn văn quyết nghị (Điều 1, Điều 2...) *
                </label>
                <textarea
                  className="form-control"
                  rows={5}
                  required
                  placeholder="Hội đồng trường Trường Đại học Công nghệ Sài Gòn quyết nghị:&#10;Điều 1. Thông qua chủ trương...&#10;Điều 2. Giao Hiệu trưởng tổ chức triển khai..."
                  value={formData.content}
                  onChange={(e) => setFormData({ ...formData, content: e.target.value })}
                />
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-2)", marginTop: "var(--space-3)" }}>
                <button
                  type="button"
                  className="btn btn-secondary"
                  onClick={() => setCreateModalOpen(false)}
                >
                  Hủy bỏ
                </button>
                <button
                  type="submit"
                  className="btn btn-primary"
                >
                  Lưu dự thảo Nghị quyết
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
