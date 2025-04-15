<?php
include('config.php');
session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch loan types
$sql_loans = "SELECT loan_type_id, loan_name FROM loan_types";
$result_loans = $conn->query($sql_loans);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loan_type_id = $_POST["loan_type_id"];
    $amount_requested = $_POST["amount_requested"];
    $duration_months = $_POST["duration_months"];

    // Insert into DB
    $sql_insert = "INSERT INTO loan_applications (customer_id, loan_type_id, amount_requested, duration_months, application_status, created_at, updated_at)
                   VALUES (?, ?, ?, ?, 'pending', NOW(), NOW())";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("iiii", $user_id, $loan_type_id, $amount_requested, $duration_months);

    if ($stmt->execute()) {
        $success_message = "Loan application submitted successfully! Your application is now pending.";
    } else {
        $error_message = "Error submitting application: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Request Loan</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding-top: 20px;
        }

        .loan-form {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .loan-form h2 {
            color: #007bff;
        }

        .loan-form .form-group label {
            font-size: 1.1rem;
        }

        .loan-form .form-group input,
        .loan-form .form-group select {
            font-size: 1.1rem;
        }

        .loan-form button {
            padding: 10px 20px;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <!-- Messages -->
        <?php if (isset($success_message)) { ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php } elseif (isset($error_message)) { ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php } ?>

        <div class="form-container">
            <div class="loan-form">
                <h2>Request a Loan</h2>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="loan_type_id">Loan Type:</label>
                        <select class="form-control" id="loan_type_id" name="loan_type_id" required>
                            <option value="">Select Loan Type</option>
                            <?php while ($row = $result_loans->fetch_assoc()) { ?>
                                <option value="<?php echo $row['loan_type_id']; ?>">
                                    <?php echo htmlspecialchars($row['loan_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="amount_requested">Amount Requested:</label>
                        <input type="number" class="form-control" id="amount_requested" name="amount_requested" required>
                    </div>

                    <div class="form-group">
                        <label for="duration_months">Duration (in months):</label>
                        <input type="number" class="form-control" id="duration_months" name="duration_months" min="1" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
