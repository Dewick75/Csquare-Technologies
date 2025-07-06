<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Customer.php';

$customer = new Customer($db);

// Get customer ID
$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$customer_id) {
    $_SESSION['error'] = "Invalid customer ID";
    header("Location: index.php");
    exit;
}

// Get customer data for confirmation
try {
    $customer_data = $customer->getCustomerById($customer_id);
    if (!$customer_data) {
        $_SESSION['error'] = "Customer not found";
        header("Location: index.php");
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error loading customer: " . $e->getMessage();
    header("Location: index.php");
    exit;
}

// Handle deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        if ($customer->deleteCustomer($customer_id)) {
            $_SESSION['success'] = "Customer deleted successfully!";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to delete customer. Please try again.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting customer: " . $e->getMessage();
    }
}

$page_title = "Delete Customer";
include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Customers</a></li>
                <li class="breadcrumb-item"><a href="view.php?id=<?php echo $customer_id; ?>">View Customer</a></li>
                <li class="breadcrumb-item active">Delete</li>
            </ol>
        </nav>
        
        <h1 class="mb-4 text-danger">
            <i class="fas fa-exclamation-triangle"></i> Delete Customer
        </h1>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-warning"></i> Confirm Deletion
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning!</strong> This action cannot be undone.
                </div>
                
                <p>Are you sure you want to delete the following customer?</p>
                
                <div class="bg-light p-3 rounded mb-4">
                    <h6>Customer Details:</h6>
                    <ul class="list-unstyled mb-0">
                        <li><strong>ID:</strong> <?php echo $customer_id; ?></li>
                        <li><strong>Name:</strong> 
                            <?php 
                            echo htmlspecialchars($customer_data['title'] . ' ' . 
                                                $customer_data['first_name'] . ' ' . 
                                                $customer_data['middle_name'] . ' ' . 
                                                $customer_data['last_name']); 
                            ?>
                        </li>
                        <li><strong>Contact:</strong> <?php echo htmlspecialchars($customer_data['contact_no']); ?></li>
                        <li><strong>District:</strong> <?php echo htmlspecialchars($customer_data['district_name'] ?? 'N/A'); ?></li>
                    </ul>
                </div>
                
                <?php
                // Check if customer has invoices
                try {
                    $invoice_check = $db->prepare("SELECT COUNT(*) as count FROM invoice WHERE customer = ?");
                    $invoice_check->bind_param("i", $customer_id);
                    $invoice_check->execute();
                    $result = $invoice_check->get_result();
                    $invoice_count = $result->fetch_assoc()['count'];
                    
                    if ($invoice_count > 0): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-ban"></i>
                            <strong>Cannot Delete!</strong> This customer has <?php echo $invoice_count; ?> invoice(s) associated with them. 
                            Please remove all invoices before deleting this customer.
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="view.php?id=<?php echo $customer_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Customer
                            </a>
                            <a href="../reports/invoice_report.php?customer=<?php echo $customer_id; ?>" class="btn btn-info">
                                <i class="fas fa-file-invoice"></i> View Invoices
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <form method="POST">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="confirmCheck" required>
                                <label class="form-check-label" for="confirmCheck">
                                    I understand that this action cannot be undone
                                </label>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="view.php?id=<?php echo $customer_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
                                <button type="submit" name="confirm_delete" class="btn btn-danger" id="deleteBtn" disabled>
                                    <i class="fas fa-trash"></i> Delete Customer
                                </button>
                            </div>
                        </form>
                    <?php endif;
                    
                } catch (Exception $e) { ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error checking customer invoices: <?php echo htmlspecialchars($e->getMessage()); ?>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="view.php?id=<?php echo $customer_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Customer
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Enable/disable delete button based on checkbox
    $('#confirmCheck').on('change', function() {
        $('#deleteBtn').prop('disabled', !this.checked);
    });
    
    // Additional confirmation on form submission
    $('form').on('submit', function(e) {
        if (!confirm('Are you absolutely sure you want to delete this customer? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
