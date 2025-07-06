#!/bin/bash
# ERP System - Linux/Mac Startup Script
# Csquare Technologies

echo "========================================"
echo " ERP System - Development Server"
echo " Csquare Technologies"
echo "========================================"
echo

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "ERROR: PHP is not installed or not in PATH"
    echo
    echo "Please install PHP:"
    echo "Ubuntu/Debian: sudo apt install php php-mysql"
    echo "CentOS/RHEL:   sudo yum install php php-mysql"
    echo "macOS:         brew install php"
    echo
    exit 1
fi

echo "PHP is available"
php --version
echo

# Check if database is configured
if [ ! -f "config/database.php" ]; then
    echo "WARNING: Database configuration not found"
    echo "Please run setup_database.php first"
    echo
fi

# Check if MySQL is running (optional check)
if command -v mysql &> /dev/null; then
    if ! mysql -e "SELECT 1" &> /dev/null; then
        echo "WARNING: MySQL might not be running"
        echo "Please start MySQL service:"
        echo "Ubuntu/Debian: sudo systemctl start mysql"
        echo "CentOS/RHEL:   sudo systemctl start mysqld"
        echo "macOS:         brew services start mysql"
        echo
    fi
fi

echo "Starting PHP development server..."
echo
echo "Server will be available at:"
echo "  http://localhost:8000"
echo
echo "To stop the server, press Ctrl+C"
echo
echo "========================================"
echo

# Start PHP built-in server
php -S localhost:8000

echo
echo "Server stopped."
