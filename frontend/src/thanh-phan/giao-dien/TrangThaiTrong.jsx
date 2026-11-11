import React from "react";
import { FolderOpen } from "lucide-react";

/**
 * EmptyState - Hiển thị khi danh sách không có dữ liệu
 */
export default function EmptyState({
  icon: Icon = FolderOpen,
  title = "Không có dữ liệu",
  description = "Hiện tại chưa có bản ghi nào phù hợp với điều kiện tìm kiếm.",
  actionButton,
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
        textAlign: "center",
        color: "var(--color-text-muted)"
      }}
    >
      <div
        style={{
          width: "56px",
          height: "56px",
          borderRadius: "50%",
          backgroundColor: "var(--color-bg-hover)",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          marginBottom: "var(--space-3)",
          color: "var(--color-text-muted)"
        }}
      >
        <Icon size={28} />
      </div>

      <h3
        style={{
          fontSize: "var(--font-size-base)",
          fontWeight: "var(--font-weight-semibold)",
          color: "var(--color-text-primary)",
          marginBottom: "var(--space-1)"
        }}
      >
        {title}
      </h3>

      <p
        style={{
          fontSize: "var(--font-size-sm)",
          color: "var(--color-text-secondary)",
          maxWidth: "400px",
          marginBottom: actionButton ? "var(--space-4)" : 0
        }}
      >
        {description}
      </p>

      {actionButton && <div>{actionButton}</div>}
    </div>
  );
}
