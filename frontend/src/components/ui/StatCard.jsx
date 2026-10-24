import React from "react";
import * as Icons from "lucide-react";

/**
 * StatCard - Thẻ thống kê chỉ số nghiệp vụ
 * @param {string} title - Tiêu đề chỉ số
 * @param {string|number} value - Giá trị số lượng / tỷ lệ
 * @param {string} description - Mô tả bổ sung
 * @param {string} iconName - Tên Lucide icon (ví dụ: "Calendar", "FileText")
 * @param {string} statusColor - "info" | "success" | "warning" | "danger"
 * @param {string} trend - Đoạn text biến động (ví dụ: "+15% so với tháng trước")
 * @param {string} trendType - "positive" | "negative" | "neutral"
 */
export default function StatCard({
  title,
  value,
  description,
  iconName = "BarChart3",
  statusColor = "info",
  trend,
  trendType = "neutral",
  className = ""
}) {
  const IconComponent = Icons[iconName] || Icons.BarChart3;

  const colorMap = {
    info: { bg: "var(--color-info-bg)", text: "var(--color-info)", border: "var(--color-info-border)" },
    success: { bg: "var(--color-success-bg)", text: "var(--color-success)", border: "var(--color-success-border)" },
    warning: { bg: "var(--color-warning-bg)", text: "var(--color-warning)", border: "var(--color-warning-border)" },
    danger: { bg: "var(--color-danger-bg)", text: "var(--color-danger)", border: "var(--color-danger-border)" }
  };

  const currentTheme = colorMap[statusColor] || colorMap.info;

  const trendColorMap = {
    positive: "var(--color-success)",
    negative: "var(--color-danger)",
    neutral: "var(--color-text-muted)"
  };

  return (
    <div
      className={`card ${className}`}
      style={{
        display: "flex",
        flexDirection: "column",
        justifyContent: "space-between",
        height: "100%",
        padding: "var(--space-5)",
        backgroundColor: "var(--color-bg-card)",
        border: "1px solid var(--color-border)",
        borderRadius: "var(--radius-lg)",
        boxShadow: "var(--shadow-sm)"
      }}
    >
      <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between", gap: "var(--space-3)" }}>
        <div>
          <span
            style={{
              fontSize: "var(--font-size-sm)",
              fontWeight: "var(--font-weight-medium)",
              color: "var(--color-text-secondary)"
            }}
          >
            {title}
          </span>
          <div
            style={{
              fontSize: "1.875rem",
              fontWeight: "var(--font-weight-bold)",
              color: "var(--color-text-primary)",
              lineHeight: 1.2,
              marginTop: "var(--space-2)"
            }}
          >
            {value}
          </div>
        </div>

        <div
          style={{
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            width: "44px",
            height: "44px",
            borderRadius: "var(--radius-md)",
            backgroundColor: currentTheme.bg,
            color: currentTheme.text,
            border: `1px solid ${currentTheme.border}`,
            flexShrink: 0
          }}
          aria-hidden="true"
        >
          <IconComponent size={22} />
        </div>
      </div>

      <div style={{ marginTop: "var(--space-4)", paddingTop: "var(--space-3)", borderTop: "1px solid var(--color-border-subtle)" }}>
        {description && (
          <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)", marginBottom: "4px" }}>
            {description}
          </div>
        )}
        {trend && (
          <div
            style={{
              fontSize: "var(--font-size-xs)",
              fontWeight: "var(--font-weight-medium)",
              color: trendColorMap[trendType] || trendColorMap.neutral
            }}
          >
            {trend}
          </div>
        )}
      </div>
    </div>
  );
}
