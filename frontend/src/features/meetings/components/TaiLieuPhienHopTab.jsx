import React, { useState } from "react";
import { FileText, Download, Trash2, Eye, Upload, Shield, Globe } from "lucide-react";
import FileUpload from "../../../components/ui/FileUpload";
import DataTable from "../../../components/ui/DataTable";
import { useToast } from "../../../components/ui/Toast";

export default function MeetingDocumentsTab({
  documents = [],
  onUploadDocument,
  onDeleteDocument
}) {
  const toast = useToast();
  const [showUploadZone, setShowUploadZone] = useState(false);
  const [accessLevel, setAccessLevel] = useState("INTERNAL");

  const formatFileSize = (bytes) => {
    if (!bytes || bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
  };

  const handleFilesSelected = (files) => {
    files.forEach((f) => {
      onUploadDocument({
        file_name: f.name,
        file_size: f.size,
        access_level: accessLevel
      });
    });
    setShowUploadZone(false);
    toast.success(`Đã tải lên ${files.length} tài liệu thành công.`);
  };

  const columns = [
    {
      key: "file_name",
      title: "Tên văn bản / Tài liệu",
      render: (val, row) => (
        <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)" }}>
          <FileText size={20} style={{ color: "var(--color-primary)", flexShrink: 0 }} />
          <div>
            <div style={{ fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)" }}>
              {val}
            </div>
            <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
              Dung lượng: {formatFileSize(row.file_size)} • Tải lên: {row.uploaded_at} bởi {row.uploaded_by}
            </div>
          </div>
        </div>
      )
    },
    {
      key: "access_level",
      title: "Phạm vi tiếp cận",
      width: "180px",
      render: (val) =>
        val === "INTERNAL" ? (
          <span
            style={{
              display: "inline-flex",
              alignItems: "center",
              gap: "4px",
              fontSize: "11px",
              fontWeight: "var(--font-weight-semibold)",
              padding: "2px 8px",
              borderRadius: "var(--radius-sm)",
              backgroundColor: "var(--color-danger-bg)",
              color: "var(--color-danger)",
              border: "1px solid var(--color-danger-border)"
            }}
          >
            <Shield size={12} /> Nội bộ HĐT (Mật)
          </span>
        ) : (
          <span
            style={{
              display: "inline-flex",
              alignItems: "center",
              gap: "4px",
              fontSize: "11px",
              fontWeight: "var(--font-weight-medium)",
              padding: "2px 8px",
              borderRadius: "var(--radius-sm)",
              backgroundColor: "var(--color-info-bg)",
              color: "var(--color-info)",
              border: "1px solid var(--color-info-border)"
            }}
          >
            <Globe size={12} /> Công khai cho đại biểu
          </span>
        )
    },
    {
      key: "actions",
      title: "Thao tác",
      width: "140px",
      align: "right",
      render: (_, row) => (
        <div style={{ display: "flex", justifyContent: "flex-end", gap: "6px" }}>
          <button
            type="button"
            className="btn btn-secondary btn-sm"
            onClick={() => toast.info(`Xem trước tệp ${row.file_name} (Private Stream Viewer)`)}
            title="Xem tài liệu"
            style={{ padding: "4px 8px" }}
          >
            <Eye size={13} />
          </button>
          <button
            type="button"
            className="btn btn-secondary btn-sm"
            onClick={() => toast.success(`Đang tải tệp an toàn: ${row.file_name}`)}
            title="Tải về"
            style={{ padding: "4px 8px" }}
          >
            <Download size={13} />
          </button>
          <button
            type="button"
            className="btn btn-sm"
            onClick={() => onDeleteDocument(row.id)}
            title="Xóa tài liệu"
            style={{ color: "var(--color-danger)", border: "1px solid var(--color-border)", padding: "4px 8px" }}
          >
            <Trash2 size={13} />
          </button>
        </div>
      )
    }
  ];

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-4)" }}>
      {/* Header Bar */}
      <div
        style={{
          display: "flex",
          flexWrap: "wrap",
          alignItems: "center",
          justifyContent: "space-between",
          gap: "var(--space-3)",
          padding: "var(--space-4)",
          backgroundColor: "var(--color-bg-card)",
          border: "1px solid var(--color-border)",
          borderRadius: "var(--radius-lg)"
        }}
      >
        <div>
          <h3 style={{ fontSize: "var(--font-size-base)", fontWeight: "var(--font-weight-semibold)", color: "var(--color-text-primary)" }}>
            Kho Tài liệu Phiên họp ({documents.length} văn bản)
          </h3>
          <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
            Lưu trữ bảo mật tại thư mục private; phân định rạch ròi giữa tài liệu nội bộ HĐT và tài liệu cho khách mời
          </div>
        </div>

        <button
          type="button"
          className="btn btn-primary btn-sm"
          onClick={() => setShowUploadZone(!showUploadZone)}
          style={{ gap: "4px" }}
        >
          <Upload size={14} />
          <span>{showUploadZone ? "Đóng vùng tải lên" : "Tải lên tài liệu mới"}</span>
        </button>
      </div>

      {/* Upload Zone */}
      {showUploadZone && (
        <div className="card" style={{ padding: "var(--space-5)" }}>
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)", marginBottom: "var(--space-3)" }}>
            <span style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-medium)" }}>
              Chọn mức độ bảo mật tài liệu:
            </span>
            <label style={{ display: "inline-flex", alignItems: "center", gap: "4px", fontSize: "var(--font-size-xs)", cursor: "pointer" }}>
              <input
                type="radio"
                name="docAccess"
                value="INTERNAL"
                checked={accessLevel === "INTERNAL"}
                onChange={(e) => setAccessLevel(e.target.value)}
              />
              <strong>Nội bộ HĐT (Chỉ thành viên xem)</strong>
            </label>
            <label style={{ display: "inline-flex", alignItems: "center", gap: "4px", fontSize: "var(--font-size-xs)", cursor: "pointer" }}>
              <input
                type="radio"
                name="docAccess"
                value="PUBLIC_AGENDA"
                checked={accessLevel === "PUBLIC_AGENDA"}
                onChange={(e) => setAccessLevel(e.target.value)}
              />
              Công khai cho đại biểu tham dự
            </label>
          </div>

          <FileUpload onFilesSelected={handleFilesSelected} />
        </div>
      )}

      {/* Documents Table */}
      <DataTable
        columns={columns}
        data={documents}
        keyField="id"
        emptyMessage="Chưa có tài liệu nào được đính kèm cho phiên họp này."
      />
    </div>
  );
}
