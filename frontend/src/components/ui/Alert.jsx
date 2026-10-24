import React from "react";
import { CheckCircle2, AlertTriangle, AlertCircle, Info, X } from "lucide-react";

/**
 * Alert - Thông báo cảnh báo tĩnh / Banner trong trang
 */
export default function Alert({
  type = "info", // "info" | "success" | "warning" | "danger"
  title,
  message,
  onClose,
  className = ""
}) {
  const iconMap = {
    info: Info,
    success: CheckCircle2,
    warning: AlertTriangle,
    danger: AlertCircle
  };

  const styleMap = {
    info: { bg: "var(--color-info-bg)", border: "var(--color-info-border)", text: "var(--color-info-text)", icon: "var(--color-info)" },
    success: { bg: "var(--color-success-bg)", border: "var(--color-success-border)", text: "var(--color-success-text)", icon: "var(--color-success)" },
    warning: { bg: "var(--color-warning-bg)", border: "var(--color-warning-border)", text: "var(--color-warning-text)", icon: "var(--color-warning)" },
    danger: { bg: "var(--color-danger-bg)", border: "var(--color-danger-border)", text: "var(--color-danger-text)", icon: "var(--color-danger)" }
  };

  const Icon = iconMap[type] || Info;
  const current = styleMap[type] || styleMap.info;

  return (
    <div
      role="alert"
      className={className}
      style={{
        display: "flex",
        alignItems: "flex-start",
        gap: "var(--space-3)",
        padding: "var(--space-3) var(--space-4)",
        backgroundColor: current.bg,
        border: `1px solid ${current.border}`,
        borderRadius: "var(--radius-md)",
        color: current.text,
        fontSize: "var(--font-size-sm)",
        marginBottom: "var(--space-4)"
      }}
    >
      <Icon size={18} style={{ color: current.icon, flexShrink: 0, marginTop: "2px" }} />
      <div style={{ flex: 1 }}>
        {title && <div style={{ fontWeight: "var(--font-weight-semibold)", marginBottom: "2px" }}>{title}</div>}
        <div style={{ lineHeight: 1.5 }}>{message}</div>
      </div>
      {onClose && (
        <button
          type="button"
          onClick={onClose}
          style={{
            background: "none",
            border: "none",
            color: "currentColor",
            opacity: 0.7,
            cursor: "pointer",
            padding: "2px",
            display: "flex",
            alignItems: "center"
          }}
          aria-label="Đóng thông báo"
        >
          <X size={15} />
        </button>
      )}
    </div>
  );
}
