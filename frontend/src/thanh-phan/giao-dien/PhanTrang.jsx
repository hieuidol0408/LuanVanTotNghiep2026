import React from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";

/**
 * Pagination - Thanh điều khiển phân trang chuẩn Enterprise
 */
export default function Pagination({
  currentPage = 1,
  totalPages = 1,
  totalItems = 0,
  itemsPerPage = 10,
  pageSizeOptions = [10, 20, 50],
  onPageChange,
  onItemsPerPageChange,
  className = ""
}) {
  const startItem = totalItems === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
  const endItem = Math.min(currentPage * itemsPerPage, totalItems);

  // Tính toán dải số trang cần hiển thị
  const getPageNumbers = () => {
    const pages = [];
    const maxVisiblePages = 5;

    if (totalPages <= maxVisiblePages) {
      for (let i = 1; i <= totalPages; i++) pages.push(i);
    } else {
      let start = Math.max(1, currentPage - 2);
      let end = Math.min(totalPages, start + maxVisiblePages - 1);
      if (end - start < maxVisiblePages - 1) {
        start = Math.max(1, end - maxVisiblePages + 1);
      }
      for (let i = start; i <= end; i++) pages.push(i);
    }
    return pages;
  };

  return (
    <div
      className={className}
      style={{
        display: "flex",
        flexWrap: "wrap",
        alignItems: "center",
        justifyContent: "space-between",
        gap: "var(--space-3)",
        padding: "var(--space-3) 0",
        borderTop: "1px solid var(--color-border-subtle)"
      }}
    >
      {/* Thông tin số lượng bản ghi */}
      <div style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-secondary)" }}>
        Hiển thị <span style={{ fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)" }}>{startItem}</span> -{" "}
        <span style={{ fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)" }}>{endItem}</span> trong tổng số{" "}
        <span style={{ fontWeight: "var(--font-weight-medium)", color: "var(--color-text-primary)" }}>{totalItems}</span> bản ghi
      </div>

      <div style={{ display: "flex", alignItems: "center", gap: "var(--space-4)" }}>
        {/* Lựa chọn số dòng mỗi trang */}
        {onItemsPerPageChange && (
          <div style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
            <label htmlFor="items-per-page" style={{ fontSize: "var(--font-size-xs)", color: "var(--color-text-muted)" }}>
              Số dòng:
            </label>
            <select
              id="items-per-page"
              value={itemsPerPage}
              onChange={(e) => onItemsPerPageChange(Number(e.target.value))}
              className="form-select"
              style={{
                width: "auto",
                padding: "2px 24px 2px 8px",
                fontSize: "var(--font-size-xs)",
                height: "30px"
              }}
            >
              {pageSizeOptions.map((opt) => (
                <option key={opt} value={opt}>
                  {opt} / trang
                </option>
              ))}
            </select>
          </div>
        )}

        {/* Nút lùi, các trang và tiến */}
        <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
          <button
            type="button"
            className="btn btn-secondary btn-sm"
            onClick={() => onPageChange(currentPage - 1)}
            disabled={currentPage <= 1}
            aria-label="Trang trước"
            style={{ width: "32px", height: "32px", padding: 0 }}
          >
            <ChevronLeft size={16} />
          </button>

          {getPageNumbers().map((pageNum) => {
            const isActive = pageNum === currentPage;
            return (
              <button
                key={pageNum}
                type="button"
                onClick={() => onPageChange(pageNum)}
                style={{
                  width: "32px",
                  height: "32px",
                  padding: 0,
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  fontSize: "var(--font-size-xs)",
                  fontWeight: isActive ? "var(--font-weight-semibold)" : "var(--font-weight-normal)",
                  borderRadius: "var(--radius-md)",
                  border: isActive ? "1px solid var(--color-primary)" : "1px solid var(--color-border)",
                  backgroundColor: isActive ? "var(--color-primary)" : "var(--color-bg-card)",
                  color: isActive ? "var(--color-primary-text)" : "var(--color-text-primary)",
                  cursor: isActive ? "default" : "pointer"
                }}
                aria-current={isActive ? "page" : undefined}
              >
                {pageNum}
              </button>
            );
          })}

          <button
            type="button"
            className="btn btn-secondary btn-sm"
            onClick={() => onPageChange(currentPage + 1)}
            disabled={currentPage >= totalPages}
            aria-label="Trang tiếp theo"
            style={{ width: "32px", height: "32px", padding: 0 }}
          >
            <ChevronRight size={16} />
          </button>
        </div>
      </div>
    </div>
  );
}
