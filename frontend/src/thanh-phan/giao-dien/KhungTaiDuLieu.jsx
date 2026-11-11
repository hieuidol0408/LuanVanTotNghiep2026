import React from "react";

/**
 * LoadingSkeleton - Khung xương hiển thị trong khi chờ nạp dữ liệu
 * @param {string} type - "line" | "card" | "table" | "stat"
 * @param {number} count - Số lượng dòng/card mô phỏng
 */
export default function LoadingSkeleton({
  type = "line",
  count = 3,
  height = "20px",
  className = ""
}) {
  const shimmerStyle = {
    backgroundColor: "var(--color-bg-hover)",
    borderRadius: "var(--radius-md)",
    animation: "skeleton-shimmer 1.5s ease-in-out infinite",
    background: "linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%)",
    backgroundSize: "200% 100%"
  };

  if (type === "card") {
    return (
      <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fit, minmax(240px, 1fr))", gap: "var(--space-4)" }}>
        {Array.from({ length: count }).map((_, i) => (
          <div
            key={i}
            className="card"
            style={{
              height: "120px",
              ...shimmerStyle
            }}
          />
        ))}
      </div>
    );
  }

  if (type === "table") {
    return (
      <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-3)", width: "100%" }}>
        {/* Table Header skeleton */}
        <div style={{ height: "40px", ...shimmerStyle }} />
        {/* Table rows skeleton */}
        {Array.from({ length: count }).map((_, i) => (
          <div key={i} style={{ height: "52px", ...shimmerStyle }} />
        ))}
      </div>
    );
  }

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: "var(--space-2)", width: "100%" }} className={className}>
      {Array.from({ length: count }).map((_, i) => (
        <div key={i} style={{ height, width: i === count - 1 ? "70%" : "100%", ...shimmerStyle }} />
      ))}
    </div>
  );
}
