import React from "react";
import { useNavigate } from "react-router-dom";
import StatCard from "../../../components/ui/StatCard";
import { PROTOTYPE_DASHBOARD_STATS } from "../../../mock/prototypeData";

/**
 * DashboardStats - Khối hiển thị 6 thẻ thống kê chỉ số cốt lõi kèm điều hướng
 */
export default function DashboardStats({ stats = PROTOTYPE_DASHBOARD_STATS }) {
  const navigate = useNavigate();

  return (
    <div
      style={{
        display: "grid",
        gridTemplateColumns: "repeat(auto-fit, minmax(200px, 1fr))",
        gap: "var(--space-4)",
        marginBottom: "var(--space-6)"
      }}
    >
      {stats.map((stat) => (
        <div
          key={stat.id}
          onClick={() => navigate(stat.path)}
          role="button"
          tabIndex={0}
          onKeyDown={(e) => {
            if (e.key === "Enter" || e.key === " ") {
              e.preventDefault();
              navigate(stat.path);
            }
          }}
          style={{
            cursor: "pointer",
            transition: "transform var(--transition-fast), box-shadow var(--transition-fast)",
            outline: "none"
          }}
          onMouseEnter={(e) => {
            e.currentTarget.style.transform = "translateY(-2px)";
            e.currentTarget.style.boxShadow = "var(--shadow-md)";
          }}
          onMouseLeave={(e) => {
            e.currentTarget.style.transform = "translateY(0)";
            e.currentTarget.style.boxShadow = "var(--shadow-sm)";
          }}
          title={`Bấm để xem danh sách chi tiết: ${stat.title}`}
        >
          <StatCard
            title={stat.title}
            value={stat.value}
            description={stat.description}
            trend={stat.trend}
            trendType={stat.trendType}
            statusColor={stat.statusColor}
            iconName={stat.iconName}
          />
        </div>
      ))}
    </div>
  );
}
