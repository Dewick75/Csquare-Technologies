@echo off
REM ERP System - Windows Startup Script
REM Csquare Technologies

echo ========================================
echo  ERP System - Development Server
echo  Csquare Technologies
echo ========================================
echo.

REM Check if PHP is available
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH
    echo.
    echo Please install PHP or add it to your system PATH
    echo Download PHP from: https://www.php.net/downloads
    echo.
    echo Alternatively, use XAMPP/WAMP which includes PHP:
    echo - XAMPP: https://www.apachefriends.org/
    echo - WAMP: https://www.wampserver.com/
    echo.
    pause
    exit /b 1
)

echo PHP is available
php --version
echo.

REM Check if database is configured
if not exist "config\database.php" (
    echo WARNING: Database configuration not found
    echo Please run setup_database.php first
    echo.
)

echo Starting PHP development server...
echo.
echo Server will be available at:
echo   http://localhost:8000
echo.
echo To stop the server, press Ctrl+C
echo.
echo ========================================
echo.

REM Start PHP built-in server
php -S localhost:8000

echo.
echo Server stopped.
pause
