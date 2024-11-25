<?php
include('config.php');

session_start();

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION["id"];

// Fetch loan applications for the logged-in user
$sql_applications = "SELECT la.application_id, lt.loan_name, la.amount_requested, la.application_status, la.feedback, la.created_at, la.updated_at
                     FROM loan_applications la
                     JOIN loan_types lt ON la.loan_type_id = lt.loan_type_id
                     WHERE la.customer_id = ?";
$stmt = $conn->prepare($sql_applications);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($application_id, $loan_name, $amount_requested, $application_status, $feedback, $created_at, $updated_at);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>View Loan Applications</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

    <style>
        .loan-table-container {
            max-width: 900px;
            margin: 20px auto;
        }

        .loan-table th, .loan-table td {
            text-align: center;
            vertical-align: middle;
        }

        .loan-table td.feedback {
            max-width: 250px;
            word-wrap: break-word;
        }
    </style>
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h2>Your Loan Applications</h2>

        <?php if ($stmt->num_rows == 0) { ?>
            <div class="alert alert-info">You have no loan applications yet.</div>
        <?php } else { ?>
            <div class="loan-table-container">
                <table class="table table-bordered loan-table">
                    <thead>
                        <tr>
                            <th>Loan Type</th>
                            <th>Amount Requested</th>
                            <th>Application Status</th>
                            <th>Feedback</th>
                            <th>Date Applied</th>
                            <th>Last Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($stmt->fetch()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($loan_name); ?></td>
                                <td><?php echo number_format($amount_requested, 2); ?></td>
                                <td>
                                    <?php
                                    // Display status in a formatted way
                                    if ($application_status == 'pending') {
                                        echo '<span class="badge badge-warning">Pending</span>';
                                    } elseif ($application_status == 'approved') {
                                        echo '<span class="badge badge-success">Approved</span>';
                                    } elseif ($application_status == 'rejected') {
                                        echo '<span class="badge badge-danger">Rejected</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($feedback); ?></td>

                                <td><?php echo date("Y-m-d H:i:s", strtotime($created_at)); ?></td>
                                <td><?php echo date("Y-m-d H:i:s", strtotime($updated_at)); ?></td>
                                <td>
                                    <a href="view_loan_distribution.php?application_id=<?php echo $application_id; ?>" class="btn btn-info btn-sm">View Loan Distribution</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

    </div>

    <!-- Bootstrap JS & dependencies -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
