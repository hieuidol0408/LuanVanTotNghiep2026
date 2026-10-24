import React, { useState } from "react";
import { UserPlus, Check, X, Clock, AlertCircle, ShieldCheck, ShieldAlert } from "lucide-react";
import StatusBadge from "../../../components/ui/StatusBadge";
import DataTable from "../../../components/ui/DataTable";
import AttendeeModal from "./AttendeeModal";

export default function MeetingAttendeesTab({
  attendees = [],
  onUpdateAttendance,
  onAddGuest
}) {
  const [filterType, setFilterType] = useState("ALL"); // ALL | OFFICIAL | GUEST
  const [isModalOpen, setIsModalOpen] = useState(false);

  // Thống kê thành viên HĐT chính thức
  const officialMembers = attendees.filter((a) => a.attendee_type === "OFFICIAL");
  const presentOfficialCount = officialMembers.filter((a) => a.attendance_status === "PRESENT").length;
  const quorumPercentage = officialMembers.length > 0 ? ((presentOfficialCount / officialMembers.length) * 100).toFixed(1) : 0;
  const isQuorumMet = Number(quorumPercentage) >= 66.67; // Tối thiểu 2/3 theo quy chế STU

  const guests = attendees.filter((a) => a.attendee_type === "GUEST");

  const filteredAttendees = attendees.filter((a) => {
    if (filterType === "OFFICIAL") return a.attendee_type === "OFFICIAL";
    if (filterType === "GUEST") return a.attendee_type === "GUEST";
    return true;
  });

  const columns = [
    {
      key: "full_name",
      title: "Họ và tên đại biểu",
      render: (val, row) => (
        <div>
          <div style={{ fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)" }}>
            {row.academic_title ? `${row.academic_title}. ` : ""}{val}
          </div>
          <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
            {row.role_in_council} • {row.department}
          </div>
        </div>
      )
    },
    {
      key: "attendee_type",
      title: "Tư cách tham dự",
      width: "180px",
      render: (val) =>
        val === "OFFICIAL" ? (
          <span
            style={{
              fontSize: "11px",
              fontWeight: "var(--font-weight-semibold)",
              padding: "2px 8px",
              borderRadius: "var(--radius-sm)",
              backgroundColor: "var(--color-primary-light)",
              color: "var(--color-primary)"
            }}
          >
            Thành viên HĐT chính thức
          </span>
        ) : (
          <span
            style={{
              fontSize: "11px",
              fontWeight: "var(--font-weight-medium)",
              padding: "2px 8px",
              borderRadius: "var(--radius-sm)",
              backgroundColor: "var(--color-bg-hover)",
              color: "var(--color-text-secondary)",
              border: "1px solid var(--color-border)"
            }}
          >
            Khách mời chuyên môn
          </span>
        )
    },
    {
      key: "can_vote",
      title: "Quyền biểu quyết",
      width: "160px",
      align: "center",
      render: (val, row) =>
        row.attendee_type === "GUEST" ? (
          <span style={{ fontSize: "11px", color: "var(--color-text-muted)", fontStyle: "italic" }}>
            Không có quyền (Khách mời)
          </span>
        ) : val ? (
          <span style={{ display: "inline-flex", alignItems: "center", gap: "4px", fontSize: "11px", color: "var(--color-success)", fontWeight: "var(--font-weight-semibold)" }}>
            <ShieldCheck size={14} /> Có quyền bỏ phiếu
          </span>
        ) : (
          <span style={{ display: "inline-flex", alignItems: "center", gap: "4px", fontSize: "11px", color: "var(--color-danger)" }}>
            <ShieldAlert size={14} /> Bị khóa (Vắng mặt)
          </span>
        )
    },
    {
      key: "attendance_status",
      title: "Điểm danh",
      width: "140px",
      render: (val, row) => {
        const badgeMap = {
          PRESENT: "success",
          INVITED: "info",
          EXCUSED: "warning",
          ABSENT: "danger"
        };
        return <StatusBadge status={badgeMap[val] || "neutral"} label={row.attendance_label} size="sm" />;
      }
    },
    {
      key: "actions",
      title: "Cập nhật điểm danh",
      width: "200px",
      align: "right",
      render: (_, row) => (
        <div style={{ display: "flex", justifyContent: "flex-end", gap: "4px" }}>
          <button
            type="button"
            className="btn btn-sm"
            onClick={() => onUpdateAttendance(row.id, "PRESENT")}
            title="Đánh dấu Có mặt"
            style={{
              backgroundColor: row.attendance_status === "PRESENT" ? "var(--color-success)" : "var(--color-bg-card)",
              color: row.attendance_status === "PRESENT" ? "#FFFFFF" : "var(--color-success)",
              border: "1px solid var(--color-success-border)",
              padding: "2px 8px"
            }}
          >
            <Check size={13} /> Có mặt
          </button>
          <button
            type="button"
            className="btn btn-sm"
            onClick={() => onUpdateAttendance(row.id, "EXCUSED")}
            title="Đánh dấu Vắng có lý do"
            style={{
              backgroundColor: row.attendance_status === "EXCUSED" ? "var(--color-warning)" : "var(--color-bg-card)",
              color: row.attendance_status === "EXCUSED" ? "#FFFFFF" : "var(--color-warning)",
              border: "1px solid var(--color-warning-border)",
              padding: "2px 8px"
            }}
          >
            Có phép
          </button>
          <button
            type="button"
            className="btn btn-sm"
            onClick={() => onUpdateAttendance(row.id, "ABSENT")}
            title="Đánh dấu Vắng mặt"
            style={{
              backgroundColor: row.attendance_status === "ABSENT" ? "var(--color-danger)" : "var(--color-bg-card)",
              color: row.attendance_status === "ABSENT" ? "#FFFFFF" : "var(--color-danger)",
              border: "1px solid var(--color-danger-border)",
              padding: "2px 8px"
            }}
          >
            <X size={13} />
          </button>
        </div>
      )
    }
  ];

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-4)" }}>
      {/* 1. Quorum Verification Banner */}
      <div
        style={{
          padding: "var(--space-4)",
          borderRadius: "var(--radius-lg)",
          backgroundColor: isQuorumMet ? "var(--color-success-bg)" : "var(--color-warning-bg)",
          border: `1px solid ${isQuorumMet ? "var(--color-success-border)" : "var(--color-warning-border)"}`,
          display: "flex",
          flexWrap: "wrap",
          alignItems: "center",
          justifyContent: "space-between",
          gap: "var(--space-3)"
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)" }}>
          <div
            style={{
              width: "36px",
              height: "36px",
              borderRadius: "50%",
              backgroundColor: isQuorumMet ? "var(--color-success)" : "var(--color-warning)",
              color: "#FFFFFF",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              fontWeight: "var(--font-weight-bold)"
            }}
          >
            {isQuorumMet ? "✓" : "!"}
          </div>

          <div>
            <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-bold)", color: isQuorumMet ? "var(--color-success-text)" : "var(--color-warning-text)" }}>
              {isQuorumMet
                ? "ĐỦ ĐIỀU KIỆN TIẾN HÀNH PHIÊN HỌP & BIỂU QUYẾT (QUORUM ĐẠT)"
                : "CHƯA ĐỦ ĐIỀU KIỆN TIẾN HÀNH PHIÊN HỌP (QUORUM DƯỚI 2/3)"}
            </div>
            <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-secondary)" }}>
              Thành viên HĐT có mặt: <strong>{presentOfficialCount}/{officialMembers.length} đại biểu</strong> ({quorumPercentage}% • Quy chế STU yêu cầu tối thiểu 66.7%). Khách mời không được tính vào quorum.
            </div>
          </div>
        </div>

        <button
          type="button"
          className="btn btn-primary btn-sm"
          onClick={() => setIsModalOpen(true)}
          style={{ gap: "4px" }}
        >
          <UserPlus size={14} />
          <span>Thêm Khách mời</span>
        </button>
      </div>

      {/* 2. Filter Bar by Type */}
      <div style={{ display: "flex", gap: "var(--space-2)" }}>
        <button
          type="button"
          className={`btn btn-sm ${filterType === "ALL" ? "btn-primary" : "btn-secondary"}`}
          onClick={() => setFilterType("ALL")}
        >
          Tất cả đại biểu ({attendees.length})
        </button>
        <button
          type="button"
          className={`btn btn-sm ${filterType === "OFFICIAL" ? "btn-primary" : "btn-secondary"}`}
          onClick={() => setFilterType("OFFICIAL")}
        >
          Thành viên HĐT chính thức ({officialMembers.length})
        </button>
        <button
          type="button"
          className={`btn btn-sm ${filterType === "GUEST" ? "btn-primary" : "btn-secondary"}`}
          onClick={() => setFilterType("GUEST")}
        >
          Khách mời chuyên môn ({guests.length})
        </button>
      </div>

      {/* 3. Attendees Table */}
      <DataTable
        columns={columns}
        data={filteredAttendees}
        keyField="id"
        emptyMessage="Chưa có đại biểu nào trong danh sách."
      />

      {/* Modal Thêm Khách mời */}
      <AttendeeModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSave={(guestData) => {
          onAddGuest(guestData);
          setIsModalOpen(false);
        }}
      />
    </div>
  );
}
