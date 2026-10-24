import React from "react";
import { useNavigate } from "react-router-dom";
import { FileText, ArrowRight, Eye } from "lucide-react";
import DataTable from "../../../components/ui/DataTable";
import StatusBadge from "../../../components/ui/StatusBadge";
import { PROTOTYPE_PENDING_DOSSIERS } from "../../../mock/prototypeData";

/**
 * PendingDossiersSection - Danh sách các hồ sơ trình đang chờ thẩm tra hoặc cần bổ sung
 */
export default function PendingDossiersSection({ dossiers: liveDossiers }) {
  const navigate = useNavigate();

  const displayDossiers = (liveDossiers && liveDossiers.length > 0)
    ? liveDossiers
    : PROTOTYPE_PENDING_DOSSIERS;

  const columns = [
    {
      key: "id",
      title: "Mã hồ sơ",
      width: "120px",
      render: (val, row) => (
        <span
          style={{ fontWeight: "var(--font-weight-semibold)", color: "var(--color-primary)", cursor: "pointer" }}
          onClick={() => navigate("/dossiers")}
        >
          {val}
        </span>
      )
    },
    {
      key: "title",
      title: "Trích yếu nội dung đề xuất",
      render: (val, row) => (
        <div>
          <div style={{ fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)", lineHeight: 1.4 }}>
            {val}
          </div>
          <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginTop: "2px" }}>
            Đơn vị: {row.submitter_dept || row.department || "Khoa/Phòng chức năng"} {row.field ? `• Lĩnh vực: ${row.field}` : ""}
          </div>
        </div>
      )
    },
    {
      key: "submitted_date",
      title: "Ngày nộp",
      width: "110px",
      align: "center",
      render: (val) => val ? new Date(val).toLocaleDateString("vi-VN") : "Hôm nay"
    },
    {
      key: "status",
      title: "Trạng thái",
      width: "150px",
      render: (val, row) => (
        <StatusBadge
          status={val === "APPROVED" ? "ACTIVE" : val === "REJECTED" ? "REJECTED" : "WAITING"}
          text={val === "APPROVED" ? "Đã duyệt" : val === "REJECTED" ? "Bác bỏ" : (row.status_label || "Đang thẩm tra")}
          size="sm"
        />
      )
    },
    {
      key: "actions",
      title: "Thao tác",
      width: "110px",
      align: "right",
      render: (_, row) => (
        <button
          type="button"
          className="btn btn-secondary btn-sm"
          onClick={() => navigate("/dossiers")}
          style={{ fontSize: "var(--font-size-xs)", padding: "3px 8px" }}
          title="Xem chi tiết hồ sơ"
        >
          <Eye size={13} />
          <span>Thẩm tra</span>
        </button>
      )
    }
  ];

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
              backgroundColor: "var(--color-warning-bg)",
              color: "var(--color-warning)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center"
            }}
          >
            <FileText size={16} />
          </div>
          <div>
            <h2 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)" }}>
              Hồ sơ trình chờ xử lý
            </h2>
            <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
              Các tờ trình đang trong quy trình thẩm tra hoặc yêu cầu bổ sung tài liệu
            </div>
          </div>
        </div>

        <button
          type="button"
          onClick={() => navigate("/dossiers")}
          className="btn btn-secondary btn-sm"
          style={{ gap: "4px" }}
        >
          <span>Xem tất cả ({displayDossiers.length})</span>
          <ArrowRight size={13} />
        </button>
      </div>

      <DataTable
        columns={columns}
        data={displayDossiers}
        keyField="id"
        emptyMessage="Hiện tại không có hồ sơ nào đang chờ xử lý."
      />
    </div>
  );
}
