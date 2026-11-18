@echo off
title Backend Laravel - Hoi Dong Truong STU (Da luong x4)
echo Dang khoi dong Backend Laravel da luong (4 Workers) tai http://127.0.0.1:8000 ...
set PHP_CLI_SERVER_WORKERS=4
cd /d "%~dp0backend"
if exist "D:\WAMP\bin\php\php8.3.14\php.exe" (
    "D:\WAMP\bin\php\php8.3.14\php.exe" artisan serve --host=127.0.0.1 --port=8000
) else (
    php artisan serve --host=127.0.0.1 --port=8000
)
pause
