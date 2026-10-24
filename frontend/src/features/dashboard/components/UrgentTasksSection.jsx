import React from "react";
import { useNavigate } from "react-router-dom";
import { AlertCircle, Clock, CheckCircle2, ChevronRight, AlertTriangle } from "lucide-react";
import StatusBadge from "../../../components/ui/StatusBadge";
import { PROTOTYPE_URGENT_TASKS } from "../../../mock/prototypeData";

/**
 * UrgentTasksSection - Danh sách các công việc và thông báo cần chú ý xử lý gấp
 */
export default function UrgentTasksSection({ tasks: liveTasks, currentRole }) {
  const navigate = useNavigate();

  const sourceTasks = (liveTasks && liveTasks.length > 0)
    ? liveTasks
    : PROTOTYPE_URGENT_TASKS;

  const relevantTasks = sourceTasks.filter((task) => {
    if (!currentRole || currentRole.key === "SECRETARY" || currentRole.key === "CHAIR" || currentRole.role === "ADMIN") {
      return true;
    }
    return task.role_target === currentRole.key || task.role_target === "MEMBER" || !task.role_target;
  });

  return (
    <div
      className="card"
      style={{
        display: "flex",
        flexDirection: "column",
        height: "100%",
        padding: "var(--space-5)"
      }}
    >
      <div
        style={{
          display: "flex",
          alignItems: "center",
          justifyContent: "space-between",
          marginBottom: "var(--space-4)"
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
          <div
            style={{
              width: "28px",
              height: "28px",
              borderRadius: "var(--radius-sm)",
              backgroundColor: "var(--color-danger-bg)",
              color: "var(--color-danger)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center"
            }}
          >
            <AlertTriangle size={16} />
          </div>
          <div>
            <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)" }}>
              Nhiệm vụ cần ưu tiên & Đôn đốc
            </h2>
            <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
              Các đầu việc quá hạn hoặc sắp đến hạn chót thực hiện nghị quyết
            </div>
          </div>
        </div>

        <button
          type="button"
          onClick={() => navigate("/tasks")}
          className="btn btn-secondary btn-sm"
          style={{ gap: "4px" }}
        >
          <span>Xem tất cả ({relevantTasks.length})</span>
          <ChevronRight size={13} />
        </button>
      </div>

      <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)", flex: 1 }}>
        {relevantTasks.length === 0 ? (
          <div style={{ padding: "var(--space-6)", textAlign: "center", color: "var(--color-text-muted)" }}>
            Tất cả các nhiệm vụ đều đang diễn ra đúng tiến độ quy định!
          </div>
        ) : (
          relevantTasks.map((task) => (
            <div
              key={task.id}
              onClick={() => navigate("/tasks")}
              style={{
                padding: "var(--space-3) var(--space-4)",
                backgroundColor: "var(--color-bg-card)",
                border: "1px solid var(--color-border)",
                borderLeft: task.is_overdue
                  ? "4px solid var(--color-danger)"
                  : "4px solid var(--color-warning)",
                borderRadius: "var(--radius-md)",
                display: "flex",
                flexDirection: "column",
                gap: "var(--space-2)",
                cursor: "pointer",
                transition: "border-color var(--transition-fast)"
              }}
              title="Bấm để xem danh sách nhiệm vụ"
            >
              <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", gap: "var(--space-2)" }}>
                <div>
                  <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", marginBottom: "2px" }}>
                    <span
                      style={{
                        fontSize: "10px",
                        fontWeight: "var(--font-weight-bold)",
                        color: task.is_overdue ? "var(--color-danger)" : "var(--color-warning)",
                        backgroundColor: task.is_overdue ? "var(--color-danger-bg)" : "var(--color-warning-bg)",
                        padding: "1px 5px",
                        borderRadius: "var(--radius-sm)"
                      }}
                    >
                      {task.is_overdue ? "QUÁ HẠN" : "CẦN XỬ LÝ"}
                    </span>
                    <span style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>
                      Căn cứ: <strong>{task.resolution_number || "NQ-HĐT"}</strong>
                    </span>
                  </div>

                  <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)" }}>
                    {task.title}
                  </div>
                </div>

                <StatusBadge
                  status={task.is_overdue ? "REJECTED" : "WAITING"}
                  text={task.is_overdue ? "Quá hạn" : "Đang thực hiện"}
                  size="sm"
                />
              </div>

              <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginTop: "2px" }}>
                <span>Đơn vị: <strong>{task.assigned_to || task.unit_in_charge || "Phòng Đào tạo"}</strong></span>
                <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                  <Clock size={12} />
                  <span>Hạn chót: {task.deadline ? new Date(task.deadline).toLocaleDateString("vi-VN") : "Sắp tới"}</span>
                </div>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
}
