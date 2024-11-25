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

// Fetch the user data (username) from the database
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 1) {
    $stmt->bind_result($username);
    $stmt->fetch();
} else {
    // If user data is not found, redirect to login page
    header("location: index.php");
    exit;
}

$stmt->close();

// Fetch loan types from the database
$sql_loans = "SELECT * FROM loan_types";
$result_loans = $conn->query($sql_loans);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Home Page</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
    
    <style>
        .dashboard-welcome {
            background-color: #f8f9fa;
            padding: 30px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .dashboard-welcome h2 {
            font-size: 2rem;
            color: #007bff;
        }

        .dashboard-welcome p {
            font-size: 1.2rem;
            margin-top: 10px;
        }



        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .btn-container .btn {
            width: 30%;
            padding: 15px;
        }

        .loan-table {
            margin-top: 30px;
        }

        .loan-table th, .loan-table td {
            text-align: center;
        }

        .loan-table th {
            background-color: #007bff;
            color: white;
        }

        .loan-table td {
            background-color: #f8f9fa;
        }

        .loan-table tr:hover td {
            background-color: #e9ecef;
        }
    </style>
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <div class="dashboard-welcome">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <p>This is your dashboard. From here, you can manage your account settings, view your details, and more.</p>
        </div>

        <!-- Buttons for actions -->
        <div class="btn-container mt-3">
    <button class="btn btn-primary m-2" onclick="window.location.href='request_loan.php'">Request Loan</button>
    <button class="btn btn-secondary m-2" onclick="window.location.href='view_loan_application.php'">View Loan Application</button>
    <button class="btn btn-warning m-2" onclick="window.location.href='view_messages.php'">View Messages</button> <!-- New Button -->
</div>
        <!-- Loan Types Table -->
        <div class="loan-table">
            <h3>Available Loan Types</h3>
            <?php if ($result_loans->num_rows > 0) { ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Loan Name</th>
                            <th>Description</th>
                            <th>Interest Rate</th>
                            <th>Repayment Terms</th>
                            <th>Max Amount</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_loans->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['loan_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['interest_rate']); ?>%</td>
                                <td><?php echo htmlspecialchars($row['repayment_terms']); ?> months</td>
                                <td><?php echo htmlspecialchars($row['max_amount']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($row['updated_at']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>No loan types available at the moment.</p>
            <?php } ?>
        </div>
    </div>

    <!-- Bootstrap JS & dependencies -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
