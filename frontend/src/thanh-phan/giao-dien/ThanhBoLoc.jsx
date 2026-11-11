import React from "react";
import { Filter, RotateCcw } from "lucide-react";

/**
 * FilterBar - Thanh công cụ lọc dữ liệu đa tiêu chí
 * @param {React.ReactNode} searchComponent - Component SearchInput
 * @param {React.ReactNode} children - Các bộ lọc dropdown con
 * @param {Function} onReset - Hàm xóa trắng các bộ lọc
 * @param {boolean} hasActiveFilters - Đang có bộ lọc nào được áp dụng không
 */
export default function FilterBar({
  searchComponent,
  children,
  onReset,
  hasActiveFilters = false,
  className = ""
}) {
  return (
    <div
      className={`card ${className}`}
      style={{
        display: "flex",
        flexWrap: "wrap",
        alignItems: "center",
        justifyContent: "space-between",
        gap: "var(--space-3)",
        padding: "var(--space-3) var(--space-4)",
        marginBottom: "var(--space-4)",
        backgroundColor: "var(--color-bg-card)",
        border: "1px solid var(--color-border)",
        borderRadius: "var(--radius-lg)"
      }}
    >
      <div style={{ display: "flex", flexWrap: "wrap", alignItems: "center", gap: "var(--space-3)", flex: 1 }}>
        {searchComponent}
        
        {children && (
          <div style={{ display: "flex", flexWrap: "wrap", alignItems: "center", gap: "var(--space-2)" }}>
            <span
              style={{
                display: "inline-flex",
                alignItems: "center",
                gap: "4px",
                fontSize: "var(--font-size-xs)",
                fontWeight: "var(--font-weight-medium)",
                color: "var(--color-text-muted)",
                marginRight: "4px"
              }}
            >
              <Filter size={14} />
              Bộ lọc:
            </span>
            {children}
          </div>
        )}
      </div>

      {hasActiveFilters && onReset && (
        <button
          type="button"
          className="btn btn-secondary btn-sm"
          onClick={onReset}
          title="Xóa toàn bộ bộ lọc và tìm kiếm"
          style={{ gap: "4px" }}
        >
          <RotateCcw size={13} />
          <span>Đặt lại</span>
        </button>
      )}
    </div>
  );
}
