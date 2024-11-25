<?php
session_start();
include('config.php');

// Check if the user is logged in as an officer
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "officer") {
    header("Location: officerlogin.php");
    exit;
}

// Get the officer's username and ID from the session
$username = $_SESSION["username"];
$officer_id = $_SESSION["id"];

// Fetch the officer's assigned tasks (loan applications)
$sql_tasks = "SELECT la.application_id, lt.loan_name, la.amount_requested, la.application_status, la.feedback, 
                     la.customer_id, u.full_name AS customer_name
              FROM loan_applications la
              JOIN loan_types lt ON la.loan_type_id = lt.loan_type_id
              JOIN users u ON la.customer_id = u.id
              WHERE la.assigned_officer_id = ?";
$stmt = $conn->prepare($sql_tasks);
$stmt->bind_param("i", $officer_id);
$stmt->execute();
$result_tasks = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Officer Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <h3>Your Assigned Loan Applications</h3>

        <?php if ($result_tasks->num_rows > 0) { ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Loan Type</th>
                        <th>Amount Requested</th>
                        <th>Status</th>
                        <th>Feedback</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_tasks->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['loan_name']); ?></td>
                            <td><?php echo number_format($row['amount_requested'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['application_status']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['feedback'], 0, 10)); ?></td>
                            <td>
                            <a href="view_application_details.php?application_id=<?php echo $row['application_id']; ?>" class="btn btn-info btn-sm">View Details</a>
<a href="loan_disbursement.php?application_id=<?php echo $row['application_id']; ?>" class="btn btn-success btn-sm">Loan Distribution</a>
<a href="loan_messages.php?application_id=<?php echo $row['application_id']; ?>" class="btn btn-primary btn-sm mt-2">View Messages</a>

                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No tasks assigned to you yet.</p>
        <?php } ?>
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
