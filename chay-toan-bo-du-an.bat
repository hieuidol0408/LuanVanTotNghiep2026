@echo off
title Khoi Dong He Thong Hoi Dong Truong STU
echo ========================================================
echo   KHOI DONG HE THONG HOI DONG TRUONG STU
echo   Backend:  http://127.0.0.1:8000
echo   Frontend: http://localhost:5173
echo ========================================================
echo.

start "Backend Laravel" "%~dp0chay-backend.bat"
timeout /t 2 /nobreak >nul
start "Frontend React" "%~dp0chay-frontend.bat"

echo Da khoi dong Backend va Frontend trong 2 cua so rieng biet!
echo Vui long giu mo 2 cua so nay trong suot qua trinh su dung.
echo.
pause
