# ERP System Installation Guide
**Csquare Technologies Internship Assignment**

## 🚀 Quick Start Guide

### Prerequisites
- **PHP 7.4+** (Recommended: PHP 8.0+)
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Web Server** (Apache/Nginx) or PHP built-in server
- **Web Browser** (Chrome, Firefox, Safari, Edge)

### Option 1: XAMPP/WAMP/MAMP (Recommended for Development)

1. **Download and Install XAMPP**
   - Download from: https://www.apachefriends.org/
   - Install and start Apache + MySQL services

2. **Copy Project Files**
   ```bash
   # Copy the entire project folder to:
   # Windows (XAMPP): C:\xampp\htdocs\erp-system\
   # Windows (WAMP): C:\wamp64\www\erp-system\
   # macOS (MAMP): /Applications/MAMP/htdocs/erp-system/
   ```

3. **Setup Database**
   - Open browser: `http://localhost/phpmyadmin`
   - Create database named: `assignment`
   - Import file: `database/assignment.sql`

4. **Configure Database Connection**
   - File is already created: `config/database.php`
   - Default settings work with XAMPP (no changes needed)

5. **Access Application**
   ```
   http://localhost/erp-system/
   ```

### Option 2: PHP Built-in Server (Quick Testing)

1. **Navigate to Project Directory**
   ```bash
   cd /path/to/erp-system
   ```

2. **Start PHP Server**
   ```bash
   php -S localhost:8000
   ```

3. **Setup Database** (Use phpMyAdmin or MySQL command line)
   ```bash
   mysql -u root -p
   CREATE DATABASE assignment;
   USE assignment;
   SOURCE database/assignment.sql;
   ```

4. **Access Application**
   ```
   http://localhost:8000
   ```

### Option 3: Production Server (Linux/Ubuntu)

1. **Install Dependencies**
   ```bash
   sudo apt update
   sudo apt install apache2 mysql-server php php-mysql php-mbstring
   ```

2. **Configure Apache**
   ```bash
   sudo cp -r erp-system /var/www/html/
   sudo chown -R www-data:www-data /var/www/html/erp-system
   sudo chmod -R 755 /var/www/html/erp-system
   ```

3. **Setup Database**
   ```bash
   sudo mysql
   CREATE DATABASE assignment;
   CREATE USER 'erp_user'@'localhost' IDENTIFIED BY 'secure_password';
   GRANT ALL PRIVILEGES ON assignment.* TO 'erp_user'@'localhost';
   FLUSH PRIVILEGES;
   USE assignment;
   SOURCE /var/www/html/erp-system/database/assignment.sql;
   ```

4. **Update Database Config**
   ```php
   // Edit config/database.php
   define('DB_USERNAME', 'erp_user');
   define('DB_PASSWORD', 'secure_password');
   ```

## 🔧 Automated Setup

### Method 1: Database Setup Script
1. Access: `http://localhost/erp-system/setup_database.php`
2. Follow the automated setup wizard
3. The script will create database and import schema automatically

### Method 2: System Test Script
1. Access: `http://localhost/erp-system/test_system.php`
2. This will test all components and show any issues
3. Use this to verify everything is working correctly

## 📁 Project Structure
```
erp-system/
├── config/
│   └── database.php          # Database configuration
├── classes/
│   ├── Customer.php          # Customer management
│   ├── Item.php             # Item management
│   ├── Report.php           # Report generation
│   └── PDFGenerator.php     # PDF export
├── customer/                # Customer CRUD pages
├── item/                    # Item CRUD pages
├── reports/                 # Report pages
├── assets/                  # CSS, JS, images
├── includes/                # Header, footer
├── database/
│   └── assignment.sql       # Database schema
├── vendor/                  # Third-party libraries
├── .htaccess               # Apache configuration
├── index.php               # Main dashboard
├── setup_database.php      # Automated setup
└── test_system.php         # System testing
```

## 🔍 Troubleshooting

### Common Issues

**1. Database Connection Error**
```
Error: Connection failed: Access denied for user 'root'@'localhost'
```
**Solution:**
- Check MySQL is running
- Verify credentials in `config/database.php`
- For XAMPP: username=`root`, password=`` (empty)

**2. Permission Denied Errors**
```
Error: Permission denied
```
**Solution:**
```bash
# Linux/Mac
chmod -R 755 /path/to/erp-system
chown -R www-data:www-data /path/to/erp-system

# Windows: Run as Administrator
```

**3. CSS/JS Not Loading**
```
404 errors for assets/css/style.css
```
**Solution:**
- Check file paths in `includes/header.php`
- Ensure `.htaccess` is present (Apache)
- Clear browser cache

**4. PDF Export Issues**
```
Error: Class 'PDFGenerator' not found
```
**Solution:**
- Verify `classes/PDFGenerator.php` exists
- Check file permissions
- Use browser print function as fallback

### Database Issues

**Import SQL File Manually:**
```bash
# Command line
mysql -u root -p assignment < database/assignment.sql

# Or use phpMyAdmin:
# 1. Select 'assignment' database
# 2. Click 'Import' tab
# 3. Choose 'assignment.sql' file
# 4. Click 'Go'
```

**Reset Database:**
```sql
DROP DATABASE IF EXISTS assignment;
CREATE DATABASE assignment;
USE assignment;
SOURCE database/assignment.sql;
```

## 🔒 Security Notes

### For Production Use:
1. **Change default database credentials**
2. **Enable HTTPS**
3. **Update PHP to latest version**
4. **Set proper file permissions**
5. **Enable firewall**
6. **Regular backups**

### Default Credentials:
- **Database:** root / (empty password)
- **No user authentication** (as per assignment requirements)

## 📞 Support

### Quick Links:
- **Setup Script:** `http://localhost/erp-system/setup_database.php`
- **Test Script:** `http://localhost/erp-system/test_system.php`
- **Dashboard:** `http://localhost/erp-system/`

### Contact:
- **Email:** hr@csquarefintech.com
- **CC:** luckshinif@csquarefintech.com, support@csqure.cloud

---

**Version:** 1.0.0  
**Last Updated:** 2024-01-01  
**Developed by:** Csquare Technologies Intern
