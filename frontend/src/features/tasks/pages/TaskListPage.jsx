import React, { useState, useEffect, useCallback } from "react";
import {
  Layers,
  Search,
  Filter,
  Plus,
  RefreshCw,
  Eye,
  CheckCircle2,
  Clock,
  AlertTriangle,
  Send,
  Building2,
  Calendar,
  FileCheck,
  Award,
  ChevronRight,
  TrendingUp,
  FileText
} from "lucide-react";
import PageHeader from "../../../components/layout/PageHeader";
import StatusBadge from "../../../components/ui/StatusBadge";
import EmptyState from "../../../components/ui/EmptyState";
import ConfirmDialog from "../../../components/ui/ConfirmDialog";
import { useAuth } from "../../auth/context/AuthContext";
import { useToast } from "../../../components/ui/Toast";
import taskService from "../services/taskService";
import resolutionService from "../../resolutions/services/resolutionService";
import apiClient from "../../../services/apiClient";

export default function TaskListPage() {
  const { user } = useAuth();
  const toast = useToast();

  const [tasks, setTasks] = useState([]);
  const [resolutions, setResolutions] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [loading, setLoading] = useState(false);
  const [meta, setMeta] = useState({ total: 0, current_page: 1, total_pages: 1 });

  // Filters
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("ALL");
  const [deptFilter, setDeptFilter] = useState("ALL");
  const [resFilter, setResFilter] = useState("ALL");
  const [page, setPage] = useState(1);

  // Selected Task Detail Modal
  const [selectedTask, setSelectedTask] = useState(null);
  const [detailModalOpen, setDetailModalOpen] = useState(false);

  // Progress Log Modal
  const [progressModalOpen, setProgressModalOpen] = useState(false);
  const [progressFormData, setProgressFormData] = useState({
    progress_percentage: 50,
    report_content: "",
    challenges: ""
  });
  const [targetTaskId, setTargetTaskId] = useState(null);

  // Create Task Modal
  const [createModalOpen, setCreateModalOpen] = useState(false);
  const [createFormData, setCreateFormData] = useState({
    resolution_id: "",
    title: "",
    content: "",
    lead_department_id: "",
    start_date: new Date().toISOString().split("T")[0],
    due_date: "",
    person_in_charge: ""
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

  const isSecretaryOrChair = user && ["CHAIR", "SECRETARY", "ADMIN"].includes(user.role);

  // 1. Tải danh mục phụ trợ (Nghị quyết & Đơn vị)
  const fetchAuxiliaryData = useCallback(async () => {
    try {
      const resRes = await resolutionService.getResolutions({ perPage: 100 });
      if (resRes.success && resRes.data) {
        setResolutions(resRes.data);
      }

      // Tải danh sách đơn vị từ endpoint nội bộ hoặc mock
      try {
        const deptRes = await apiClient.get("/departments");
        if (deptRes.success && deptRes.data) {
          setDepartments(deptRes.data);
        }
      } catch (deptErr) {
        // Fallback default departments
        setDepartments([
          { id: 1, name: "Văn phòng Hội đồng trường" },
          { id: 2, name: "Ban Giám hiệu / Văn phòng BGH" },
          { id: 3, name: "Phòng Đào tạo" },
          { id: 4, name: "Phòng Quản lý Khoa học & Hợp tác Quốc tế" },
          { id: 5, name: "Phòng Kế hoạch - Tài chính" },
          { id: 6, name: "Khoa Công nghệ Thông tin" },
          { id: 7, name: "Khoa Điện - Điện tử" },
          { id: 8, name: "Khoa Kỹ thuật Công trình" }
        ]);
      }
    } catch (err) {
      console.warn("Lỗi tải danh mục phụ trợ:", err);
    }
  }, []);

  useEffect(() => {
    fetchAuxiliaryData();
  }, [fetchAuxiliaryData]);

  // 2. Tải danh sách nhiệm vụ
  const fetchTasks = useCallback(async () => {
    setLoading(true);
    try {
      const res = await taskService.getTasks({
        search,
        status: statusFilter,
        department_id: deptFilter,
        resolution_id: resFilter,
        page,
        perPage: 10
      });

      if (res.success) {
        setTasks(res.data);
        if (res.meta) setMeta(res.meta);
      } else {
        toast.error(res.message || "Không thể tải danh sách nhiệm vụ");
      }
    } catch (err) {
      console.warn("Lỗi tải danh sách nhiệm vụ:", err);
      toast.error("Lỗi kết nối khi tải danh sách nhiệm vụ");
    } finally {
      setLoading(false);
    }
  }, [search, statusFilter, deptFilter, resFilter, page, toast]);

  useEffect(() => {
    fetchTasks();
  }, [fetchTasks]);

  // Xem chi tiết
  const handleViewDetail = async (id) => {
    try {
      const res = await taskService.getTaskById(id);
      if (res.success) {
        setSelectedTask(res.data);
        setDetailModalOpen(true);
      } else {
        toast.error(res.message || "Không thể lấy chi tiết nhiệm vụ");
      }
    } catch (err) {
      console.warn("Lỗi xem chi tiết nhiệm vụ:", err);
      toast.error("Lỗi khi tải chi tiết nhiệm vụ");
    }
  };

  // Mở modal nộp báo cáo tiến độ
  const handleOpenProgressModal = (task) => {
    setTargetTaskId(task.id);
    setProgressFormData({
      progress_percentage: task.progress_percentage || 50,
      report_content: "",
      challenges: ""
    });
    setProgressModalOpen(true);
  };

  // Gửi báo cáo tiến độ
  const handleSubmitProgress = async (e) => {
    e.preventDefault();
    if (!progressFormData.report_content.trim()) {
      toast.error("Vui lòng nhập nội dung chi tiết báo cáo tiến độ");
      return;
    }

    try {
      const res = await taskService.submitProgressLog(targetTaskId, progressFormData);
      if (res.success) {
        toast.success(res.message);
        setProgressModalOpen(false);
        fetchTasks();
        if (detailModalOpen && selectedTask?.id === targetTaskId) {
          handleViewDetail(targetTaskId);
        }
      } else {
        toast.error(res.message || "Không thể nộp báo cáo");
      }
    } catch (err) {
      console.warn("Lỗi nộp báo cáo:", err);
      toast.error("Lỗi kết nối khi nộp báo cáo");
    }
  };

  // Thư ký / Chủ tịch nghiệm thu báo cáo
  const handleReviewLog = (logId, status) => {
    const actionLabel = status === "APPROVED" ? "Nghiệm thu đạt" : "Yêu cầu chỉnh sửa";
    setConfirmDialog({
      isOpen: true,
      title: `${actionLabel} báo cáo tiến độ`,
      message: `Xác nhận ${actionLabel.toLowerCase()} cho báo cáo tiến độ này?`,
      confirmLabel: actionLabel,
      confirmVariant: status === "APPROVED" ? "primary" : "danger",
      onConfirm: async () => {
        try {
          const res = await taskService.reviewProgressLog(logId, { status });
          if (res.success) {
            toast.success(res.message);
            fetchTasks();
            if (selectedTask) {
              handleViewDetail(selectedTask.id);
            }
          } else {
            toast.error(res.message || "Không thể xử lý nghiệm thu");
          }
        } catch (err) {
          console.warn("Lỗi nghiệm thu:", err);
          toast.error("Lỗi khi nghiệm thu báo cáo");
        }
      }
    });
  };

  // Tạo nhiệm vụ mới từ Nghị quyết
  const handleCreateTask = async (e) => {
    e.preventDefault();
    if (!createFormData.resolution_id) {
      toast.error("Vui lòng chọn Nghị quyết gốc");
      return;
    }
    if (!createFormData.lead_department_id) {
      toast.error("Vui lòng chọn đơn vị chủ trì thực hiện");
      return;
    }
    if (!createFormData.title.trim()) {
      toast.error("Vui lòng nhập tên nhiệm vụ");
      return;
    }
    if (!createFormData.due_date) {
      toast.error("Vui lòng chọn hạn chót hoàn thành");
      return;
    }

    try {
      const res = await taskService.createTask(createFormData);
      if (res.success) {
        toast.success(res.message);
        setCreateModalOpen(false);
        setCreateFormData({
          resolution_id: "",
          title: "",
          content: "",
          lead_department_id: "",
          start_date: new Date().toISOString().split("T")[0],
          due_date: "",
          person_in_charge: ""
        });
        fetchTasks();
      } else {
        toast.error(res.message || "Không thể tạo nhiệm vụ");
      }
    } catch (err) {
      console.warn("Lỗi tạo nhiệm vụ:", err);
      toast.error("Lỗi khi tạo nhiệm vụ mới");
    }
  };

  // Thống kê nhanh
  const totalTasks = tasks.length;
  const inProgressCount = tasks.filter((t) => t.status === "IN_PROGRESS").length;
  const completedCount = tasks.filter((t) => t.status === "COMPLETED").length;
  const overdueCount = tasks.filter((t) => t.is_overdue || t.status === "OVERDUE").length;

  return (
    <div>
      <PageHeader
        title="Phân rã Nhiệm vụ & Theo dõi thực hiện"
        description="Quản lý chi tiết việc tách nhiệm vụ từ các Nghị quyết HĐT, theo dõi phân công đơn vị chủ trì, nhật ký tiến độ và minh chứng nghiệm thu."
        breadcrumbItems={[
          { label: "Bảng điều hành", path: "/" },
          { label: "Nhiệm vụ & Tiến độ", path: "" }
        ]}
        actions={
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
            <button
              type="button"
              className="btn btn-secondary"
              onClick={fetchTasks}
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
                <span>Phân rã nhiệm vụ</span>
              </button>
            )}
          </div>
        }
      />

      {/* 1. Các thẻ số liệu tổng quan */}
      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))", gap: "var(--space-3)", marginBottom: "var(--space-4)" }}>
        <div className="card" style={{ padding: "var(--space-4)", display: "flex", alignItems: "center", gap: "var(--space-3)" }}>
          <div style={{ width: "40px", height: "40px", borderRadius: "var(--radius-md)", backgroundColor: "var(--color-primary-light)", color: "var(--color-primary)", display: "flex", alignItems: "center", justifyContent: "center" }}>
            <Layers size={20} />
          </div>
          <div>
            <div style={{ fontSize: "12px", color: "var(--color-text-muted)" }}>Tổng số đầu việc</div>
            <div style={{ fontSize: "var(--font-size-xl)", fontWeight: "var(--font-weight-bold)", color: "var(--color-text-primary)" }}>{meta.total || totalTasks}</div>
          </div>
        </div>

        <div className="card" style={{ padding: "var(--space-4)", display: "flex", alignItems: "center", gap: "var(--space-3)" }}>
          <div style={{ width: "40px", height: "40px", borderRadius: "var(--radius-md)", backgroundColor: "rgba(59, 130, 246, 0.1)", color: "#2563eb", display: "flex", alignItems: "center", justifyContent: "center" }}>
            <Clock size={20} />
          </div>
          <div>
            <div style={{ fontSize: "12px", color: "var(--color-text-muted)" }}>Đang thực hiện</div>
            <div style={{ fontSize: "var(--font-size-xl)", fontWeight: "var(--font-weight-bold)", color: "#2563eb" }}>{inProgressCount}</div>
          </div>
        </div>

        <div className="card" style={{ padding: "var(--space-4)", display: "flex", alignItems: "center", gap: "var(--space-3)" }}>
          <div style={{ width: "40px", height: "40px", borderRadius: "var(--radius-md)", backgroundColor: "rgba(16, 185, 129, 0.1)", color: "var(--color-success)", display: "flex", alignItems: "center", justifyContent: "center" }}>
            <CheckCircle2 size={20} />
          </div>
          <div>
            <div style={{ fontSize: "12px", color: "var(--color-text-muted)" }}>Đã hoàn thành 100%</div>
            <div style={{ fontSize: "var(--font-size-xl)", fontWeight: "var(--font-weight-bold)", color: "var(--color-success)" }}>{completedCount}</div>
          </div>
        </div>

        <div className="card" style={{ padding: "var(--space-4)", display: "flex", alignItems: "center", gap: "var(--space-3)" }}>
          <div style={{ width: "40px", height: "40px", borderRadius: "var(--radius-md)", backgroundColor: "rgba(239, 68, 68, 0.1)", color: "var(--color-danger)", display: "flex", alignItems: "center", justifyContent: "center" }}>
            <AlertTriangle size={20} />
          </div>
          <div>
            <div style={{ fontSize: "12px", color: "var(--color-text-muted)" }}>Cần đôn đốc quá hạn</div>
            <div style={{ fontSize: "var(--font-size-xl)", fontWeight: "var(--font-weight-bold)", color: "var(--color-danger)" }}>{overdueCount}</div>
          </div>
        </div>
      </div>

      {/* 2. Bộ lọc & Tìm kiếm */}
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
            placeholder="Tìm theo tên nhiệm vụ, đơn vị hoặc số nghị quyết..."
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
            <option value="ASSIGNED">Mới giao việc</option>
            <option value="IN_PROGRESS">Đang thực hiện</option>
            <option value="COMPLETED">Đã hoàn thành</option>
            <option value="OVERDUE">Quá hạn tiến độ</option>
          </select>

          {/* Lọc Đơn vị */}
          <select
            className="form-control"
            style={{ width: "auto", maxWidth: "200px" }}
            value={deptFilter}
            onChange={(e) => {
              setDeptFilter(e.target.value);
              setPage(1);
            }}
          >
            <option value="ALL">Tất cả đơn vị</option>
            {departments.map((d) => (
              <option key={d.id} value={d.id}>
                {d.name || d.TenDonVi}
              </option>
            ))}
          </select>

          {/* Lọc Nghị quyết */}
          <select
            className="form-control"
            style={{ width: "auto", maxWidth: "220px" }}
            value={resFilter}
            onChange={(e) => {
              setResFilter(e.target.value);
              setPage(1);
            }}
          >
            <option value="ALL">Tất cả Nghị quyết</option>
            {resolutions.map((r) => (
              <option key={r.id} value={r.id}>
                {r.resolution_number}: {r.title?.substring(0, 30)}...
              </option>
            ))}
          </select>
        </div>
      </div>

      {/* 3. Bảng danh sách nhiệm vụ */}
      <div className="card" style={{ padding: 0, overflow: "hidden" }}>
        {loading ? (
          <div style={{ padding: "var(--space-8)", textAlign: "center", color: "var(--color-text-muted)" }}>
            Đang tải dữ liệu nhiệm vụ...
          </div>
        ) : tasks.length === 0 ? (
          <div style={{ padding: "var(--space-8)" }}>
            <EmptyState
              icon={Layers}
              title="Không tìm thấy nhiệm vụ nào"
              description="Chưa có nhiệm vụ nào được phân rã khớp với điều kiện tìm kiếm hiện tại."
            />
          </div>
        ) : (
          <div className="table-responsive">
            <table className="table" style={{ width: "100%", borderCollapse: "collapse" }}>
              <thead>
                <tr style={{ backgroundColor: "var(--color-bg-muted)", borderBottom: "1px solid var(--color-border)" }}>
                  <th style={{ padding: "var(--space-3) var(--space-4)", textAlign: "left", fontSize: "12px", textTransform: "uppercase" }}>Tên nhiệm vụ & Nghị quyết</th>
                  <th style={{ padding: "var(--space-3) var(--space-4)", textAlign: "left", fontSize: "12px", textTransform: "uppercase" }}>Đơn vị chủ trì</th>
                  <th style={{ padding: "var(--space-3) var(--space-4)", textAlign: "left", fontSize: "12px", textTransform: "uppercase" }}>Hạn chót</th>
                  <th style={{ padding: "var(--space-3) var(--space-4)", textAlign: "center", fontSize: "12px", textTransform: "uppercase" }}>Tiến độ</th>
                  <th style={{ padding: "var(--space-3) var(--space-4)", textAlign: "center", fontSize: "12px", textTransform: "uppercase" }}>Trạng thái</th>
                  <th style={{ padding: "var(--space-3) var(--space-4)", textAlign: "right", fontSize: "12px", textTransform: "uppercase" }}>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                {tasks.map((task) => {
                  const isComplete = task.status === "COMPLETED";
                  const isOverdue = task.is_overdue || task.status === "OVERDUE";
                  const progressColor = isComplete
                    ? "var(--color-success)"
                    : isOverdue
                    ? "var(--color-danger)"
                    : "var(--color-primary)";

                  return (
                    <tr key={task.id} style={{ borderBottom: "1px solid var(--color-border)" }}>
                      <td style={{ padding: "var(--space-3) var(--space-4)", maxWidth: "340px" }}>
                        <div style={{ fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)", marginBottom: "2px" }}>
                          {task.title}
                        </div>
                        <div style={{ fontSize: "11px", color: "var(--color-primary)", fontWeight: "var(--font-weight-bold)" }}>
                          Nghị quyết: {task.resolution_number}
                        </div>
                      </td>

                      <td style={{ padding: "var(--space-3) var(--space-4)", whiteSpace: "nowrap" }}>
                        <div style={{ fontSize: "13px", fontWeight: "var(--font-weight-medium)" }}>{task.lead_department}</div>
                        {task.person_in_charge && (
                          <div style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>Phụ trách: {task.person_in_charge}</div>
                        )}
                      </td>

                      <td style={{ padding: "var(--space-3) var(--space-4)", whiteSpace: "nowrap" }}>
                        <div style={{ fontSize: "12px", color: isOverdue ? "var(--color-danger)" : "var(--color-text-primary)", fontWeight: isOverdue ? "var(--font-weight-bold)" : "normal" }}>
                          {task.due_date || "Chưa ấn định"}
                        </div>
                        {isOverdue && (
                          <span style={{ fontSize: "10px", color: "var(--color-danger)", display: "flex", alignItems: "center", gap: "2px" }}>
                            <AlertTriangle size={11} /> Quá hạn
                          </span>
                        )}
                      </td>

                      <td style={{ padding: "var(--space-3) var(--space-4)", minWidth: "140px" }}>
                        <div style={{ display: "flex", justifyContent: "space-between", fontSize: "11px", marginBottom: "4px" }}>
                          <span style={{ fontWeight: "var(--font-weight-bold)", color: progressColor }}>
                            {task.progress_percentage}%
                          </span>
                          <span style={{ color: "var(--color-text-muted)" }}>
                            {task.reports_count} báo cáo
                          </span>
                        </div>
                        <div style={{ height: "6px", backgroundColor: "var(--color-bg-muted)", borderRadius: "var(--radius-full)", overflow: "hidden" }}>
                          <div
                            style={{
                              height: "100%",
                              width: `${task.progress_percentage}%`,
                              backgroundColor: progressColor,
                              transition: "width 0.3s"
                            }}
                          />
                        </div>
                      </td>

                      <td style={{ padding: "var(--space-3) var(--space-4)", textAlign: "center" }}>
                        <StatusBadge
                          status={isComplete ? "success" : isOverdue ? "danger" : "primary"}
                          label={task.status_label}
                          size="sm"
                        />
                      </td>

                      <td style={{ padding: "var(--space-3) var(--space-4)", textAlign: "right", whiteSpace: "nowrap" }}>
                        <div style={{ display: "inline-flex", alignItems: "center", gap: "var(--space-2)" }}>
                          <button
                            type="button"
                            className="btn btn-secondary btn-sm"
                            onClick={() => handleViewDetail(task.id)}
                            title="Xem chi tiết & Nhật ký tiến độ"
                          >
                            <Eye size={14} />
                            <span>Chi tiết</span>
                          </button>

                          {task.status !== "COMPLETED" && (
                            <button
                              type="button"
                              className="btn btn-primary btn-sm"
                              onClick={() => handleOpenProgressModal(task)}
                              title="Nộp báo cáo tiến độ mới"
                            >
                              <TrendingUp size={14} />
                              <span>Báo cáo</span>
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
              Tổng cộng {meta.total} Nhiệm vụ (Trang {meta.current_page}/{meta.total_pages})
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

      {/* Modal: Chi tiết nhiệm vụ kèm nhật ký tiến độ */}
      {detailModalOpen && selectedTask && (
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
            <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", borderBottom: "1px solid var(--color-border)", paddingBottom: "var(--space-3)" }}>
              <div>
                <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", marginBottom: "4px" }}>
                  <span style={{ fontSize: "12px", fontWeight: "var(--font-weight-bold)", color: "var(--color-primary)" }}>
                    Nghị quyết: {selectedTask.resolution_number}
                  </span>
                  <StatusBadge
                    status={selectedTask.status === "COMPLETED" ? "success" : selectedTask.is_overdue ? "danger" : "primary"}
                    label={selectedTask.status_label}
                    size="sm"
                  />
                </div>
                <h3 style={{ fontSize: "var(--font-size-lg)", fontWeight: "var(--font-weight-bold)" }}>
                  {selectedTask.title}
                </h3>
                <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
                  Chủ trì: <strong>{selectedTask.lead_department}</strong> • Hạn chót: {selectedTask.due_date || "Chưa ấn định"}
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

            {/* Nội dung giao việc */}
            <div>
              <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-bold)", color: "var(--color-text-primary)", marginBottom: "var(--space-2)" }}>
                NỘI DUNG NHIỆM VỤ GIAO VIỆC
              </div>
              <div style={{ padding: "var(--space-3)", backgroundColor: "var(--color-bg-muted)", borderRadius: "var(--radius-md)", fontSize: "var(--font-size-sm)", lineHeight: 1.5 }}>
                {selectedTask.content}
              </div>
            </div>

            {/* Lịch sử báo cáo tiến độ */}
            <div>
              <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginBottom: "var(--space-3)" }}>
                <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-bold)", color: "var(--color-text-primary)" }}>
                  NHẬT KÝ BÁO CÁO TIẾN ĐỘ ({selectedTask.progress_logs?.length || 0})
                </div>

                {selectedTask.status !== "COMPLETED" && (
                  <button
                    type="button"
                    className="btn btn-primary btn-sm"
                    onClick={() => {
                      setDetailModalOpen(false);
                      handleOpenProgressModal(selectedTask);
                    }}
                  >
                    <Plus size={14} />
                    <span>Nộp báo cáo mới</span>
                  </button>
                )}
              </div>

              {selectedTask.progress_logs?.length > 0 ? (
                <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
                  {selectedTask.progress_logs.map((log) => (
                    <div
                      key={log.id}
                      style={{
                        padding: "var(--space-4)",
                        border: "1px solid var(--color-border)",
                        borderRadius: "var(--radius-md)",
                        backgroundColor: "var(--color-bg-card)",
                        display: "flex",
                        flexDirection: "column",
                        gap: "var(--space-2)"
                      }}
                    >
                      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
                        <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
                          <span style={{ fontSize: "14px", fontWeight: "var(--font-weight-bold)", color: log.progress_percentage === 100 ? "var(--color-success)" : "var(--color-primary)" }}>
                            Tiến độ: {log.progress_percentage}%
                          </span>
                          <span style={{ fontSize: "12px", color: "var(--color-text-muted)" }}>
                            • Ngày nộp: {log.report_date}
                          </span>
                        </div>

                        <StatusBadge
                          status={log.status === "APPROVED" ? "success" : log.status === "REVISION_REQUIRED" ? "danger" : "warning"}
                          label={log.status_label}
                          size="sm"
                        />
                      </div>

                      <div style={{ fontSize: "var(--font-size-sm)", color: "var(--color-text-primary)", lineHeight: 1.5 }}>
                        {log.content}
                      </div>

                      {log.challenges && (
                        <div style={{ fontSize: "12px", color: "var(--color-warning-text)", backgroundColor: "var(--color-warning-bg)", padding: "4px 8px", borderRadius: "var(--radius-sm)" }}>
                          <strong>Khó khăn/Kiến nghị:</strong> {log.challenges}
                        </div>
                      )}

                      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", borderTop: "1px dashed var(--color-border)", paddingTop: "8px", marginTop: "4px", fontSize: "11px", color: "var(--color-text-muted)" }}>
                        <span>Người nộp: <strong>{log.reporter_name}</strong> ({log.department})</span>

                        {log.status === "PENDING" && isSecretaryOrChair && (
                          <div style={{ display: "flex", gap: "var(--space-2)" }}>
                            <button
                              type="button"
                              className="btn btn-danger btn-sm"
                              style={{ padding: "2px 8px", fontSize: "11px" }}
                              onClick={() => handleReviewLog(log.id, "REVISION_REQUIRED")}
                            >
                              Yêu cầu sửa
                            </button>
                            <button
                              type="button"
                              className="btn btn-primary btn-sm"
                              style={{ padding: "2px 8px", fontSize: "11px" }}
                              onClick={() => handleReviewLog(log.id, "APPROVED")}
                            >
                              Nghiệm thu đạt
                            </button>
                          </div>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              ) : (
                <div style={{ fontSize: "12px", color: "var(--color-text-muted)", padding: "var(--space-4)", textAlign: "center", border: "1px dashed var(--color-border)", borderRadius: "var(--radius-md)" }}>
                  Chưa có báo cáo tiến độ nào được nộp cho nhiệm vụ này.
                </div>
              )}
            </div>

            <div style={{ display: "flex", justifyContent: "flex-end", borderTop: "1px solid var(--color-border)", paddingTop: "var(--space-3)" }}>
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

      {/* Modal: Nộp báo cáo tiến độ */}
      {progressModalOpen && (
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
                Nộp Báo cáo tiến độ & Minh chứng
              </h3>
              <button
                type="button"
                className="btn btn-secondary btn-sm"
                onClick={() => setProgressModalOpen(false)}
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleSubmitProgress} style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
              {/* Tỷ lệ tiến độ % */}
              <div>
                <div style={{ display: "flex", justifyContent: "space-between", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  <span>Tỷ lệ phần trăm tiến độ đã hoàn thành *</span>
                  <strong style={{ color: "var(--color-primary)" }}>{progressFormData.progress_percentage}%</strong>
                </div>
                <input
                  type="range"
                  min="0"
                  max="100"
                  step="5"
                  className="form-range"
                  style={{ width: "100%" }}
                  value={progressFormData.progress_percentage}
                  onChange={(e) => setProgressFormData({ ...progressFormData, progress_percentage: Number(e.target.value) })}
                />
              </div>

              {/* Nội dung báo cáo */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Nội dung kết quả đã thực hiện *
                </label>
                <textarea
                  className="form-control"
                  rows={4}
                  required
                  placeholder="Mô tả cụ thể khối lượng công việc, sản phẩm đầu ra đã hoàn tất..."
                  value={progressFormData.report_content}
                  onChange={(e) => setProgressFormData({ ...progressFormData, report_content: e.target.value })}
                />
              </div>

              {/* Khó khăn vướng mắc */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Khó khăn, vướng mắc & Đề xuất kiến nghị (nếu có)
                </label>
                <textarea
                  className="form-control"
                  rows={2}
                  placeholder="Nêu rõ khó khăn cần HĐT hoặc Ban Giám hiệu hỗ trợ tháo gỡ..."
                  value={progressFormData.challenges}
                  onChange={(e) => setProgressFormData({ ...progressFormData, challenges: e.target.value })}
                />
              </div>

              <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-2)", marginTop: "var(--space-3)" }}>
                <button
                  type="button"
                  className="btn btn-secondary"
                  onClick={() => setProgressModalOpen(false)}
                >
                  Hủy bỏ
                </button>
                <button
                  type="submit"
                  className="btn btn-primary"
                >
                  Gửi báo cáo tiến độ
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal: Phân rã nhiệm vụ mới từ Nghị quyết */}
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
              maxWidth: "600px",
              padding: "var(--space-5)",
              display: "flex",
              flexDirection: "column",
              gap: "var(--space-4)"
            }}
          >
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
              <h3 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-bold)" }}>
                Phân rã Nhiệm vụ từ Nghị quyết HĐT
              </h3>
              <button
                type="button"
                className="btn btn-secondary btn-sm"
                onClick={() => setCreateModalOpen(false)}
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleCreateTask} style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)" }}>
              {/* Nghị quyết gốc */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Nghị quyết gốc thông qua *
                </label>
                <select
                  className="form-control"
                  required
                  value={createFormData.resolution_id}
                  onChange={(e) => setCreateFormData({ ...createFormData, resolution_id: e.target.value })}
                >
                  <option value="">-- Chọn Nghị quyết gốc --</option>
                  {resolutions.map((r) => (
                    <option key={r.id} value={r.id}>
                      {r.resolution_number}: {r.title}
                    </option>
                  ))}
                </select>
              </div>

              {/* Tên nhiệm vụ */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Tên đầu việc nhiệm vụ *
                </label>
                <input
                  type="text"
                  required
                  placeholder="Ví dụ: Xây dựng chuẩn đầu ra ngành Kỹ thuật Robot..."
                  className="form-control"
                  value={createFormData.title}
                  onChange={(e) => setCreateFormData({ ...createFormData, title: e.target.value })}
                />
              </div>

              {/* Nội dung giao việc */}
              <div>
                <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                  Nội dung chi tiết giao việc & Yêu cầu đầu ra *
                </label>
                <textarea
                  className="form-control"
                  rows={3}
                  required
                  placeholder="Mô tả các yêu cầu cần hoàn thành..."
                  value={createFormData.content}
                  onChange={(e) => setCreateFormData({ ...createFormData, content: e.target.value })}
                />
              </div>

              {/* Đơn vị chủ trì & Người phụ trách */}
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: "var(--space-3)" }}>
                <div>
                  <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                    Đơn vị chủ trì *
                  </label>
                  <select
                    className="form-control"
                    required
                    value={createFormData.lead_department_id}
                    onChange={(e) => setCreateFormData({ ...createFormData, lead_department_id: e.target.value })}
                  >
                    <option value="">-- Chọn đơn vị --</option>
                    {departments.map((d) => (
                      <option key={d.id} value={d.id}>
                        {d.name || d.TenDonVi}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="form-label" style={{ display: "block", marginBottom: "4px", fontSize: "var(--font-size-sm)" }}>
                    Hạn chót hoàn thành *
                  </label>
                  <input
                    type="date"
                    required
                    className="form-control"
                    value={createFormData.due_date}
                    onChange={(e) => setCreateFormData({ ...createFormData, due_date: e.target.value })}
                  />
                </div>
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
                  Phân rã nhiệm vụ
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
