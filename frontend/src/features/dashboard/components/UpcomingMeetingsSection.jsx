import React from "react";
import { useNavigate } from "react-router-dom";
import { Calendar, MapPin, Clock, Users, FileText, ArrowRight } from "lucide-react";
import StatusBadge from "../../../components/ui/StatusBadge";
import { PROTOTYPE_UPCOMING_MEETINGS } from "../../../mock/prototypeData";

/**
 * UpcomingMeetingsSection - Danh sách lịch các phiên họp Hội đồng trường sắp tới
 */
export default function UpcomingMeetingsSection() {
  const navigate = useNavigate();

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
              backgroundColor: "var(--color-info-bg)",
              color: "var(--color-info)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center"
            }}
          >
            <Calendar size={16} />
          </div>
          <div>
            <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)" }}>
              Lịch phiên họp sắp tới
            </h2>
            <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
              Kế hoạch triệu tập và chương trình nghị sự các kỳ họp
            </div>
          </div>
        </div>

        <button
          type="button"
          onClick={() => navigate("/meetings")}
          className="btn btn-secondary btn-sm"
          style={{ gap: "4px" }}
        >
          <span>Xem tất cả ({PROTOTYPE_UPCOMING_MEETINGS.length})</span>
          <ArrowRight size={13} />
        </button>
      </div>

      <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)", flex: 1 }}>
        {PROTOTYPE_UPCOMING_MEETINGS.map((meeting) => (
          <div
            key={meeting.id}
            style={{
              padding: "var(--space-4)",
              backgroundColor: "var(--color-bg-card)",
              border: "1px solid var(--color-border)",
              borderRadius: "var(--radius-md)",
              display: "flex",
              flexDirection: "column",
              gap: "var(--space-2)",
              transition: "border-color var(--transition-fast)"
            }}
          >
            <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", gap: "var(--space-3)" }}>
              <div>
                <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", marginBottom: "4px" }}>
                  <span
                    style={{
                      fontSize: "11px",
                      fontWeight: "var(--font-weight-bold)",
                      color: "var(--color-primary)",
                      backgroundColor: "var(--color-primary-light)",
                      padding: "2px 6px",
                      borderRadius: "var(--radius-sm)"
                    }}
                  >
                    {meeting.id}
                  </span>
                  <span style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>• {meeting.type_label}</span>
                </div>

                <div
                  style={{
                    fontSize: "var(--font-size-sm)",
                    fontWeight: "var(--font-weight-semibold)",
                    color: "var(--color-text-primary)",
                    lineHeight: 1.4
                  }}
                >
                  {meeting.title}
                </div>
              </div>

              <StatusBadge
                status={meeting.status === "SCHEDULED" ? "success" : "warning"}
                label={meeting.status_label}
                size="sm"
              />
            </div>

            <div
              style={{
                display: "grid",
                gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))",
                gap: "var(--space-2)",
                fontSize: "var(--font-size-xs)",
                color: "var(--color-text-secondary)",
                marginTop: "var(--space-1)",
                paddingTop: "var(--space-2)",
                borderTop: "1px solid var(--color-border-subtle)"
              }}
            >
              <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                <Clock size={13} style={{ color: "var(--color-text-muted)" }} />
                <span>{meeting.start_time}</span>
              </div>

              <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                <MapPin size={13} style={{ color: "var(--color-text-muted)" }} />
                <span style={{ overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
                  {meeting.location}
                </span>
              </div>

              <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                <Users size={13} style={{ color: "var(--color-text-muted)" }} />
                <span>Chủ tọa: {meeting.chairperson}</span>
              </div>

              <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                <FileText size={13} style={{ color: "var(--color-text-muted)" }} />
                <span>
                  {meeting.agendas_count} nội dung thảo luận • {meeting.documents_count} tài liệu
                </span>
              </div>
            </div>

            <div style={{ display: "flex", justifyContent: "flex-end", marginTop: "4px" }}>
              <button
                type="button"
                className="btn btn-secondary btn-sm"
                onClick={() => navigate("/meetings")}
                style={{ fontSize: "var(--font-size-xs)", padding: "3px 10px" }}
              >
                Xem chi tiết & Tài liệu
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
