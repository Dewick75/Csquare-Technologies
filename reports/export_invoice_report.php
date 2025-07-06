<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Report.php';

// Check if user is logged in (if you have authentication)
// if (!isset($_SESSION['user_id'])) {
//     header('Location: ../login.php');
//     exit;
// }

$report = new Report($db);

// Get filter parameters
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$customer_id = isset($_GET['customer']) ? (int)$_GET['customer'] : null;
$format = isset($_GET['format']) ? trim($_GET['format']) : 'csv';

// Set default date range if not provided (last 30 days)
if (empty($start_date) || empty($end_date)) {
    $end_date = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime('-30 days'));
}

try {
    // Validate format
    if (!in_array($format, ['csv', 'pdf'])) {
        throw new Exception("Invalid export format: " . $format);
    }

    // Validate date range
    if (!empty($start_date) && !empty($end_date)) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        if ($start > $end) {
            throw new Exception("Start date cannot be later than end date");
        }
    }

    if ($format === 'pdf') {
        // Export as PDF
        $report->exportInvoiceReportPDF($start_date, $end_date, $customer_id);
    } else {
        // Export as CSV (default)
        $report->exportInvoiceReportCSV($start_date, $end_date, $customer_id);
    }
} catch (Exception $e) {
    error_log("Export error: " . $e->getMessage());
    $_SESSION['error'] = "Error exporting report: " . $e->getMessage();
    header('Location: invoice_report.php?error=export_failed');
    exit;
}
?>
