import React from "react";
import { FileText, FileSpreadsheet, File, Trash2, Eye, Download } from "lucide-react";

/**
 * FileList - Danh sách các tệp đã tải lên kèm định dạng, kích thước và thao tác
 */
export default function FileList({
  files = [],
  onDelete,
  onView,
  onDownload,
  canDelete = true,
  className = ""
}) {
  if (!files || files.length === 0) {
    return null;
  }

  const formatFileSize = (bytes) => {
    if (!bytes || bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
  };

  const getFileIcon = (fileName = "") => {
    const ext = fileName.split(".").pop().toLowerCase();
    if (ext === "pdf" || ext === "doc" || ext === "docx") {
      return <FileText size={20} style={{ color: "var(--color-danger)" }} />;
    }
    if (ext === "xls" || ext === "xlsx") {
      return <FileSpreadsheet size={20} style={{ color: "var(--color-success)" }} />;
    }
    return <File size={20} style={{ color: "var(--color-info)" }} />;
  };

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-2)" }} className={className}>
      {files.map((file, idx) => (
        <div
          key={file.id || idx}
          style={{
            display: "flex",
            alignItems: "center",
            justifyContent: "space-between",
            padding: "var(--space-2) var(--space-3)",
            backgroundColor: "var(--color-bg-card)",
            border: "1px solid var(--color-border)",
            borderRadius: "var(--radius-md)",
            fontSize: "var(--font-size-sm)"
          }}
        >
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)", overflow: "hidden" }}>
            {getFileIcon(file.name)}
            <div style={{ overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
              <div style={{ fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)", display: "flex", alignItems: "center", gap: "6px" }}>
                <span>{file.name}</span>
                {file.is_main && (
                  <span
                    style={{
                      fontSize: "10px",
                      padding: "1px 6px",
                      backgroundColor: "var(--color-primary-light)",
                      color: "var(--color-primary)",
                      borderRadius: "var(--radius-sm)",
                      fontWeight: "var(--font-weight-semibold)"
                    }}
                  >
                    Tờ trình chính
                  </span>
                )}
              </div>
              <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
                {formatFileSize(file.size)} {file.uploaded_at ? `• ${file.uploaded_at}` : ""}
              </div>
            </div>
          </div>

          <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
            {onView && (
              <button
                type="button"
                className="btn btn-secondary btn-sm"
                onClick={() => onView(file)}
                title="Xem tệp"
                style={{ padding: "4px 8px" }}
              >
                <Eye size={14} />
              </button>
            )}
            {onDownload && (
              <button
                type="button"
                className="btn btn-secondary btn-sm"
                onClick={() => onDownload(file)}
                title="Tải về"
                style={{ padding: "4px 8px" }}
              >
                <Download size={14} />
              </button>
            )}
            {canDelete && onDelete && (
              <button
                type="button"
                className="btn btn-sm"
                onClick={() => onDelete(file)}
                title="Xóa tệp"
                style={{
                  padding: "4px 8px",
                  color: "var(--color-danger)",
                  border: "1px solid var(--color-border)"
                }}
              >
                <Trash2 size={14} />
              </button>
            )}
          </div>
        </div>
      ))}
    </div>
  );
}
