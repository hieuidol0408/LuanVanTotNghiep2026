import React from "react";
import { useNavigate } from "react-router-dom";
import { AlertCircle, Clock, CheckCircle2, ChevronRight, AlertTriangle } from "lucide-react";
import StatusBadge from "../../../components/ui/StatusBadge";
import { PROTOTYPE_URGENT_TASKS } from "../../../mock/prototypeData";

/**
 * UrgentTasksSection - Danh sách các công việc và thông báo cần chú ý xử lý gấp
 */
export default function UrgentTasksSection({ currentRole }) {
  const navigate = useNavigate();

  // Lọc hoặc sắp xếp việc ưu tiên phù hợp với vai trò hiện tại
  const relevantTasks = PROTOTYPE_URGENT_TASKS.filter((task) => {
    if (currentRole.key === "SECRETARY" || currentRole.key === "CHAIR") return true; // Thư ký & Chủ tịch thấy toàn diện
    return task.role_target === currentRole.key || task.role_target === "MEMBER";
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
              Công việc cần chú ý
            </h2>
            <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
              Các đầu việc sắp đến hạn, quá hạn hoặc hồ sơ cần xử lý
            </div>
          </div>
        </div>

        <span
          style={{
            fontSize: "11px",
            fontWeight: "var(--font-weight-semibold)",
            padding: "2px 8px",
            borderRadius: "var(--radius-full)",
            backgroundColor: "var(--color-danger-bg)",
            color: "var(--color-danger)",
            border: "1px solid var(--color-danger-border)"
          }}
        >
          {relevantTasks.length} việc cần xử lý
        </span>
      </div>

      <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)", flex: 1 }}>
        {relevantTasks.map((task) => {
          const isOverdue = task.days_left < 0;
          const isUrgent = task.days_left <= 3 && task.days_left >= 0;

          return (
            <div
              key={task.id}
              style={{
                display: "flex",
                flexDirection: "column",
                gap: "var(--space-2)",
                padding: "var(--space-3) var(--space-4)",
                backgroundColor: isOverdue ? "#FFF5F5" : isUrgent ? "#FFFDF5" : "var(--color-bg-app)",
                border: `1px solid ${isOverdue ? "var(--color-danger-border)" : isUrgent ? "var(--color-warning-border)" : "var(--color-border)"}`,
                borderRadius: "var(--radius-md)",
                transition: "background-color var(--transition-fast)"
              }}
            >
              <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", gap: "var(--space-3)" }}>
                <div style={{ display: "flex", alignItems: "flex-start", gap: "var(--space-2)" }}>
                  <span style={{ marginTop: "2px" }}>
                    {isOverdue ? (
                      <AlertCircle size={15} style={{ color: "var(--color-danger)" }} />
                    ) : (
                      <Clock size={15} style={{ color: isUrgent ? "var(--color-warning)" : "var(--color-info)" }} />
                    )}
                  </span>
                  <div>
                    <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)", lineHeight: 1.4 }}>
                      {task.title}
                    </div>
                    <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginTop: "2px" }}>
                      Đơn vị: {task.assigned_dept}
                    </div>
                  </div>
                </div>

                <StatusBadge
                  status={task.severity}
                  label={task.type_label}
                  size="sm"
                />
              </div>

              <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", borderTop: "1px solid rgba(0,0,0,0.04)", paddingTop: "6px" }}>
                <span
                  style={{
                    fontSize: "var(--font-size-xs)",
                    fontWeight: "var(--font-weight-medium)",
                    color: isOverdue ? "var(--color-danger)" : isUrgent ? "var(--color-warning)" : "var(--color-text-secondary)"
                  }}
                >
                  {task.deadline} {isOverdue ? `(Trễ ${Math.abs(task.days_left)} ngày)` : isUrgent ? `(Còn ${task.days_left} ngày)` : ""}
                </span>

                <button
                  type="button"
                  onClick={() => navigate(task.type_label.includes("hồ sơ") ? "/dossiers" : "/tasks")}
                  style={{
                    background: "none",
                    border: "none",
                    color: "var(--color-primary)",
                    fontSize: "var(--font-size-xs)",
                    fontWeight: "var(--font-weight-medium)",
                    cursor: "pointer",
                    display: "inline-flex",
                    alignItems: "center",
                    gap: "2px",
                    padding: 0
                  }}
                >
                  <span>Xử lý ngay</span>
                  <ChevronRight size={13} />
                </button>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
