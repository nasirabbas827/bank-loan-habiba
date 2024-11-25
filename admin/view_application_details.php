<?php
session_start();
include('../config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Get the application ID from the URL parameter
if (isset($_GET['application_id'])) {
    $application_id = $_GET['application_id'];

    // Fetch loan application details
    $sql_application = "SELECT la.application_id, lt.loan_name, la.amount_requested, la.application_status, la.customer_id,
                                la.feedback, la.assigned_officer_id, la.created_at, la.updated_at
                        FROM loan_applications la
                        JOIN loan_types lt ON la.loan_type_id = lt.loan_type_id
                        WHERE la.application_id = ?";
    $stmt_app = $conn->prepare($sql_application);
    $stmt_app->bind_param("i", $application_id);  // Binding the application ID as an integer
    $stmt_app->execute();
    $stmt_app->store_result();

    // Bind the result for all the columns fetched in the query
    $stmt_app->bind_result($application_id, $loan_name, $amount_requested, $application_status, $customer_id, $feedback, $assigned_officer_id, $created_at, $updated_at);
    $stmt_app->fetch();
    $stmt_app->close();
    
    // Fetch loan disbursements for the selected application
    $sql_disbursements = "SELECT ld.disbursement_id, ld.amount_disbursed, ld.disbursement_date, ld.repayment_status, ld.transaction_image
                          FROM loan_disbursements ld
                          WHERE ld.application_id = ?";
    $stmt_disb = $conn->prepare($sql_disbursements);
    $stmt_disb->bind_param("i", $application_id);  // Binding the application ID as an integer
    $stmt_disb->execute();
    $stmt_disb->store_result();
    $stmt_disb->bind_result($disbursement_id, $amount_disbursed, $disbursement_date, $repayment_status, $transaction_image);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>View Application Details</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5">
        <h1>Application Details</h1>

        <!-- Display loan application details -->
        <table class="table table-bordered">
            <tr>
                <th>Loan Name</th>
                <td><?php echo htmlspecialchars($loan_name); ?></td>
            </tr>
            <tr>
                <th>Amount Requested</th>
                <td><?php echo number_format($amount_requested, 2); ?></td>
            </tr>
            <tr>
                <th>Application Status</th>
                <td><?php echo htmlspecialchars($application_status); ?></td>
            </tr>
            <tr>
                <th>Customer ID</th>
                <td><?php echo $customer_id; ?></td>
            </tr>
            <tr>
                <th>Feedback</th>
                <td><?php echo $feedback ? htmlspecialchars($feedback) : 'No feedback'; ?></td>
            </tr>
            <tr>
                <th>Assigned Officer</th>
                <td>
                    <?php
                    if ($assigned_officer_id) {
                        $sql_officer = "SELECT username FROM loanofficers WHERE id = ?";
                        $stmt_officer = $conn->prepare($sql_officer);
                        $stmt_officer->bind_param("i", $assigned_officer_id);
                        $stmt_officer->execute();
                        $stmt_officer->bind_result($officer_name);
                        $stmt_officer->fetch();
                        echo $officer_name;
                        $stmt_officer->close();
                    } else {
                        echo "Not assigned yet";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>Created At</th>
                <td><?php echo date("Y-m-d H:i:s", strtotime($created_at)); ?></td>
            </tr>
            <tr>
                <th>Updated At</th>
                <td><?php echo date("Y-m-d H:i:s", strtotime($updated_at)); ?></td>
            </tr>
        </table>

        <h2>Loan Disbursements</h2>

        <?php if ($stmt_disb->num_rows == 0) { ?>
            <div class="alert alert-info">No loan disbursements found for this application.</div>
        <?php } else { ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Amount Disbursed</th>
                        <th>Disbursement Date</th>
                        <th>Repayment Status</th>
                        <th>Transaction Image</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($stmt_disb->fetch()) { ?>
                        <tr>
                            <td><?php echo number_format($amount_disbursed, 2); ?></td>
                            <td><?php echo date("Y-m-d H:i:s", strtotime($disbursement_date)); ?></td>
                            <td>
                                <?php
                                if ($repayment_status == 'active') {
                                    echo '<span class="badge badge-warning">Active</span>';
                                } elseif ($repayment_status == 'closed') {
                                    echo '<span class="badge badge-success">Paid</span>';
                                    if ($transaction_image) {
                                        echo '<a href="../' . htmlspecialchars($transaction_image) . '" target="_blank"><img src="../' . htmlspecialchars($transaction_image) . '" alt="Transaction Image" width="100"></a>';
                                    }
                                } elseif ($repayment_status == 'overdue') {
                                    echo '<span class="badge badge-danger">Overdue</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ($transaction_image) {
                                    echo '<a href="../' . htmlspecialchars($transaction_image) . '" target="_blank"><img src="../' . htmlspecialchars($transaction_image) . '" alt="Transaction Image" width="100"></a>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>

    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<?php
// Close database connection
$stmt_disb->close();
$conn->close();
?>
