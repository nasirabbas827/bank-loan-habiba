<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle deletion of a loan officer
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Delete loan officer from the database
    $delete_query = "DELETE FROM loanofficers WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<script>
                alert('Loan officer deleted successfully.');
                window.location.href = 'view_loan_officers.php';
              </script>";
    } else {
        echo "<script>alert('Error: Unable to delete loan officer.');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Loan Officers</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="container mt-5">
    <h1 class="text-center">Loan Officers</h1>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch all loan officers
                $query = "SELECT * FROM loanofficers ORDER BY created_at DESC";
                $result = $conn->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['username']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['phone_number']}</td>
                                <td>{$row['created_at']}</td>
                                <td>
                                    <a href='edit_loan_officer.php?id={$row['id']}' class='btn btn-warning btn-sm'>Edit</a>
                                    <a href='view_loan_officers.php?delete_id={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this loan officer?\");' class='btn btn-danger btn-sm'>Delete</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No loan officers found.</td></tr>";
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
