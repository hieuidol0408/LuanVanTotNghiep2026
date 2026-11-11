import React, { createContext, useContext, useState, useCallback } from "react";
import { CheckCircle2, AlertTriangle, AlertCircle, Info, X } from "lucide-react";

const ToastContext = createContext(null);

export function ToastProvider({ children }) {
  const [toasts, setToasts] = useState([]);

  const addToast = useCallback((message, type = "info", duration = 3500) => {
    const id = Date.now() + Math.random().toString(36).substring(2, 9);
    setToasts((prev) => [...prev, { id, message, type }]);

    if (duration > 0) {
      setTimeout(() => {
        setToasts((prev) => prev.filter((t) => t.id !== id));
      }, duration);
    }
  }, []);

  const removeToast = useCallback((id) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  const toast = {
    success: (msg, dur) => addToast(msg, "success", dur),
    error: (msg, dur) => addToast(msg, "danger", dur),
    warning: (msg, dur) => addToast(msg, "warning", dur),
    info: (msg, dur) => addToast(msg, "info", dur)
  };

  const iconMap = {
    success: CheckCircle2,
    warning: AlertTriangle,
    danger: AlertCircle,
    info: Info
  };

  const styleMap = {
    success: { bg: "var(--color-success-bg)", border: "var(--color-success-border)", text: "var(--color-success-text)", icon: "var(--color-success)" },
    warning: { bg: "var(--color-warning-bg)", border: "var(--color-warning-border)", text: "var(--color-warning-text)", icon: "var(--color-warning)" },
    danger: { bg: "var(--color-danger-bg)", border: "var(--color-danger-border)", text: "var(--color-danger-text)", icon: "var(--color-danger)" },
    info: { bg: "var(--color-info-bg)", border: "var(--color-info-border)", text: "var(--color-info-text)", icon: "var(--color-info)" }
  };

  return (
    <ToastContext.Provider value={toast}>
      {children}
      {/* Toast Container */}
      <div
        style={{
          position: "fixed",
          top: "20px",
          right: "20px",
          zIndex: 9999,
          display: "flex",
          flexDirection: "column",
          gap: "var(--space-2)",
          maxWidth: "380px",
          pointerEvents: "none"
        }}
        aria-live="polite"
      >
        {toasts.map((t) => {
          const Icon = iconMap[t.type] || Info;
          const current = styleMap[t.type] || styleMap.info;

          return (
            <div
              key={t.id}
              style={{
                pointerEvents: "auto",
                display: "flex",
                alignItems: "center",
                gap: "var(--space-3)",
                padding: "var(--space-3) var(--space-4)",
                backgroundColor: "var(--color-bg-card)",
                border: `1px solid ${current.border}`,
                borderLeft: `4px solid ${current.icon}`,
                borderRadius: "var(--radius-md)",
                boxShadow: "var(--shadow-lg)",
                color: "var(--color-text-primary)",
                fontSize: "var(--font-size-sm)",
                animation: "slideIn 0.2s ease-out"
              }}
            >
              <Icon size={18} style={{ color: current.icon, flexShrink: 0 }} />
              <span style={{ flex: 1 }}>{t.message}</span>
              <button
                type="button"
                onClick={() => removeToast(t.id)}
                style={{
                  background: "none",
                  border: "none",
                  color: "var(--color-text-muted)",
                  cursor: "pointer",
                  padding: "2px",
                  display: "flex"
                }}
                aria-label="Đóng thông báo"
              >
                <X size={14} />
              </button>
            </div>
          );
        })}
      </div>
    </ToastContext.Provider>
  );
}

export function useToast() {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error("useToast must be used within a ToastProvider");
  }
  return context;
}
