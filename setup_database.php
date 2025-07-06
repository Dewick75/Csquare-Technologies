<?php
/**
 * Database Setup Script
 * ERP System - Csquare Technologies
 * 
 * This script helps set up the database for ERP
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'assignment';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>ERP System Database Setup</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .setup-container { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .step { padding: 20px; margin: 10px 0; border-radius: 10px; }
        .step-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .step-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .step-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .step-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
    </style>
</head>
<body>
<div class='container py-5'>
    <div class='row justify-content-center'>
        <div class='col-lg-8'>
            <div class='setup-container p-4'>
                <div class='text-center mb-4'>
                    <h1 class='display-6'><i class='fas fa-database'></i> ERP System Setup</h1>
                    <p class='lead'>Database Configuration & Setup</p>
                </div>";

// Step 1: Test MySQL Connection
echo "<div class='step step-info'>
    <h4><i class='fas fa-plug'></i> Step 1: Testing MySQL Connection</h4>";

try {
    $connection = new mysqli($db_host, $db_username, $db_password);
    if ($connection->connect_error) {
        throw new Exception("Connection failed: " . $connection->connect_error);
    }
    echo "<p class='mb-0'><i class='fas fa-check-circle text-success'></i> Successfully connected to MySQL server</p>";
    $mysql_connected = true;
} catch (Exception $e) {
    echo "<p class='mb-0'><i class='fas fa-times-circle text-danger'></i> Failed to connect to MySQL: " . $e->getMessage() . "</p>";
    $mysql_connected = false;
}

echo "</div>";

// Step 2: Check/Create Database
if ($mysql_connected) {
    echo "<div class='step step-info'>
        <h4><i class='fas fa-database'></i> Step 2: Database Setup</h4>";
    
    try {
        // Check if database exists
        $result = $connection->query("SHOW DATABASES LIKE '$db_name'");
        if ($result->num_rows > 0) {
            echo "<p><i class='fas fa-info-circle text-info'></i> Database '$db_name' already exists</p>";
        } else {
            // Create database
            if ($connection->query("CREATE DATABASE $db_name")) {
                echo "<p><i class='fas fa-check-circle text-success'></i> Database '$db_name' created successfully</p>";
            } else {
                throw new Exception("Error creating database: " . $connection->error);
            }
        }
        
        // Connect to the database
        $connection->select_db($db_name);
        echo "<p class='mb-0'><i class='fas fa-check-circle text-success'></i> Connected to database '$db_name'</p>";
        $database_ready = true;
        
    } catch (Exception $e) {
        echo "<p class='mb-0'><i class='fas fa-times-circle text-danger'></i> Database setup failed: " . $e->getMessage() . "</p>";
        $database_ready = false;
    }
    
    echo "</div>";
}

// Step 3: Import Database Schema
if ($mysql_connected && $database_ready) {
    echo "<div class='step step-info'>
        <h4><i class='fas fa-file-import'></i> Step 3: Importing Database Schema</h4>";
    
    $sql_file = 'database/assignment.sql';
    
    if (file_exists($sql_file)) {
        try {
            $sql_content = file_get_contents($sql_file);
            
            // Split SQL file into individual queries
            $queries = explode(';', $sql_content);
            $imported_tables = 0;
            $inserted_records = 0;
            
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query) && !preg_match('/^(--|\/\*|\s*$)/', $query)) {
                    if ($connection->query($query)) {
                        if (preg_match('/CREATE TABLE/i', $query)) {
                            $imported_tables++;
                        } elseif (preg_match('/INSERT INTO/i', $query)) {
                            $inserted_records++;
                        }
                    } else {
                        // Ignore errors for existing tables/data
                        if (!preg_match('/already exists|Duplicate entry/', $connection->error)) {
                            echo "<p><i class='fas fa-exclamation-triangle text-warning'></i> Query warning: " . $connection->error . "</p>";
                        }
                    }
                }
            }
            
            echo "<p><i class='fas fa-check-circle text-success'></i> Database schema imported successfully</p>";
            echo "<p><i class='fas fa-table text-info'></i> Tables processed: $imported_tables</p>";
            echo "<p class='mb-0'><i class='fas fa-database text-info'></i> Data records processed: $inserted_records</p>";
            $schema_imported = true;
            
        } catch (Exception $e) {
            echo "<p class='mb-0'><i class='fas fa-times-circle text-danger'></i> Schema import failed: " . $e->getMessage() . "</p>";
            $schema_imported = false;
        }
    } else {
        echo "<p class='mb-0'><i class='fas fa-times-circle text-danger'></i> SQL file not found: $sql_file</p>";
        $schema_imported = false;
    }
    
    echo "</div>";
}

// Step 4: Verify Tables
if ($mysql_connected && $database_ready && $schema_imported) {
    echo "<div class='step step-info'>
        <h4><i class='fas fa-check-double'></i> Step 4: Verifying Database Tables</h4>";
    
    $required_tables = ['customer', 'district', 'invoice', 'invoice_master', 'item', 'item_category', 'item_subcategory'];
    $tables_ok = true;
    
    foreach ($required_tables as $table) {
        $result = $connection->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            $count_result = $connection->query("SELECT COUNT(*) as count FROM $table");
            $count = $count_result->fetch_assoc()['count'];
            echo "<p><i class='fas fa-check text-success'></i> Table '$table' exists with $count records</p>";
        } else {
            echo "<p><i class='fas fa-times text-danger'></i> Table '$table' is missing</p>";
            $tables_ok = false;
        }
    }
    
    if ($tables_ok) {
        echo "<p class='mb-0'><i class='fas fa-check-circle text-success'></i> All required tables are present and populated</p>";
    }
    
    echo "</div>";
}

// Step 5: Final Status
echo "<div class='step " . (($mysql_connected && $database_ready && $schema_imported && $tables_ok) ? "step-success" : "step-warning") . "'>
    <h4><i class='fas fa-flag-checkered'></i> Setup Status</h4>";

if ($mysql_connected && $database_ready && $schema_imported && $tables_ok) {
    echo "<p><i class='fas fa-check-circle text-success'></i> <strong>Setup completed successfully!</strong></p>
          <p>Your ERP system is ready to use. You can now:</p>
          <ul>
              <li><a href='index.php' class='btn btn-primary btn-sm'><i class='fas fa-home'></i> Go to Dashboard</a></li>
              <li><a href='customer/' class='btn btn-info btn-sm'><i class='fas fa-users'></i> Manage Customers</a></li>
              <li><a href='item/' class='btn btn-success btn-sm'><i class='fas fa-cube'></i> Manage Items</a></li>
              <li><a href='reports/invoice_report.php' class='btn btn-warning btn-sm'><i class='fas fa-chart-bar'></i> View Reports</a></li>
          </ul>";
} else {
    echo "<p><i class='fas fa-exclamation-triangle text-warning'></i> <strong>Setup incomplete</strong></p>
          <p>Please check the errors above and ensure:</p>
          <ul>
              <li>MySQL server is running</li>
              <li>Database credentials are correct in config/database.php</li>
              <li>The database/assignment.sql file exists</li>
              <li>PHP has proper permissions</li>
          </ul>
          <p><a href='setup_database.php' class='btn btn-primary'><i class='fas fa-redo'></i> Try Again</a></p>";
}

echo "</div>";

// Close connection
if (isset($connection)) {
    $connection->close();
}

echo "            </div>
        </div>
    </div>
</div>
</body>
</html>";
?>
