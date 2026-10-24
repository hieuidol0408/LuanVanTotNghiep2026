import React from "react";
import { useNavigate } from "react-router-dom";
import { Calendar, MapPin, Clock, Users, ArrowRight } from "lucide-react";
import StatusBadge from "../../../components/ui/StatusBadge";
import { PROTOTYPE_UPCOMING_MEETINGS } from "../../../mock/prototypeData";

/**
 * UpcomingMeetingsSection - Danh sách lịch các phiên họp Hội đồng trường sắp tới
 */
export default function UpcomingMeetingsSection({ meetings: liveMeetings }) {
  const navigate = useNavigate();

  const displayMeetings = (liveMeetings && liveMeetings.length > 0)
    ? liveMeetings
    : PROTOTYPE_UPCOMING_MEETINGS;

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
          <span>Xem tất cả ({displayMeetings.length})</span>
          <ArrowRight size={13} />
        </button>
      </div>

      <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)", flex: 1 }}>
        {displayMeetings.map((meeting) => (
          <div
            key={meeting.id}
            onClick={() => navigate(`/meetings/${meeting.id}`)}
            style={{
              padding: "var(--space-4)",
              backgroundColor: "var(--color-bg-card)",
              border: "1px solid var(--color-border)",
              borderRadius: "var(--radius-md)",
              display: "flex",
              flexDirection: "column",
              gap: "var(--space-2)",
              transition: "border-color var(--transition-fast)",
              cursor: "pointer"
            }}
            title="Bấm để xem chi tiết phiên họp"
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
                    #{meeting.id}
                  </span>
                  <span style={{ fontSize: "11px", color: "var(--color-text-muted)" }}>
                    • {meeting.type || meeting.type_label || "Định kỳ"}
                  </span>
                </div>

                <div
                  style={{
                    fontSize: "var(--font-size-sm)",
                    fontWeight: "var(--font-weight-medium)",
                    color: "var(--color-text-primary)",
                    lineHeight: 1.4
                  }}
                >
                  {meeting.title}
                </div>
              </div>

              <StatusBadge
                status={meeting.status === "COMPLETED" ? "ACTIVE" : meeting.status === "HAPPENING" ? "WAITING" : "ACTIVE"}
                text={meeting.status_label || (meeting.status === "COMPLETED" ? "Đã kết thúc" : "Đã công bố")}
                size="sm"
              />
            </div>

            <div
              style={{
                display: "flex",
                flexWrap: "wrap",
                alignItems: "center",
                gap: "var(--space-4)",
                fontSize: "var(--font-size-xs)",
                color: "var(--color-text-secondary)",
                marginTop: "2px",
                borderTop: "1px dashed var(--color-border)",
                paddingTop: "var(--space-2)"
              }}
            >
              <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                <Clock size={12} style={{ color: "var(--color-text-muted)" }} />
                <span>
                  {meeting.time || "08:30"} {meeting.date ? `• ${new Date(meeting.date).toLocaleDateString("vi-VN")}` : ""}
                </span>
              </div>

              <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                <MapPin size={12} style={{ color: "var(--color-text-muted)" }} />
                <span>{meeting.location || "Phòng họp HĐT, Nhà A"}</span>
              </div>

              <div style={{ display: "flex", alignItems: "center", gap: "4px", marginLeft: "auto" }}>
                <Users size={12} style={{ color: "var(--color-text-muted)" }} />
                <span>{meeting.attendees_count || 15} thành viên</span>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
