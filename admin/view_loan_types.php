<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle deletion of a loan type
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Delete loan type from the database
    $delete_query = "DELETE FROM loan_types WHERE loan_type_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Loan type deleted successfully.');
                window.location.href = 'view_loan_types.php';
              </script>";
    } else {
        echo "<script>alert('Error: Unable to delete loan type.');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Loan Types</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="container mt-5">
    <h1 class="text-center">Loan Types</h1>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Loan Type ID</th>
                    <th>Loan Name</th>
                    <th>Description</th>
                    <th>Interest Rate</th>
                    <th>Repayment Terms (Months)</th>
                    <th>Max Amount</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all loan types
                $query = "SELECT * FROM loan_types ORDER BY created_at DESC";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['loan_type_id']}</td>
                                <td>{$row['loan_name']}</td>
                                <td>{$row['description']}</td>
                                <td>{$row['interest_rate']}%</td>
                                <td>{$row['repayment_terms']}</td>
                                <td>{$row['max_amount']}</td>
                                <td>{$row['created_at']}</td>
                                <td>{$row['updated_at']}</td>
                                <td>
                                    <a href='edit_loan_type.php?loan_type_id={$row['loan_type_id']}' class='btn btn-warning btn-sm mb-2'>Edit</a>
                                    <a href='view_loan_types.php?delete_id={$row['loan_type_id']}' onclick='return confirm(\"Are you sure you want to delete this loan type?\");' class='btn btn-danger btn-sm'>Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No loan types found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
