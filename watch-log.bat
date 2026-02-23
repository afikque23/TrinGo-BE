@echo off
echo ==========================================
echo   Laravel Log Monitor - Real Time
echo ==========================================
echo.
echo Monitoring: storage\logs\laravel.log
echo Press Ctrl+C to stop
echo.
echo ==========================================
echo.

powershell -Command "Get-Content storage\logs\laravel.log -Wait -Tail 20"
