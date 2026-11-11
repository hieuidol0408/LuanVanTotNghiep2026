import React, { useEffect } from "react";
import { AlertTriangle, Info, X } from "lucide-react";

/**
 * ConfirmDialog - Hộp thoại xác nhận thao tác quan trọng hoặc nguy hiểm
 */
export default function ConfirmDialog({
  isOpen,
  title = "Xác nhận thao tác",
  description,
  confirmLabel = "Xác nhận",
  cancelLabel = "Hủy bỏ",
  variant = "danger", // "danger" | "primary" | "warning"
  isLoading = false,
  onConfirm,
  onCancel
}) {
  useEffect(() => {
    const handleKeyDown = (e) => {
      if (e.key === "Escape" && isOpen && !isLoading) {
        onCancel?.();
      }
    };
    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, [isOpen, isLoading, onCancel]);

  if (!isOpen) return null;

  const isDanger = variant === "danger";

  return (
    <div
      style={{
        position: "fixed",
        inset: 0,
        zIndex: 1000,
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        padding: "var(--space-4)",
        backgroundColor: "rgba(15, 23, 42, 0.5)",
        backdropFilter: "blur(2px)"
      }}
      role="dialog"
      aria-modal="true"
      aria-labelledby="confirm-dialog-title"
      aria-describedby="confirm-dialog-desc"
    >
      <div
        className="card"
        style={{
          width: "100%",
          maxWidth: "460px",
          padding: "var(--space-6)",
          backgroundColor: "var(--color-bg-card)",
          borderRadius: "var(--radius-lg)",
          boxShadow: "var(--shadow-modal)",
          position: "relative"
        }}
      >
        <button
          type="button"
          onClick={onCancel}
          disabled={isLoading}
          style={{
            position: "absolute",
            top: "16px",
            right: "16px",
            background: "none",
            border: "none",
            color: "var(--color-text-muted)",
            cursor: "pointer",
            padding: "4px"
          }}
          aria-label="Đóng hộp thoại"
        >
          <X size={18} />
        </button>

        <div style={{ display: "flex", gap: "var(--space-4)", alignItems: "flex-start" }}>
          <div
            style={{
              width: "40px",
              height: "40px",
              borderRadius: "50%",
              backgroundColor: isDanger ? "var(--color-danger-bg)" : "var(--color-info-bg)",
              color: isDanger ? "var(--color-danger)" : "var(--color-info)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              flexShrink: 0
            }}
          >
            {isDanger ? <AlertTriangle size={20} /> : <Info size={20} />}
          </div>

          <div style={{ flex: 1 }}>
            <h3
              id="confirm-dialog-title"
              style={{
                fontSize: "var(--font-size-base)",
                fontWeight: "var(--font-weight-semibold)",
                color: "var(--color-text-primary)",
                marginBottom: "var(--space-2)"
              }}
            >
              {title}
            </h3>
            <p
              id="confirm-dialog-desc"
              style={{
                fontSize: "var(--font-size-sm)",
                color: "var(--color-text-secondary)",
                lineHeight: var_to_string("--line-height-normal"),
                marginBottom: "var(--space-6)"
              }}
            >
              {description}
            </p>

            <div style={{ display: "flex", justifyContent: "flex-end", gap: "var(--space-3)" }}>
              <button
                type="button"
                className="btn btn-secondary"
                onClick={onCancel}
                disabled={isLoading}
              >
                {cancelLabel}
              </button>

              <button
                type="button"
                className={`btn ${isDanger ? "btn-danger" : "btn-primary"}`}
                onClick={onConfirm}
                disabled={isLoading}
              >
                {isLoading ? "Đang xử lý..." : confirmLabel}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function var_to_string(v) {
  return "1.5";
}
