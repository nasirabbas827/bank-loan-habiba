<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Check if loan_type_id is set in the URL
if (isset($_GET['loan_type_id'])) {
    $loan_type_id = $_GET['loan_type_id'];

    // Fetch loan type data based on the ID
    $query = "SELECT * FROM loan_types WHERE loan_type_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $loan_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $loan_type = $result->fetch_assoc();
    } else {
        echo "<script>alert('Loan type not found.'); window.location.href = 'view_loan_types.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href = 'view_loan_types.php';</script>";
    exit;
}

// Handle form submission to update loan type
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loan_name = trim($_POST['loan_name']);
    $description = trim($_POST['description']);
    $interest_rate = trim($_POST['interest_rate']);
    $repayment_terms = trim($_POST['repayment_terms']);
    $max_amount = trim($_POST['max_amount']);
    $updated_at = date('Y-m-d H:i:s');

    // Check for required fields
    if (empty($loan_name) || empty($description) || empty($interest_rate) || empty($repayment_terms) || empty($max_amount)) {
        $error_message = "All fields are required.";
    } else {
        // Update loan type in the database
        $update_query = "UPDATE loan_types SET loan_name = ?, description = ?, interest_rate = ?, repayment_terms = ?, max_amount = ?, updated_at = ? WHERE loan_type_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssssss", $loan_name, $description, $interest_rate, $repayment_terms, $max_amount, $updated_at, $loan_type_id);

        if ($stmt->execute()) {
            $success_message = "Loan type updated successfully.";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Loan Type</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="container mt-5">
    <h1 class="text-center">Edit Loan Type</h1>
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="loan_name">Loan Name</label>
                    <input type="text" class="form-control" id="loan_name" name="loan_name" value="<?php echo $loan_type['loan_name']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required><?php echo $loan_type['description']; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="interest_rate">Interest Rate (%)</label>
                    <input type="number" class="form-control" id="interest_rate" name="interest_rate" value="<?php echo $loan_type['interest_rate']; ?>" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="repayment_terms">Repayment Terms (Months)</label>
                    <input type="number" class="form-control" id="repayment_terms" name="repayment_terms" value="<?php echo $loan_type['repayment_terms']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="max_amount">Max Amount</label>
                    <input type="number" class="form-control" id="max_amount" name="max_amount" value="<?php echo $loan_type['max_amount']; ?>" step="0.01" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Loan Type</button>
                <a class="btn btn-outline-dark" href="view_loan_types.php">View Loan Types</a>
            </form>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
