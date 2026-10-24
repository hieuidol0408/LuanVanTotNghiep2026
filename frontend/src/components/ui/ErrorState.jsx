import React from "react";
import { AlertCircle, RotateCcw } from "lucide-react";

/**
 * ErrorState - Hiển thị khi xảy ra sự cố nạp dữ liệu hoặc lỗi kết nối
 */
export default function ErrorState({
  title = "Đã xảy ra lỗi khi tải dữ liệu",
  description = "Không thể kết nối đến máy chủ hoặc dữ liệu trả về không hợp lệ. Vui lòng thử lại.",
  onRetry,
  className = ""
}) {
  return (
    <div
      className={className}
      style={{
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
        padding: "var(--space-10) var(--space-4)",
        textAlign: "center"
      }}
    >
      <div
        style={{
          width: "56px",
          height: "56px",
          borderRadius: "50%",
          backgroundColor: "var(--color-danger-bg)",
          color: "var(--color-danger)",
          border: "1px solid var(--color-danger-border)",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          marginBottom: "var(--space-3)"
        }}
      >
        <AlertCircle size={28} />
      </div>

      <h3
        style={{
          fontSize: "var(--font-size-base)",
          fontWeight: "var(--font-weight-semibold)",
          color: "var(--color-danger)",
          marginBottom: "var(--space-1)"
        }}
      >
        {title}
      </h3>

      <p
        style={{
          fontSize: "var(--font-size-sm)",
          color: "var(--color-text-secondary)",
          maxWidth: "420px",
          marginBottom: onRetry ? "var(--space-4)" : 0
        }}
      >
        {description}
      </p>

      {onRetry && (
        <button type="button" className="btn btn-secondary btn-sm" onClick={onRetry} style={{ gap: "6px" }}>
          <RotateCcw size={14} />
          <span>Thử lại</span>
        </button>
      )}
    </div>
  );
}
