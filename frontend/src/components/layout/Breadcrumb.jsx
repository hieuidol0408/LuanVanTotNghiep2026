import React from "react";
import { Link } from "react-router-dom";
import { ChevronRight, Home } from "lucide-react";

/**
 * Breadcrumb - Điều hướng phân cấp đường dẫn trang
 */
export default function Breadcrumb({ items = [], className = "" }) {
  if (!items || items.length === 0) return null;

  return (
    <nav aria-label="Breadcrumb" className={className} style={{ marginBottom: "var(--space-3)" }}>
      <ol
        style={{
          display: "flex",
          alignItems: "center",
          flexWrap: "wrap",
          listStyle: "none",
          gap: "var(--space-2)",
          fontSize: "var(--font-size-xs)",
          color: "var(--color-text-muted)"
        }}
      >
        <li style={{ display: "flex", alignItems: "center" }}>
          <Link
            to="/"
            style={{
              display: "flex",
              alignItems: "center",
              gap: "4px",
              color: "var(--color-text-secondary)",
              textDecoration: "none"
            }}
          >
            <Home size={13} />
            <span>Trang chủ</span>
          </Link>
        </li>

        {items.map((item, index) => {
          const isLast = index === items.length - 1;

          return (
            <li key={index} style={{ display: "flex", alignItems: "center", gap: "var(--space-2)" }}>
              <ChevronRight size={12} style={{ color: "var(--color-text-muted)" }} aria-hidden="true" />
              {isLast || !item.path ? (
                <span
                  style={{
                    color: "var(--color-text-primary)",
                    fontWeight: "var(--font-weight-medium)"
                  }}
                  aria-current={isLast ? "page" : undefined}
                >
                  {item.label}
                </span>
              ) : (
                <Link
                  to={item.path}
                  style={{
                    color: "var(--color-text-secondary)",
                    textDecoration: "none"
                  }}
                >
                  {item.label}
                </Link>
              )}
            </li>
          );
        })}
      </ol>
    </nav>
  );
}
