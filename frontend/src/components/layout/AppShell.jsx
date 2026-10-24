import React, { useState, useEffect } from "react";
import Sidebar from "./Sidebar";
import Header from "./Header";

/**
 * AppShell - Khung bao bọc tổng thể ứng dụng (Enterprise Layout Shell)
 */
export default function AppShell({ children }) {
  const [isSidebarCollapsed, setIsSidebarCollapsed] = useState(false);
  const [isMobile, setIsMobile] = useState(false);

  // Lắng nghe thay đổi kích thước màn hình để tự động thu gọn Sidebar trên tablet/mobile
  useEffect(() => {
    const handleResize = () => {
      const mobile = window.innerWidth < 768;
      const tablet = window.innerWidth >= 768 && window.innerWidth < 1200;
      setIsMobile(mobile);
      if (tablet) {
        setIsSidebarCollapsed(true);
      } else if (!mobile) {
        setIsSidebarCollapsed(false);
      }
    };

    handleResize();
    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  return (
    <div style={{ display: "flex", minHeight: "100vh", backgroundColor: "var(--color-bg-app)" }}>
      {/* Sidebar */}
      <Sidebar
        isCollapsed={isSidebarCollapsed}
        onToggle={() => setIsSidebarCollapsed(!isSidebarCollapsed)}
      />

      {/* Main Container */}
      <div style={{ flex: 1, display: "flex", flexDirection: "column", minWidth: 0, overflowX: "hidden" }}>
        {/* Header */}
        <Header
          isSidebarCollapsed={isSidebarCollapsed}
          onToggleSidebar={() => setIsSidebarCollapsed(!isSidebarCollapsed)}
        />

        {/* Content Body */}
        <main
          style={{
            flex: 1,
            padding: "var(--space-6)",
            maxWidth: "1440px",
            width: "100%",
            margin: "0 auto"
          }}
        >
          {children}
        </main>

        {/* Standard Academic Footer */}
        <footer
          style={{
            padding: "var(--space-4) var(--space-6)",
            borderTop: "1px solid var(--color-border)",
            backgroundColor: "var(--color-bg-card)",
            fontSize: "var(--font-size-xs)",
            color: "var(--color-text-muted)",
            textAlign: "center"
          }}
        >
          © 2026 Hội đồng trường Trường Đại học Công nghệ Sài Gòn (STU). Hệ thống Quản lý Phiên họp & Thực hiện Nghị quyết.
        </footer>
      </div>
    </div>
  );
}
