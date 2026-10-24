import React from "react";
import { BrowserRouter } from "react-router-dom";
import AppShell from "./components/layout/AppShell";
import AppRoutes from "./routes/AppRoutes";
import { ToastProvider } from "./components/ui/Toast";

export default function App() {
  return (
    <BrowserRouter>
      <ToastProvider>
        <AppShell>
          <AppRoutes />
        </AppShell>
      </ToastProvider>
    </BrowserRouter>
  );
}
