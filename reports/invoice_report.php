<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Report.php';

$page_title = "Invoice Report";
$report = new Report($db);

// Get filter parameters
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$customer_id = isset($_GET['customer']) ? (int)$_GET['customer'] : null;

// Set default date range if not provided
if (empty($start_date) || empty($end_date)) {
    // Try to get the actual date range from existing invoices
    $date_range_result = $db->query("SELECT MIN(date) as min_date, MAX(date) as max_date FROM invoice");
    if ($date_range_result && $date_range_result->num_rows > 0) {
        $date_range = $date_range_result->fetch_assoc();
        if ($date_range['min_date'] && $date_range['max_date']) {
            $start_date = $date_range['min_date'];
            $end_date = $date_range['max_date'];
        } else {
            // Fallback to last 30 days if no invoices exist
            $end_date = date('Y-m-d');
            $start_date = date('Y-m-d', strtotime('-30 days'));
        }
    } else {
        // Fallback to last 30 days
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-30 days'));
    }
}

try {
    // Get invoice data
    $invoices = $report->getInvoiceReport($start_date, $end_date, $customer_id);
    
    // Get customers for filter dropdown
    $customers = $report->getAllCustomers();
    
    // Get report statistics
    $stats = $report->getReportStats($start_date, $end_date);
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error generating report: " . $e->getMessage();
    $invoices = null;
    $customers = null;
    $stats = null;
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-file-invoice"></i> Invoice Report
        </h1>
    </div>
</div>

<!-- Filter Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card search-box">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-filter"></i> Report Filters
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="start_date" 
                               name="start_date" 
                               value="<?php echo htmlspecialchars($start_date); ?>"
                               required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="end_date" 
                               name="end_date" 
                               value="<?php echo htmlspecialchars($end_date); ?>"
                               required>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="customer" class="form-label">Customer (Optional)</label>
                        <select class="form-select" id="customer" name="customer">
                            <option value="">All Customers</option>
                            <?php if ($customers): ?>
                                <?php while ($customer = $customers->fetch_assoc()): ?>
                                    <option value="<?php echo $customer['id']; ?>" 
                                            <?php echo ($customer_id == $customer['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($customer['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Generate Report
                        </button>
                        <a href="invoice_report.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-refresh"></i> Reset Filters
                        </a>

                        <!-- Enhanced Export Section -->
                        <div class="export-section ms-2">
                            <!-- Export Dropdown -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="exportDropdown">
                                    <i class="fas fa-download"></i> Export Report
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                    <li><h6 class="dropdown-header"><i class="fas fa-file-export"></i> Export Options</h6></li>
                                    <li>
                                        <a class="dropdown-item export-link"
                                           href="export_invoice_report.php?format=csv&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&customer=<?php echo $customer_id; ?>"
                                           data-format="csv"
                                           title="Download as Excel-compatible CSV file">
                                            <i class="fas fa-file-csv text-success"></i>
                                            <span class="ms-2">CSV File</span>
                                            <small class="text-muted d-block">Excel compatible</small>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-link"
                                           href="export_invoice_report.php?format=pdf&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&customer=<?php echo $customer_id; ?>"
                                           data-format="pdf"
                                           title="Download as PDF document">
                                            <i class="fas fa-file-pdf text-danger"></i>
                                            <span class="ms-2">PDF Document</span>
                                            <small class="text-muted d-block">Print-ready format</small>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item" onclick="copyTableData()" title="Copy table data to clipboard">
                                            <i class="fas fa-copy text-info"></i>
                                            <span class="ms-2">Copy to Clipboard</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <!-- Quick Export Buttons -->
                            <div class="btn-group ms-2" role="group" aria-label="Quick Export">
                                <a href="export_invoice_report.php?format=csv&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&customer=<?php echo $customer_id; ?>"
                                   class="btn btn-outline-success btn-sm export-link"
                                   data-format="csv"
                                   title="Quick CSV Export">
                                    <i class="fas fa-file-csv"></i>
                                </a>
                                <a href="export_invoice_report.php?format=pdf&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&customer=<?php echo $customer_id; ?>"
                                   class="btn btn-outline-danger btn-sm export-link"
                                   data-format="pdf"
                                   title="Quick PDF Export">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </div>

                            <!-- Print Button -->
                            <button type="button" class="btn btn-outline-primary ms-2 print-btn" title="Print this report">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<?php if ($stats): ?>
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3><?php echo number_format($stats['total_invoices']); ?></h3>
                <p class="mb-0">Total Invoices</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3>LKR <?php echo number_format($stats['total_revenue'], 2); ?></h3>
                <p class="mb-0">Total Revenue</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3>LKR <?php echo number_format($stats['average_invoice'], 2); ?></h3>
                <p class="mb-0">Average Invoice</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h3><?php echo number_format($stats['total_items_sold']); ?></h3>
                <p class="mb-0">Items Sold</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Report Results -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table"></i> Invoice Report Results
                </h5>
                <span class="badge bg-primary">
                    Period: <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>
                </span>
            </div>
            <div class="card-body">
                <?php if ($invoices && $invoices->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="reportTable">
                            <thead>
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Customer District</th>
                                    <th>Item Count</th>
                                    <th>Invoice Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_amount = 0;
                                $total_items = 0;
                                while ($invoice = $invoices->fetch_assoc()): 
                                    $total_amount += floatval($invoice['amount']);
                                    $total_items += intval($invoice['item_count']);
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($invoice['invoice_no']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($invoice['formatted_date']); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['customer_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($invoice['customer_district'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo number_format($invoice['item_count']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                LKR <?php echo number_format($invoice['amount'], 2); ?>
                                            </strong>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <th colspan="4">TOTAL</th>
                                    <th>
                                        <span class="badge bg-light text-dark">
                                            <?php echo number_format($total_items); ?>
                                        </span>
                                    </th>
                                    <th>
                                        <strong class="text-warning">
                                            LKR <?php echo number_format($total_amount, 2); ?>
                                        </strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            Total: <?php echo $invoices->num_rows; ?> invoice(s) found for the selected period
                            <?php if ($customer_id): ?>
                                for the selected customer
                            <?php endif; ?>
                        </small>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No invoices found</h5>
                        <p class="text-muted">
                            No invoices were found for the selected date range
                            <?php if ($customer_id): ?>
                                and customer
                            <?php endif; ?>.
                        </p>
                        <p class="text-muted">Try adjusting your search criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Enhanced Export Functionality
    let exportInProgress = false;

    // Export link click handler with progress indication
    $('.export-link').on('click', function(e) {
        if (exportInProgress) {
            e.preventDefault();
            return false;
        }

        const format = $(this).data('format');
        const link = this;

        // Show loading state
        exportInProgress = true;
        const originalText = $(link).html();
        $(link).html('<i class="fas fa-spinner fa-spin"></i> Generating...');
        $(link).addClass('disabled');

        // Create a temporary iframe for download
        const iframe = $('<iframe>').hide().appendTo('body');
        iframe.attr('src', $(link).attr('href'));

        // Reset button after delay
        setTimeout(() => {
            $(link).html(originalText);
            $(link).removeClass('disabled');
            exportInProgress = false;
            iframe.remove();

            // Show success message
            showNotification(`${format.toUpperCase()} export completed!`, 'success');
        }, 3000);

        e.preventDefault();
        return false;
    });

    // Copy table data to clipboard
    window.copyTableData = function() {
        const table = document.getElementById('reportTable');
        if (!table) {
            showNotification('No table data to copy', 'warning');
            return;
        }

        let csvContent = '';
        const rows = table.querySelectorAll('tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('th, td');
            const rowData = Array.from(cells).map(cell => {
                return '"' + cell.textContent.trim().replace(/"/g, '""') + '"';
            });
            csvContent += rowData.join(',') + '\n';
        });

        navigator.clipboard.writeText(csvContent).then(() => {
            showNotification('Table data copied to clipboard!', 'success');
        }).catch(() => {
            showNotification('Failed to copy data', 'error');
        });
    };

    // Enhanced print functionality
    $('.print-btn').on('click', function() {
        const printWindow = window.open('', '_blank');
        const reportContent = $('.card').last().html();

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Invoice Report - ${$('#start_date').val()} to ${$('#end_date').val()}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @media print {
                        .btn, .dropdown { display: none !important; }
                        .card { border: none !important; box-shadow: none !important; }
                        body { font-size: 12px; }
                        table { font-size: 11px; }
                    }
                    .print-header { text-align: center; margin-bottom: 20px; }
                    .print-date { text-align: right; margin-bottom: 10px; }
                </style>
            </head>
            <body>
                <div class="print-header">
                    <h2>Invoice Report</h2>
                    <p>Period: ${$('#start_date').val()} to ${$('#end_date').val()}</p>
                </div>
                <div class="print-date">
                    Generated on: ${new Date().toLocaleDateString()}
                </div>
                ${reportContent}
            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => printWindow.print(), 500);
    });

    // Notification system
    function showNotification(message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed"
                 style="top: 20px; right: 20px; z-index: 1050; min-width: 300px;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(notification);
        setTimeout(() => notification.alert('close'), 5000);
    }

    // Validate date range
    $('#start_date, #end_date').on('change', function() {
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#end_date').val());

        if (startDate > endDate) {
            showNotification('Start date cannot be later than end date.', 'warning');
            $(this).focus();
        }
    });

    // Quick date range buttons
    const today = new Date();
    const formatDate = (date) => date.toISOString().split('T')[0];

    // Add quick date range buttons
    const quickRanges = $(`
        <div class="mt-2">
            <small class="text-muted me-2">Quick ranges:</small>
            <button type="button" class="btn btn-outline-secondary btn-sm me-1" data-range="today">Today</button>
            <button type="button" class="btn btn-outline-secondary btn-sm me-1" data-range="week">This Week</button>
            <button type="button" class="btn btn-outline-secondary btn-sm me-1" data-range="month">This Month</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-range="year">This Year</button>
        </div>
    `);
    
    $('.card-body form').append(quickRanges);
    
    // Handle quick range clicks
    $('[data-range]').on('click', function() {
        const range = $(this).data('range');
        let startDate, endDate = new Date();
        
        switch(range) {
            case 'today':
                startDate = new Date();
                break;
            case 'week':
                startDate = new Date();
                startDate.setDate(startDate.getDate() - startDate.getDay());
                break;
            case 'month':
                startDate = new Date();
                startDate.setDate(1);
                break;
            case 'year':
                startDate = new Date();
                startDate.setMonth(0, 1);
                break;
        }
        
        $('#start_date').val(formatDate(startDate));
        $('#end_date').val(formatDate(endDate));
    });
});
</script>

<?php include '../includes/footer.php'; ?>
