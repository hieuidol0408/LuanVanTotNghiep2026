import React from "react";
import { useNavigate } from "react-router-dom";
import {
  Calendar,
  Clock,
  MapPin,
  Users,
  ShieldCheck,
  FileCheck2,
  Edit,
  Send,
  Play,
  CheckCircle,
  ExternalLink
} from "lucide-react";
import StatusBadge from "../../../components/ui/StatusBadge";

export default function MeetingGeneralInfoTab({
  meeting,
  onStatusChange,
  onSendInvitations
}) {
  const navigate = useNavigate();

  // Workflow steps
  const steps = [
    { key: "PREPARING", label: "Chuẩn bị", desc: "Xây dựng Agenda & Tài liệu" },
    { key: "SCHEDULED", label: "Đã triệu tập", desc: "Đã gửi giấy mời & mở tài liệu" },
    { key: "IN_PROGRESS", label: "Đang diễn ra", desc: "Thảo luận & Biểu quyết" },
    { key: "COMPLETED", label: "Đã bế mạc", desc: "Lập biên bản & Nghị quyết" }
  ];

  const currentStepIndex = steps.findIndex((s) => s.key === meeting.status);

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-6)" }}>
      {/* 1. Workflow Progression Bar */}
      <div
        className="card"
        style={{
          padding: "var(--space-5)",
          backgroundColor: "var(--color-bg-card)"
        }}
      >
        <div style={{ fontSize: "var(--font-size-xs)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-muted)", textTransform: "uppercase", letterSpacing: "0.05em", marginBottom: "var(--space-3)" }}>
          Tiến trình Vòng đời Phiên họp
        </div>

        <div
          style={{
            display: "grid",
            gridTemplateColumns: "repeat(auto-fit, minmax(180px, 1fr))",
            gap: "var(--space-3)"
          }}
        >
          {steps.map((step, idx) => {
            const isCompleted = currentStepIndex > idx;
            const isCurrent = currentStepIndex === idx;

            return (
              <div
                key={step.key}
                style={{
                  display: "flex",
                  alignItems: "center",
                  gap: "var(--space-3)",
                  padding: "var(--space-3)",
                  borderRadius: "var(--radius-md)",
                  backgroundColor: isCurrent
                    ? "var(--color-primary-light)"
                    : isCompleted
                    ? "var(--color-success-bg)"
                    : "var(--color-bg-app)",
                  border: isCurrent
                    ? "1px solid var(--color-primary-border)"
                    : isCompleted
                    ? "1px solid var(--color-success-border)"
                    : "1px solid var(--color-border)"
                }}
              >
                <div
                  style={{
                    width: "28px",
                    height: "28px",
                    borderRadius: "50%",
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    fontWeight: "var(--font-weight-bold)",
                    fontSize: "12px",
                    backgroundColor: isCurrent
                      ? "var(--color-primary)"
                      : isCompleted
                      ? "var(--color-success)"
                      : "var(--color-border)",
                    color: "#FFFFFF",
                    flexShrink: 0
                  }}
                >
                  {isCompleted ? "✓" : idx + 1}
                </div>

                <div>
                  <div
                    style={{
                      fontSize: "var(--font-size-sm)",
                      fontWeight: isCurrent ? "var(--font-weight-bold)" : "var(--font-weight-medium)",
                      color: isCurrent
                        ? "var(--color-primary)"
                        : isCompleted
                        ? "var(--color-success-text)"
                        : "var(--color-text-secondary)"
                    }}
                  >
                    {step.label}
                  </div>
                  <div style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>
                    {step.desc}
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* 2. Metadata Grid */}
      <div
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(auto-fit, minmax(320px, 1fr))",
          gap: "var(--space-6)"
        }}
      >
        {/* Khối Thông tin Thời gian & Địa điểm */}
        <div className="card" style={{ padding: "var(--space-5)" }}>
          <h3 style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)", marginBottom: "var(--space-4)", display: "flex", alignItems: "center", gap: "6px" }}>
            <Calendar size={16} style={{ color: "var(--color-primary)" }} />
            Thời gian & Địa điểm tổ chức
          </h3>

          <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)", fontSize: "var(--font-size-sm)" }}>
            <div style={{ display: "flex", justifyContent: "space-between", borderBottom: "1px solid var(--color-border-subtle)", paddingBottom: "8px" }}>
              <span style={{ color: "var(--color-text-muted)" }}>Khóa / Nhiệm kỳ:</span>
              <span style={{ fontWeight: "var(--font-weight-medium)" }}>{meeting.term}</span>
            </div>

            <div style={{ display: "flex", justifyContent: "space-between", borderBottom: "1px solid var(--color-border-subtle)", paddingBottom: "8px" }}>
              <span style={{ color: "var(--color-text-muted)" }}>Loại kỳ họp:</span>
              <span style={{ fontWeight: "var(--font-weight-medium)" }}>{meeting.meeting_type_label}</span>
            </div>

            <div style={{ display: "flex", justifyContent: "space-between", borderBottom: "1px solid var(--color-border-subtle)", paddingBottom: "8px" }}>
              <span style={{ color: "var(--color-text-muted)" }}>Hình thức:</span>
              <span style={{ fontWeight: "var(--font-weight-medium)" }}>{meeting.meeting_format_label}</span>
            </div>

            <div style={{ display: "flex", justifyContent: "space-between", borderBottom: "1px solid var(--color-border-subtle)", paddingBottom: "8px" }}>
              <span style={{ color: "var(--color-text-muted)" }}>Ngày tổ chức:</span>
              <span style={{ fontWeight: "var(--font-weight-semibold)", color: "var(--color-primary)" }}>{meeting.meeting_date}</span>
            </div>

            <div style={{ display: "flex", justifyContent: "space-between", borderBottom: "1px solid var(--color-border-subtle)", paddingBottom: "8px" }}>
              <span style={{ color: "var(--color-text-muted)" }}>Thời gian:</span>
              <span style={{ fontWeight: "var(--font-weight-medium)" }}>
                {meeting.start_time} {meeting.end_time ? `- ${meeting.end_time}` : ""}
              </span>
            </div>

            <div style={{ display: "flex", flexDirection: "column", gap: "4px", paddingTop: "4px" }}>
              <span style={{ color: "var(--color-text-muted)" }}>Địa điểm / Đường dẫn:</span>
              <span style={{ fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)" }}>
                {meeting.location.startsWith("http") ? (
                  <a href={meeting.location} target="_blank" rel="noreferrer" style={{ display: "inline-flex", alignItems: "center", gap: "4px" }}>
                    <span>{meeting.location}</span>
                    <ExternalLink size={13} />
                  </a>
                ) : (
                  meeting.location
                )}
              </span>
            </div>
          </div>
        </div>

        {/* Khối Thẩm quyền & Điều hành */}
        <div className="card" style={{ padding: "var(--space-5)" }}>
          <h3 style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)", marginBottom: "var(--space-4)", display: "flex", alignItems: "center", gap: "6px" }}>
            <Users size={16} style={{ color: "var(--color-primary)" }} />
            Thẩm quyền Chủ trì & Thư ký
          </h3>

          <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)", fontSize: "var(--font-size-sm)" }}>
            <div style={{ display: "flex", justifyContent: "space-between", borderBottom: "1px solid var(--color-border-subtle)", paddingBottom: "8px" }}>
              <span style={{ color: "var(--color-text-muted)" }}>Chủ tọa phiên họp:</span>
              <span style={{ fontWeight: "var(--font-weight-semibold)" }}>{meeting.chairperson}</span>
            </div>

            <div style={{ display: "flex", justifyContent: "space-between", borderBottom: "1px solid var(--color-border-subtle)", paddingBottom: "8px" }}>
              <span style={{ color: "var(--color-text-muted)" }}>Thư ký ghi biên bản:</span>
              <span style={{ fontWeight: "var(--font-weight-medium)" }}>{meeting.secretary}</span>
            </div>

            <div style={{ display: "flex", justifyContent: "space-between", borderBottom: "1px solid var(--color-border-subtle)", paddingBottom: "8px" }}>
              <span style={{ color: "var(--color-text-muted)" }}>Số mục chương trình:</span>
              <span style={{ fontWeight: "var(--font-weight-medium)" }}>{meeting.agendas.length} nội dung</span>
            </div>

            <div style={{ display: "flex", justifyContent: "space-between", borderBottom: "1px solid var(--color-border-subtle)", paddingBottom: "8px" }}>
              <span style={{ color: "var(--color-text-muted)" }}>Số tài liệu đính kèm:</span>
              <span style={{ fontWeight: "var(--font-weight-medium)" }}>{meeting.documents.length} tệp</span>
            </div>

            <div style={{ display: "flex", flexDirection: "column", gap: "4px", paddingTop: "4px" }}>
              <span style={{ color: "var(--color-text-muted)" }}>Mục tiêu & Tóm tắt:</span>
              <span style={{ color: "var(--color-text-secondary)", lineHeight: 1.5, fontSize: "var(--font-size-xs)" }}>
                {meeting.description || "Chưa có mô tả chi tiết."}
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* 3. Action Toolbar */}
      <div
        className="card"
        style={{
          padding: "var(--space-4) var(--space-5)",
          display: "flex",
          flexWrap: "wrap",
          alignItems: "center",
          justifyContent: "space-between",
          gap: "var(--space-3)"
        }}
      >
        <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
          Thao tác điều hành vòng đời phiên họp theo thẩm quyền Thư ký & Chủ tọa:
        </div>

        <div style={{ display: "flex", flexWrap: "wrap", gap: "var(--space-2)" }}>
          <button
            type="button"
            className="btn btn-secondary btn-sm"
            onClick={() => navigate(`/meetings/${meeting.id}/edit`)}
          >
            <Edit size={14} />
            <span>Chỉnh sửa thông tin</span>
          </button>

          {meeting.status === "PREPARING" && (
            <button
              type="button"
              className="btn btn-primary btn-sm"
              onClick={onSendInvitations}
            >
              <Send size={14} />
              <span>Phát hành Giấy triệu tập</span>
            </button>
          )}

          {meeting.status === "SCHEDULED" && (
            <button
              type="button"
              className="btn btn-primary btn-sm"
              onClick={() => onStatusChange("IN_PROGRESS")}
            >
              <Play size={14} />
              <span>Khai mạc Phiên họp</span>
            </button>
          )}

          {meeting.status === "IN_PROGRESS" && (
            <button
              type="button"
              className="btn btn-danger btn-sm"
              onClick={() => onStatusChange("COMPLETED")}
            >
              <CheckCircle size={14} />
              <span>Bế mạc & Chốt biên bản</span>
            </button>
          )}
        </div>
      </div>
    </div>
  );
}
