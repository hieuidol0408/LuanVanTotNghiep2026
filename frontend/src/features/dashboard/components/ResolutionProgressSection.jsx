import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { ScrollText, ArrowRight } from "lucide-react";
import StatusBadge from "../../../components/ui/StatusBadge";
import { PROTOTYPE_RESOLUTIONS_PROGRESS } from "../../../mock/prototypeData";
import resolutionService from "../../resolutions/services/resolutionService";

/**
 * ResolutionProgressSection - Tình hình thực hiện các Nghị quyết Hội đồng trường đã ban hành
 */
export default function ResolutionProgressSection() {
  const navigate = useNavigate();
  const [resolutions, setResolutions] = useState(PROTOTYPE_RESOLUTIONS_PROGRESS);

  useEffect(() => {
    async function loadRealResolutions() {
      try {
        const res = await resolutionService.getResolutions({ perPage: 4 });
        if (res.success && res.data && res.data.length > 0) {
          const mapped = res.data.map((item) => ({
            id: item.resolution_number,
            title: item.title,
            issued_date: item.issued_date,
            lead_department: "Các đơn vị thi hành",
            total_tasks: item.total_tasks || 0,
            completed_tasks: item.completed_tasks || 0,
            progress_percentage: item.progress_percentage || 0,
            status: item.status,
            status_label: item.status_label
          }));
          setResolutions(mapped);
        }
      } catch (err) {
        console.warn("Lỗi tải tiến độ nghị quyết dashboard:", err);
      }
    }
    loadRealResolutions();
  }, []);

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
              backgroundColor: "var(--color-primary-light)",
              color: "var(--color-primary)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center"
            }}
          >
            <ScrollText size={16} />
          </div>
          <div>
            <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)" }}>
              Tình hình thực hiện Nghị quyết
            </h2>
            <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
              Theo dõi tiến độ phân rã nhiệm vụ và minh chứng kết quả của các đơn vị
            </div>
          </div>
        </div>

        <button
          type="button"
          onClick={() => navigate("/resolutions")}
          className="btn btn-secondary btn-sm"
          style={{ gap: "4px" }}
        >
          <span>Xem tất cả ({resolutions.length})</span>
          <ArrowRight size={13} />
        </button>
      </div>

      <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)", flex: 1 }}>
        {resolutions.map((res) => {
          const isComplete = res.progress_percentage === 100;
          const isOverdue = res.status === "OVERDUE";
          const progressColor = isComplete
            ? "var(--color-success)"
            : isOverdue
            ? "var(--color-danger)"
            : "var(--color-primary)";

          return (
            <div
              key={res.id}
              style={{
                padding: "var(--space-3) var(--space-4)",
                backgroundColor: "var(--color-bg-card)",
                border: "1px solid var(--color-border)",
                borderRadius: "var(--radius-md)",
                display: "flex",
                flexDirection: "column",
                gap: "var(--space-2)"
              }}
            >
              <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", gap: "var(--space-3)" }}>
                <div>
                  <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", marginBottom: "2px" }}>
                    <span style={{ fontSize: "12px", fontWeight: "var(--font-weight-bold)", color: "var(--color-primary)" }}>
                      Số: {res.id}
                    </span>
                    <span style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>
                      • Ban hành: {res.issued_date}
                    </span>
                  </div>
                  <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)", lineHeight: 1.4 }}>
                    {res.title}
                  </div>
                  <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginTop: "2px" }}>
                    Đơn vị chủ trì: <span style={{ fontWeight: "var(--font-weight-medium)", color: "var(--color-text-secondary)" }}>{res.lead_department}</span>
                  </div>
                </div>

                <StatusBadge
                  status={isComplete ? "success" : isOverdue ? "danger" : "info"}
                  label={res.status_label}
                  size="sm"
                />
              </div>

              {/* Thanh tiến độ trực quan */}
              <div style={{ marginTop: "4px" }}>
                <div style={{ display: "flex", justifyContent: "space-between", fontSize: "11px", color: "var(--color-text-muted)", marginBottom: "4px" }}>
                  <span>Tiến độ hoàn thành nhiệm vụ:</span>
                  <span style={{ fontWeight: "var(--font-weight-semibold)", color: progressColor }}>
                    {res.completed_tasks}/{res.total_tasks} đầu việc ({res.progress_percentage}%)
                  </span>
                </div>
                <div
                  style={{
                    height: "6px",
                    width: "100%",
                    backgroundColor: "var(--color-border-subtle)",
                    borderRadius: "var(--radius-full)",
                    overflow: "hidden"
                  }}
                >
                  <div
                    style={{
                      height: "100%",
                      width: `${res.progress_percentage}%`,
                      backgroundColor: progressColor,
                      borderRadius: "var(--radius-full)",
                      transition: "width 0.5s ease-out"
                    }}
                  />
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
