import React from "react";

/**
 * StatusBadge - Huy hiệu hiển thị trạng thái chuẩn hóa
 * @param {string} status - Loại trạng thái: "success" | "warning" | "danger" | "info" | "neutral"
 * @param {string} label - Nội dung hiển thị
 * @param {string} size - Kích thước: "sm" | "md"
 * @param {boolean} showDot - Có hiển thị dấu chấm trạng thái hay không
 */
export default function StatusBadge({
  status = "neutral",
  label,
  size = "md",
  showDot = true,
  className = ""
}) {
  const statusStyles = {
    success: {
      bg: "var(--color-success-bg)",
      border: "var(--color-success-border)",
      text: "var(--color-success-text)",
      dot: "var(--color-success)"
    },
    warning: {
      bg: "var(--color-warning-bg)",
      border: "var(--color-warning-border)",
      text: "var(--color-warning-text)",
      dot: "var(--color-warning)"
    },
    danger: {
      bg: "var(--color-danger-bg)",
      border: "var(--color-danger-border)",
      text: "var(--color-danger-text)",
      dot: "var(--color-danger)"
    },
    info: {
      bg: "var(--color-info-bg)",
      border: "var(--color-info-border)",
      text: "var(--color-info-text)",
      dot: "var(--color-info)"
    },
    neutral: {
      bg: "var(--color-bg-hover)",
      border: "var(--color-border)",
      text: "var(--color-text-secondary)",
      dot: "var(--color-text-muted)"
    }
  };

  const current = statusStyles[status] || statusStyles.neutral;
  const isSmall = size === "sm";

  return (
    <span
      className={`inline-flex items-center gap-1 font-medium ${className}`}
      style={{
        display: "inline-flex",
        alignItems: "center",
        gap: "6px",
        padding: isSmall ? "2px 8px" : "4px 10px",
        fontSize: isSmall ? "var(--font-size-xs)" : "var(--font-size-sm)",
        lineHeight: 1.25,
        borderRadius: "var(--radius-full)",
        backgroundColor: current.bg,
        border: `1px solid ${current.border}`,
        color: current.text,
        whiteSpace: "nowrap"
      }}
    >
      {showDot && (
        <span
          style={{
            width: isSmall ? "6px" : "7px",
            height: isSmall ? "6px" : "7px",
            borderRadius: "50%",
            backgroundColor: current.dot,
            flexShrink: 0
          }}
          aria-hidden="true"
        />
      )}
      <span>{label}</span>
    </span>
  );
}
