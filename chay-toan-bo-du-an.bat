@echo off
title Khoi Dong He Thong Hoi Dong Truong STU
color 0A

set "PROJECT_DIR=%~dp0"
if not exist "%PROJECT_DIR%backend" set "PROJECT_DIR=D:\WAMP\www\LVTN\"

echo ========================================================
echo   KHOI DONG HE THONG QUAN LY HOI DONG TRUONG STU
echo ========================================================
echo.
echo [1/3] Dang khoi dong Backend Laravel (Port 8000)...
start "Backend Laravel (STU)" cmd /k "cd /d %PROJECT_DIR%backend && if exist D:\WAMP\bin\php\php8.3.14\php.exe (D:\WAMP\bin\php\php8.3.14\php.exe artisan serve --host=127.0.0.1 --port=8000) else (php artisan serve --host=127.0.0.1 --port=8000)"

echo [2/3] Dang khoi dong Frontend React (Port 5173)...
timeout /t 2 /nobreak >nul
start "Frontend React (STU)" cmd /k "cd /d %PROJECT_DIR%frontend && npm run dev -- --host 127.0.0.1 --port 5173"

echo [3/3] Dang tu dong mo trinh duyet...
timeout /t 3 /nobreak >nul
start http://127.0.0.1:8000/

echo.
echo ========================================================
echo   DA KHOI DONG THANH CONG!
echo   - Backend:  http://127.0.0.1:8000
echo   - Frontend: http://127.0.0.1:5173
echo ========================================================
echo   (Cua so nay se tu dong dong sau 3 giay...)
timeout /t 3 /nobreak >nul
exit
