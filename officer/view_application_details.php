<?php
session_start();
include('config.php');

// Check if the user is logged in as an officer
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "officer") {
    header("Location: officerlogin.php");
    exit;
}

// Get the officer's ID from the session
$officer_id = $_SESSION["id"];

// Get the application ID from the URL
$application_id = isset($_GET['application_id']) ? $_GET['application_id'] : 0;

// Fetch the application details
$sql_application = "SELECT la.application_id, lt.loan_name, la.amount_requested, la.application_status, la.feedback, 
                           la.duration_months, la.created_at, la.updated_at,
                           u.full_name AS customer_name, u.email, u.phone, u.age, u.bio
                    FROM loan_applications la
                    JOIN loan_types lt ON la.loan_type_id = lt.loan_type_id
                    JOIN users u ON la.customer_id = u.id
                    WHERE la.application_id = ? AND la.assigned_officer_id = ?";
$stmt = $conn->prepare($sql_application);
$stmt->bind_param("ii", $application_id, $officer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Application not found or you do not have permission to view this application.");
}

$row = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $application_status = $_POST["application_status"];
    $feedback = $_POST["feedback"];

    // Update loan application status and feedback
    $sql_update = "UPDATE loan_applications 
                   SET application_status = ?, feedback = ?, updated_at = NOW() 
                   WHERE application_id = ? AND assigned_officer_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssii", $application_status, $feedback, $application_id, $officer_id);

    if ($stmt_update->execute()) {
        $success_message = "Application status and feedback updated successfully!";
    } else {
        $error_message = "Error updating application: " . $conn->error;
    }
    $stmt_update->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Application Details</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h3>Application Details</h3>

        <?php if (isset($success_message)) { ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php } elseif (isset($error_message)) { ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php } ?>

        <form method="POST">
            <div class="form-group">
                <label><strong>Customer Name:</strong></label> <?php echo htmlspecialchars($row['customer_name']); ?>
            </div>
            <div class="form-group">
                <label><strong>Loan Type:</strong></label> <?php echo htmlspecialchars($row['loan_name']); ?>
            </div>
            <div class="form-group">
                <label><strong>Amount Requested:</strong></label> <?php echo number_format($row['amount_requested'], 2); ?>
            </div>
            <div class="form-group">
            <label><strong>Loan Duration:</strong></label> <?php echo $row['duration_months']; ?> months
            </div>
           
            <div class="form-group">
                <label><strong>Status:</strong></label>
                <select name="application_status" class="form-control" required>
                    <option value="pending" <?php echo ($row['application_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo ($row['application_status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo ($row['application_status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="form-group">
                <label><strong>Feedback:</strong></label>
                <textarea name="feedback" class="form-control" rows="3" placeholder="Enter feedback (optional)"><?php echo htmlspecialchars($row['feedback']); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Application</button>
        </form>

        <hr>

        <h5>Customer Details</h5>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['phone']); ?></p>
        <p><strong>Age:</strong> <?php echo htmlspecialchars($row['age']); ?></p>
        <p><strong>Bio:</strong> <?php echo htmlspecialchars($row['bio']); ?></p>

    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
