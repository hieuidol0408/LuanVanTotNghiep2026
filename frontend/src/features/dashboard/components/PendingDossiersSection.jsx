import React from "react";
import { useNavigate } from "react-router-dom";
import { FileText, ArrowRight, Eye } from "lucide-react";
import DataTable from "../../../components/ui/DataTable";
import StatusBadge from "../../../components/ui/StatusBadge";
import { PROTOTYPE_PENDING_DOSSIERS } from "../../../mock/prototypeData";

/**
 * PendingDossiersSection - Danh sách các hồ sơ trình đang chờ thẩm tra hoặc cần bổ sung
 */
export default function PendingDossiersSection() {
  const navigate = useNavigate();

  const columns = [
    {
      key: "id",
      title: "Mã hồ sơ",
      width: "120px",
      render: (val) => (
        <span style={{ fontWeight: "var(--font-weight-semibold)", color: "var(--color-primary)" }}>
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
            Đơn vị: {row.department} • Người nộp: {row.submitter} ({row.files_count} tệp đính kèm)
          </div>
        </div>
      )
    },
    {
      key: "submitted_date",
      title: "Ngày nộp",
      width: "110px",
      align: "center"
    },
    {
      key: "status",
      title: "Trạng thái",
      width: "150px",
      render: (val, row) => (
        <StatusBadge
          status={val === "in_review" ? "warning" : "danger"}
          label={row.status_label}
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
          <span>Xem tất cả ({PROTOTYPE_PENDING_DOSSIERS.length})</span>
          <ArrowRight size={13} />
        </button>
      </div>

      <DataTable
        columns={columns}
        data={PROTOTYPE_PENDING_DOSSIERS}
        keyField="id"
        emptyMessage="Hiện tại không có hồ sơ nào đang chờ xử lý."
      />
    </div>
  );
}
