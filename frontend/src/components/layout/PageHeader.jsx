import React from "react";
import Breadcrumb from "./Breadcrumb";

/**
 * PageHeader - Tiêu đề trang, mô tả và cụm nút thao tác chính
 */
export default function PageHeader({
  title,
  description,
  breadcrumbItems,
  statusBadge,
  actions,
  className = ""
}) {
  return (
    <div
      className={className}
      style={{
        marginBottom: "var(--space-6)"
      }}
    >
      {breadcrumbItems && <Breadcrumb items={breadcrumbItems} />}

      <div
        style={{
          display: "flex",
          flexWrap: "wrap",
          alignItems: "flex-start",
          justifyContent: "space-between",
          gap: "var(--space-4)"
        }}
      >
        <div>
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-3)", flexWrap: "wrap" }}>
            <h1
              style={{
                fontSize: "var(--font-size-2xl)",
                fontWeight: "var(--font-weight-bold)",
                color: "var(--color-text-primary)",
                letterSpacing: "-0.01em"
              }}
            >
              {title}
            </h1>
            {statusBadge && <div>{statusBadge}</div>}
          </div>

          {description && (
            <p
              style={{
                fontSize: "var(--font-size-sm)",
                color: "var(--color-text-secondary)",
                marginTop: "var(--space-1)",
                maxWidth: "720px"
              }}
            >
              {description}
            </p>
          )}
        </div>

        {actions && (
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)", flexShrink: 0 }}>
            {actions}
          </div>
        )}
      </div>
    </div>
  );
}
