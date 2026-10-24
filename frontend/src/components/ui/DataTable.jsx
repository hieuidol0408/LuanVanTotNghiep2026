import React from "react";
import { ArrowUpDown, ArrowUp, ArrowDown } from "lucide-react";
import LoadingSkeleton from "./LoadingSkeleton";
import EmptyState from "./EmptyState";
import ErrorState from "./ErrorState";

/**
 * DataTable - Bảng dữ liệu chuẩn Enterprise
 */
export default function DataTable({
  columns = [],
  data = [],
  keyField = "id",
  isLoading = false,
  isError = false,
  onRetry,
  emptyMessage,
  selectable = false,
  selectedRowKeys = [],
  onSelectRowKeys,
  sortConfig = { key: null, direction: "asc" },
  onSort,
  className = ""
}) {
  if (isLoading) {
    return (
      <div style={{ padding: "var(--space-4)" }}>
        <LoadingSkeleton type="table" count={5} />
      </div>
    );
  }

  if (isError) {
    return <ErrorState onRetry={onRetry} />;
  }

  if (!data || data.length === 0) {
    return <EmptyState description={emptyMessage} />;
  }

  const handleSelectAll = (e) => {
    if (e.target.checked) {
      const allKeys = data.map((row) => row[keyField]);
      onSelectRowKeys?.(allKeys);
    } else {
      onSelectRowKeys?.([]);
    }
  };

  const handleSelectRow = (key) => {
    if (selectedRowKeys.includes(key)) {
      onSelectRowKeys?.(selectedRowKeys.filter((k) => k !== key));
    } else {
      onSelectRowKeys?.([...selectedRowKeys, key]);
    }
  };

  const isAllSelected = data.length > 0 && selectedRowKeys.length === data.length;

  return (
    <div
      style={{
        width: "100%",
        overflowX: "auto",
        border: "1px solid var(--color-border)",
        borderRadius: "var(--radius-lg)",
        backgroundColor: "var(--color-bg-card)"
      }}
      className={className}
    >
      <table
        style={{
          width: "100%",
          borderCollapse: "collapse",
          fontSize: "var(--font-size-sm)",
          textAlign: "left"
        }}
      >
        <thead>
          <tr
            style={{
              backgroundColor: "var(--color-bg-hover)",
              borderBottom: "1px solid var(--color-border)"
            }}
          >
            {selectable && (
              <th style={{ width: "48px", padding: "var(--space-3) var(--space-4)", textAlign: "center" }}>
                <input
                  type="checkbox"
                  checked={isAllSelected}
                  onChange={handleSelectAll}
                  aria-label="Chọn tất cả các dòng"
                  style={{ width: "16px", height: "16px", cursor: "pointer", accentColor: "var(--color-primary)" }}
                />
              </th>
            )}

            {columns.map((col) => {
              const isSorted = sortConfig?.key === col.key;
              return (
                <th
                  key={col.key}
                  style={{
                    padding: "var(--space-3) var(--space-4)",
                    fontWeight: "var(--font-weight-semibold)",
                    color: "var(--color-text-secondary)",
                    width: col.width,
                    textAlign: col.align || "left",
                    whiteSpace: "nowrap"
                  }}
                >
                  {col.sortable ? (
                    <button
                      type="button"
                      onClick={() => onSort?.(col.key)}
                      style={{
                        background: "none",
                        border: "none",
                        font: "inherit",
                        fontWeight: "inherit",
                        color: isSorted ? "var(--color-primary)" : "inherit",
                        cursor: "pointer",
                        display: "inline-flex",
                        alignItems: "center",
                        gap: "6px"
                      }}
                    >
                      <span>{col.title}</span>
                      {isSorted ? (
                        sortConfig.direction === "asc" ? <ArrowUp size={14} /> : <ArrowDown size={14} />
                      ) : (
                        <ArrowUpDown size={13} style={{ color: "var(--color-text-muted)" }} />
                      )}
                    </button>
                  ) : (
                    col.title
                  )}
                </th>
              );
            })}
          </tr>
        </thead>

        <tbody>
          {data.map((row, index) => {
            const rowKey = row[keyField];
            const isSelected = selectedRowKeys.includes(rowKey);

            return (
              <tr
                key={rowKey}
                style={{
                  borderBottom: index < data.length - 1 ? "1px solid var(--color-border-subtle)" : "none",
                  backgroundColor: isSelected ? "var(--color-primary-light)" : index % 2 === 1 ? "#FAFBFD" : "var(--color-bg-card)",
                  transition: "background-color var(--transition-fast)"
                }}
                onMouseEnter={(e) => {
                  if (!isSelected) e.currentTarget.style.backgroundColor = "var(--color-bg-hover)";
                }}
                onMouseLeave={(e) => {
                  if (!isSelected) e.currentTarget.style.backgroundColor = index % 2 === 1 ? "#FAFBFD" : "var(--color-bg-card)";
                }}
              >
                {selectable && (
                  <td style={{ padding: "var(--space-3) var(--space-4)", textAlign: "center" }}>
                    <input
                      type="checkbox"
                      checked={isSelected}
                      onChange={() => handleSelectRow(rowKey)}
                      aria-label={`Chọn bản ghi ${rowKey}`}
                      style={{ width: "16px", height: "16px", cursor: "pointer", accentColor: "var(--color-primary)" }}
                    />
                  </td>
                )}

                {columns.map((col) => (
                  <td
                    key={col.key}
                    style={{
                      padding: "var(--space-3) var(--space-4)",
                      color: "var(--color-text-primary)",
                      textAlign: col.align || "left",
                      verticalAlign: "middle"
                    }}
                  >
                    {col.render ? col.render(row[col.key], row, index) : row[col.key]}
                  </td>
                ))}
              </tr>
            );
          })}
        </tbody>
      </table>
    </div>
  );
}
