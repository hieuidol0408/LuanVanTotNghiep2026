import React from "react";
import { Search, X } from "lucide-react";

/**
 * SearchInput - Ô tìm kiếm chuẩn hóa với icon và nút xóa nhanh
 */
export default function SearchInput({
  value,
  onChange,
  onClear,
  placeholder = "Tìm kiếm...",
  ariaLabel = "Tìm kiếm trong bảng dữ liệu",
  className = "",
  style = {}
}) {
  return (
    <div
      style={{
        position: "relative",
        display: "inline-flex",
        alignItems: "center",
        width: "100%",
        maxWidth: "320px",
        ...style
      }}
      className={className}
    >
      <Search
        size={16}
        style={{
          position: "absolute",
          left: "12px",
          color: "var(--color-text-muted)",
          pointerEvents: "none"
        }}
        aria-hidden="true"
      />
      <input
        type="text"
        className="form-input"
        style={{
          paddingLeft: "36px",
          paddingRight: value ? "32px" : "12px",
          height: "38px"
        }}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder={placeholder}
        aria-label={ariaLabel}
      />
      {value && (
        <button
          type="button"
          onClick={onClear}
          style={{
            position: "absolute",
            right: "8px",
            background: "none",
            border: "none",
            color: "var(--color-text-muted)",
            cursor: "pointer",
            padding: "4px",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            borderRadius: "var(--radius-sm)"
          }}
          aria-label="Xóa nội dung tìm kiếm"
          title="Xóa tìm kiếm"
        >
          <X size={14} />
        </button>
      )}
    </div>
  );
}
