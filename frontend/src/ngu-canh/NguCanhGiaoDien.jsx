import React, { createContext, useContext, useState, useEffect } from "react";

const ThemeContext = createContext();

export function ThemeProvider({ children }) {
  // Đọc chế độ đã lưu từ LocalStorage, mặc định là "light" (giao diện sáng)
  const [theme, setTheme] = useState(() => {
    return localStorage.getItem("stu_theme") || "light";
  });

  // Áp dụng thuộc tính data-theme lên thẻ <html> để kích hoạt biến CSS tương ứng
  useEffect(() => {
    document.documentElement.setAttribute("data-theme", theme);
    localStorage.setItem("stu_theme", theme);
  }, [theme]);

  const toggleTheme = () => {
    setTheme((prev) => (prev === "dark" ? "light" : "dark"));
  };

  return (
    <ThemeContext.Provider value={{ theme, toggleTheme, isDark: theme === "dark" }}>
      {children}
    </ThemeContext.Provider>
  );
}

export function useTheme() {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error("useTheme must be used within a ThemeProvider");
  }
  return context;
}
