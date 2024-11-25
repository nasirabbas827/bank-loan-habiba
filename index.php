<?php
include('config.php');

// Fetch loan types from the database
$sql_loans = "SELECT * FROM loan_types";
$result_loans = $conn->query($sql_loans);

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bank Loan</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
 <style>
.jumbotron {
            height: 500px;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('./images/hotel.jpg');
            background-size: cover;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .jumbotron h1 {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .jumbotron p {
            font-size: 1.5rem;
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

<?php
include('navbar.php');
?>

<div class="jumbotron text-center">
    <h1>Welcome to the Bank Loan Portal</h1>
    <p>Manage and Track Loan Applications with Ease</p>
    <a href="login.php" class="btn btn-primary btn-lg">Apply For Loan</a>
</div>


<div class="container">
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


<footer class="mt-5 py-3 bg-light">
    <div class="container text-center">
        <p>&copy; 2024 Bank Loan Portal. All rights reserved.</p>
    </div>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
