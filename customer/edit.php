<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Customer.php';

$page_title = "Edit Customer";
$customer = new Customer($db);

// Get customer ID
$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$customer_id) {
    $_SESSION['error'] = "Invalid customer ID";
    header("Location: index.php");
    exit;
}

// Get customer data
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize input data
        $data = [
            'title' => trim($_POST['title']),
            'first_name' => trim($_POST['first_name']),
            'middle_name' => trim($_POST['middle_name']),
            'last_name' => trim($_POST['last_name']),
            'contact_no' => trim($_POST['contact_no']),
            'district' => trim($_POST['district'])
        ];
        
        // Validate data
        $errors = $customer->validateCustomerData($data);
        
        // Check if contact number already exists (excluding current customer)
        if (empty($errors) && $customer->isContactNumberExists($data['contact_no'], $customer_id)) {
            $errors[] = "Contact number already exists";
        }
        
        if (empty($errors)) {
            if ($customer->updateCustomer($customer_id, $data)) {
                $_SESSION['success'] = "Customer updated successfully!";
                header("Location: view.php?id=" . $customer_id);
                exit;
            } else {
                $errors[] = "Failed to update customer. Please try again.";
            }
        }
        
    } catch (Exception $e) {
        $errors[] = "Error: " . $e->getMessage();
    }
} else {
    // Pre-populate form with existing data
    $data = $customer_data;
}

// Get districts for dropdown
try {
    $districts = $customer->getDistricts();
} catch (Exception $e) {
    $districts = null;
    $error_message = "Error loading districts: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Customers</a></li>
                <li class="breadcrumb-item"><a href="view.php?id=<?php echo $customer_id; ?>">View Customer</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        
        <h1 class="mb-4">
            <i class="fas fa-user-edit"></i> Edit Customer
            <small class="text-muted">#<?php echo $customer_id; ?></small>
        </h1>
    </div>
</div>

<?php if (isset($errors) && !empty($errors)): ?>
    <div class="alert alert-danger">
        <h6>Please fix the following errors:</h6>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-warning">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user"></i> Customer Information
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="customerForm" novalidate>
                    <div class="row">
                        <!-- Title -->
                        <div class="col-md-3 mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <select class="form-select" id="title" name="title" required>
                                <option value="">Select Title</option>
                                <option value="Mr" <?php echo ($data['title'] === 'Mr') ? 'selected' : ''; ?>>Mr</option>
                                <option value="Mrs" <?php echo ($data['title'] === 'Mrs') ? 'selected' : ''; ?>>Mrs</option>
                                <option value="Miss" <?php echo ($data['title'] === 'Miss') ? 'selected' : ''; ?>>Miss</option>
                                <option value="Dr" <?php echo ($data['title'] === 'Dr') ? 'selected' : ''; ?>>Dr</option>
                            </select>
                        </div>
                        
                        <!-- First Name -->
                        <div class="col-md-4 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="<?php echo htmlspecialchars($data['first_name']); ?>"
                                   required
                                   maxlength="50">
                        </div>
                        
                        <!-- Middle Name -->
                        <div class="col-md-5 mb-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="middle_name" 
                                   name="middle_name" 
                                   value="<?php echo htmlspecialchars($data['middle_name']); ?>"
                                   maxlength="50">
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Last Name -->
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="<?php echo htmlspecialchars($data['last_name']); ?>"
                                   required
                                   maxlength="50">
                        </div>
                        
                        <!-- Contact Number -->
                        <div class="col-md-6 mb-3">
                            <label for="contact_no" class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="contact_no" 
                                   name="contact_no" 
                                   value="<?php echo htmlspecialchars($data['contact_no']); ?>"
                                   required
                                   pattern="[0-9]{10}"
                                   maxlength="10"
                                   placeholder="0771234567">
                            <div class="form-text">Enter 10-digit mobile number</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- District -->
                        <div class="col-md-6 mb-3">
                            <label for="district" class="form-label">District <span class="text-danger">*</span></label>
                            <select class="form-select" id="district" name="district" required>
                                <option value="">Select District</option>
                                <?php if ($districts): ?>
                                    <?php while ($district = $districts->fetch_assoc()): ?>
                                        <option value="<?php echo $district['id']; ?>" 
                                                <?php echo ($data['district'] == $district['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($district['district']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="view.php?id=<?php echo $customer_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to View
                                </a>
                                <div>
                                    <a href="index.php" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-list"></i> Customer List
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Customer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Form validation
    $('#customerForm').on('submit', function(e) {
        let isValid = true;
        
        // Clear previous validation
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Validate required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                $(this).after('<div class="invalid-feedback">This field is required.</div>');
                isValid = false;
            }
        });
        
        // Validate contact number
        const contactNo = $('#contact_no').val().trim();
        if (contactNo && !/^[0-9]{10}$/.test(contactNo)) {
            $('#contact_no').addClass('is-invalid');
            $('#contact_no').after('<div class="invalid-feedback">Contact number must be exactly 10 digits.</div>');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        }
    });
    
    // Real-time contact number validation
    $('#contact_no').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        $(this).val(value);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
