<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Report.php';

$page_title = "Item Report";
$report = new Report($db);

// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

try {
    // Get item data
    $items = $report->getItemReport($category_id);
    
    // Get categories for filter dropdown
    $categories = $report->getAllCategories();
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error generating report: " . $e->getMessage();
    $items = null;
    $categories = null;
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">
            <i class="fas fa-boxes"></i> Item Report
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
                    <div class="col-md-6">
                        <label for="category" class="form-label">Item Category (Optional)</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php if ($categories): ?>
                                <?php while ($category = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['category']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Generate Report
                        </button>
                        <a href="item_report.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-refresh"></i> Reset Filters
                        </a>

                        <!-- Enhanced Export Section -->
                        <div class="export-section">
                            <!-- Export Dropdown -->
                            <div class="btn-group me-2" role="group">
                                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="exportDropdown">
                                    <i class="fas fa-download"></i> Export Report
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                    <li><h6 class="dropdown-header"><i class="fas fa-file-export"></i> Export Options</h6></li>
                                    <li>
                                        <a class="dropdown-item export-link"
                                           href="export_item_report.php?format=csv&category=<?php echo $category_id ?? ''; ?>"
                                           data-format="csv"
                                           title="Download as Excel-compatible CSV file">
                                            <i class="fas fa-file-csv text-success"></i>
                                            <span class="ms-2">CSV File</span>
                                            <small class="text-muted d-block">Excel compatible</small>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-link"
                                           href="export_item_report.php?format=pdf&category=<?php echo $category_id ?? ''; ?>"
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
                            <div class="btn-group me-2" role="group" aria-label="Quick Export">
                                <a href="export_item_report.php?format=csv&category=<?php echo $category_id ?? ''; ?>"
                                   class="btn btn-outline-success btn-sm export-link"
                                   data-format="csv"
                                   title="Quick CSV Export">
                                    <i class="fas fa-file-csv"></i>
                                </a>
                                <a href="export_item_report.php?format=pdf&category=<?php echo $category_id ?? ''; ?>"
                                   class="btn btn-outline-danger btn-sm export-link"
                                   data-format="pdf"
                                   title="Quick PDF Export">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </div>

                            <!-- Print Button -->
                            <button type="button" class="btn btn-outline-primary print-btn" title="Print this report">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Report Results -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table"></i> Item Report Results
                </h5>
                <?php if ($category_id): ?>
                    <span class="badge bg-primary">
                        Filtered by Category
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary">
                        All Categories
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($items && $items->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="reportTable">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Item Code</th>
                                    <th>Item Category</th>
                                    <th>Item Sub Category</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Value</th>
                                    <th>Stock Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_quantity = 0;
                                $total_value = 0;
                                $low_stock_count = 0;
                                
                                while ($item = $items->fetch_assoc()): 
                                    $quantity = intval($item['item_quantity']);
                                    $item_total_value = floatval($item['total_value']);
                                    
                                    $total_quantity += $quantity;
                                    $total_value += $item_total_value;
                                    
                                    if ($quantity < 10) {
                                        $low_stock_count++;
                                    }
                                    
                                    // Determine stock status
                                    if ($quantity == 0) {
                                        $stock_status = '<span class="badge bg-danger">Out of Stock</span>';
                                    } elseif ($quantity < 10) {
                                        $stock_status = '<span class="badge bg-warning">Low Stock</span>';
                                    } elseif ($quantity < 50) {
                                        $stock_status = '<span class="badge bg-info">Medium Stock</span>';
                                    } else {
                                        $stock_status = '<span class="badge bg-success">Good Stock</span>';
                                    }
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($item['item_code']); ?></code>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($item['item_category'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo htmlspecialchars($item['item_subcategory'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo number_format($quantity); ?></strong>
                                        </td>
                                        <td>LKR <?php echo number_format($item['unit_price'], 2); ?></td>
                                        <td>
                                            <strong class="text-success">
                                                LKR <?php echo number_format($item_total_value, 2); ?>
                                            </strong>
                                        </td>
                                        <td><?php echo $stock_status; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <th colspan="4">TOTAL</th>
                                    <th>
                                        <span class="badge bg-light text-dark">
                                            <?php echo number_format($total_quantity); ?>
                                        </span>
                                    </th>
                                    <th>-</th>
                                    <th>
                                        <strong class="text-warning">
                                            LKR <?php echo number_format($total_value, 2); ?>
                                        </strong>
                                    </th>
                                    <th>-</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            Total: <?php echo $items->num_rows; ?> item(s) found
                            <?php if ($category_id): ?>
                                for the selected category
                            <?php endif; ?>
                        </small>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No items found</h5>
                        <p class="text-muted">
                            No items were found
                            <?php if ($category_id): ?>
                                for the selected category
                            <?php endif; ?>.
                        </p>
                        <a href="../item/add.php" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add New Item
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<?php if ($items && $items->num_rows > 0): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie"></i> Inventory Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <h4 class="text-primary"><?php echo $items->num_rows; ?></h4>
                        <small class="text-muted">Total Items</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-info"><?php echo number_format($total_quantity); ?></h4>
                        <small class="text-muted">Total Quantity</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-success">LKR <?php echo number_format($total_value, 2); ?></h4>
                        <small class="text-muted">Total Inventory Value</small>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-warning"><?php echo $low_stock_count; ?></h4>
                        <small class="text-muted">Low Stock Items</small>
                    </div>
                </div>
                
                <?php if ($low_stock_count > 0): ?>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Stock Alert:</strong> You have <?php echo $low_stock_count; ?> item(s) with low stock (less than 10 units).
                        <a href="../item/" class="alert-link">Manage Items</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Category Breakdown -->
<?php if (!$category_id): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar"></i> Category Breakdown
                </h5>
            </div>
            <div class="card-body">
                <?php
                // Reset items result and group by category
                $items->data_seek(0);
                $category_stats = [];
                
                while ($item = $items->fetch_assoc()) {
                    $cat = $item['item_category'] ?? 'Uncategorized';
                    if (!isset($category_stats[$cat])) {
                        $category_stats[$cat] = [
                            'count' => 0,
                            'quantity' => 0,
                            'value' => 0
                        ];
                    }
                    $category_stats[$cat]['count']++;
                    $category_stats[$cat]['quantity'] += intval($item['item_quantity']);
                    $category_stats[$cat]['value'] += floatval($item['total_value']);
                }
                ?>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Item Count</th>
                                <th>Total Quantity</th>
                                <th>Total Value</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($category_stats as $category => $stats): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($category); ?></span>
                                    </td>
                                    <td><?php echo number_format($stats['count']); ?></td>
                                    <td><?php echo number_format($stats['quantity']); ?></td>
                                    <td>LKR <?php echo number_format($stats['value'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $percentage = $total_value > 0 ? ($stats['value'] / $total_value) * 100 : 0;
                                        echo number_format($percentage, 1) . '%';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Loading Indicator -->
<div class="loading" id="loadingIndicator">
    <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Processing export...</p>
</div>

<script>
// Enhanced Export Functionality
$(document).ready(function() {
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
                <title>Item Report</title>
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
                    <h2>Item Report</h2>
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

    console.log('Enhanced item report functionality loaded');
});
</script>

<?php include '../includes/footer.php'; ?>
