<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch total counts for users, loan officers, loan types, loan applications
$sql_users = "SELECT COUNT(*) FROM users";
$sql_loanofficers = "SELECT COUNT(*) FROM loanofficers";
$sql_loan_types = "SELECT COUNT(*) FROM loan_types";
$sql_loan_applications = "SELECT COUNT(*) FROM loan_applications";

// Execute queries
$result_users = $conn->query($sql_users);
$result_loanofficers = $conn->query($sql_loanofficers);
$result_loan_types = $conn->query($sql_loan_types);
$result_loan_applications = $conn->query($sql_loan_applications);

// Fetch the counts
$total_users = $result_users->fetch_row()[0];
$total_loanofficers = $result_loanofficers->fetch_row()[0];
$total_loan_types = $result_loan_types->fetch_row()[0];
$total_loan_applications = $result_loan_applications->fetch_row()[0];

// Fetch loan payment data for the graph (this example uses monthly data)
$sql_payments = "SELECT DATE_FORMAT(ld.disbursement_date, '%Y-%m') AS month, 
                        SUM(ld.amount_disbursed) AS total_disbursed
                 FROM loan_disbursements ld
                 GROUP BY month
                 ORDER BY month ASC";
$result_payments = $conn->query($sql_payments);

// Prepare data for the graph
$payment_months = [];
$payment_values = [];
while ($row = $result_payments->fetch_assoc()) {
    $payment_months[] = $row['month'];
    $payment_values[] = $row['total_disbursed'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5">
        <h1>Admin Dashboard</h1>

        <!-- Dashboard Summary Section -->
        <div class="row">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <p class="card-text"><?php echo $total_users; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Loan Officers</h5>
                        <p class="card-text"><?php echo $total_loanofficers; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Loan Types</h5>
                        <p class="card-text"><?php echo $total_loan_types; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Loan Applications</h5>
                        <p class="card-text"><?php echo $total_loan_applications; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="mt-4">Loan Payments Overview</h2>
        <canvas id="paymentChart"></canvas>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Chart.js for dynamic graph -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Prepare data for the chart
        var months = <?php echo json_encode($payment_months); ?>;
        var payments = <?php echo json_encode($payment_values); ?>;

        // Create the chart
        var ctx = document.getElementById('paymentChart').getContext('2d');
        var paymentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Total Disbursements',
                    data: payments,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</body>

</html>

<?php
// Close the connection
$conn->close();
?>
