import React from "react";
import { BrowserRouter } from "react-router-dom";
import AppRoutes from "./dinh-tuyen/DinhTuyenUngDung";
import { ToastProvider } from "./thanh-phan/giao-dien/ThongBaoNhanh";
import { AuthProvider } from "./tinh-nang/xac-thuc/ngu-canh/NguCanhXacThuc";
import { ThemeProvider } from "./ngu-canh/NguCanhGiaoDien";

export default function App() {
  return (
    <BrowserRouter>
      <ThemeProvider>
        <AuthProvider>
          <ToastProvider>
            <AppRoutes />
          </ToastProvider>
        </AuthProvider>
      </ThemeProvider>
    </BrowserRouter>
  );
}
