import React, { useRef, useState } from "react";
import { UploadCloud } from "lucide-react";

/**
 * FileUpload - Vùng tải tệp lên hỗ trợ kéo thả và chọn tệp
 */
export default function FileUpload({
  onFilesSelected,
  accept = ".pdf,.docx,.doc,.xlsx,.xls,.png,.jpg",
  maxSizeMB = 50,
  multiple = true,
  disabled = false,
  helperText = "Hỗ trợ tệp PDF, Word, Excel (tối đa 50MB/tệp)",
  className = ""
}) {
  const fileInputRef = useRef(null);
  const [isDragging, setIsDragging] = useState(false);

  const handleDragOver = (e) => {
    e.preventDefault();
    if (!disabled) setIsDragging(true);
  };

  const handleDragLeave = (e) => {
    e.preventDefault();
    setIsDragging(false);
  };

  const handleDrop = (e) => {
    e.preventDefault();
    setIsDragging(false);
    if (disabled) return;
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
      onFilesSelected?.(Array.from(e.dataTransfer.files));
    }
  };

  const handleFileChange = (e) => {
    if (e.target.files && e.target.files.length > 0) {
      onFilesSelected?.(Array.from(e.target.files));
      e.target.value = ""; // Reset sau khi chọn
    }
  };

  return (
    <div
      className={className}
      onDragOver={handleDragOver}
      onDragLeave={handleDragLeave}
      onDrop={handleDrop}
      onClick={() => !disabled && fileInputRef.current?.click()}
      style={{
        border: `2px dashed ${isDragging ? "var(--color-primary)" : "var(--color-border)"}`,
        backgroundColor: isDragging ? "var(--color-primary-light)" : "var(--color-bg-app)",
        borderRadius: "var(--radius-lg)",
        padding: "var(--space-6) var(--space-4)",
        textAlign: "center",
        cursor: disabled ? "not-allowed" : "pointer",
        transition: "all var(--transition-fast)",
        opacity: disabled ? 0.6 : 1
      }}
      role="button"
      tabIndex={disabled ? -1 : 0}
      onKeyDown={(e) => {
        if ((e.key === "Enter" || e.key === " ") && !disabled) {
          e.preventDefault();
          fileInputRef.current?.click();
        }
      }}
      aria-label="Tải lên tệp tài liệu"
    >
      <input
        ref={fileInputRef}
        type="file"
        accept={accept}
        multiple={multiple}
        onChange={handleFileChange}
        style={{ display: "none" }}
        disabled={disabled}
      />

      <div
        style={{
          width: "48px",
          height: "48px",
          borderRadius: "50%",
          backgroundColor: "var(--color-bg-card)",
          color: "var(--color-primary)",
          border: "1px solid var(--color-border)",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          margin: "0 auto var(--space-3)"
        }}
      >
        <UploadCloud size={24} />
      </div>

      <div style={{ fontSize: "var(--font-size-sm)", fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)", marginBottom: "4px" }}>
        <span style={{ color: "var(--color-primary)", textDecoration: "underline" }}>Nhấp để chọn tệp</span> hoặc kéo thả vào đây
      </div>

      <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
        {helperText} (Tối đa {maxSizeMB}MB)
      </div>
    </div>
  );
}
