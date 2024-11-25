<?php
session_start();
include('config.php');

// Check if the user is already logged in, redirect to homepage if so
if (isset($_SESSION["usertype"]) && $_SESSION["usertype"] === "officer") {
    header("Location: officer_home.php");
    exit;
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check if username and password are provided
    if (!empty($username) && !empty($password)) {
        // Prepare and execute the query to check if the officer exists
        $sql = "SELECT id, username, password FROM loanofficers WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        // If officer found, verify the password
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $db_username, $db_password);
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $db_password)) {
                // Password is correct, set session variables
                $_SESSION["usertype"] = "officer";
                $_SESSION["id"] = $id;
                $_SESSION["username"] = $db_username;

                // Redirect to officer's homepage
                header("Location: officer/officer_home.php");
                exit;
            } else {
                $error_message = "Invalid username or password.";
            }
        } else {
            $error_message = "Invalid username or password.";
        }
        $stmt->close();
    } else {
        $error_message = "Please enter both username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Officer Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container h2 {
            font-size: 1.8em;
            margin-bottom: 20px;
        }
        .form-container .form-group label {
            font-weight: 600;
        }
        .invalid-feedback {
            font-size: 0.9em;
            color: #dc3545;
        }
        .form-container .btn {
            width: 100%;
            padding: 12px;
        }
        .form-container p {
            margin-top: 20px;
        }
        .form-container .alert {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h2 class="text-center">Officer Login</h2>
        <?php if (isset($error_message)) { ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php } ?>

        <form action="officerlogin.php" method="POST" class="form-container">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
