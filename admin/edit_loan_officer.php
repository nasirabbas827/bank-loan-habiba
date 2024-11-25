<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$officer_id = $_GET['id'] ?? null;

if (!$officer_id) {
    header("Location: view_loan_officers.php");
    exit;
}

// Fetch loan officer details
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $query = "SELECT * FROM loanofficers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $officer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $officer = $result->fetch_assoc();

    if (!$officer) {
        header("Location: view_loan_officers.php");
        exit;
    }
}

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $updated_at = date('Y-m-d H:i:s');

    if (empty($username) || empty($email) || empty($phone_number)) {
        $error_message = "All fields are required.";
    } else {
        $update_query = "UPDATE loanofficers SET username = ?, email = ?, phone_number = ?, updated_at = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssi", $username, $email, $phone_number, $updated_at, $officer_id);

        if ($stmt->execute()) {
            header("Location: view_loan_officers.php");
            exit;
        } else {
            $error_message = "Error: Could not update the loan officer.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Loan Officer</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>
<div class="container mt-5">
    <h1 class="text-center">Edit Loan Officer</h1>
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $officer['username']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $officer['email']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo $officer['phone_number']; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Update Loan Officer</button>
            </form>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
