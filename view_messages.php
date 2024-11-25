<?php
include('config.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION["id"];

// Fetch the username from the database
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 1) {
    $stmt->bind_result($username);
    $stmt->fetch();
} else {
    header("location: index.php");
    exit;
}

$stmt->close();

// Fetch the messages sent to the user
$sql_messages = "SELECT lm.message, lm.log_date, lo.username AS officer_name
                 FROM loan_messages lm
                 JOIN loanofficers lo ON lm.officer_id = lo.id
                 WHERE lm.customer_id = ?
                 ORDER BY lm.log_date DESC";
$stmt = $conn->prepare($sql_messages);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_messages = $stmt->get_result();
$stmt->close();

// Handle reply submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reply_message'])) {
    $reply_message = $_POST['reply_message'];
    $officer_id = $_POST['officer_id'];

    $sql_insert_reply = "INSERT INTO loan_messages (customer_id, officer_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert_reply);
    $stmt->bind_param("iis", $user_id, $officer_id, $reply_message);

    if ($stmt->execute()) {
        $success_message = "Reply sent successfully!";
    } else {
        $error_message = "Error sending reply: " . $conn->error;
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Messages</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h3>Messages from Loan Officers</h3>

        <!-- Success or Error message display -->
        <?php if (isset($success_message)) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } elseif (isset($error_message)) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <!-- Display message history -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Message History</h5>
            </div>
            <div class="card-body">
                <?php if ($result_messages->num_rows > 0) { ?>
                    <ul class="list-unstyled">
                        <?php while ($row = $result_messages->fetch_assoc()) { ?>
                            <li class="media mb-3">
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1"><?php echo htmlspecialchars($row['officer_name']); ?> <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($row['log_date'])); ?></small></h6>
                                    <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p>No messages found.</p>
                <?php } ?>
            </div>
        </div>

        <!-- Reply Form -->
        <div class="card">
            <div class="card-header">
                <h5>Reply to Loan Officer</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="view_messages.php">
                    <div class="form-group">
                        <textarea class="form-control" name="reply_message" rows="4" required></textarea>
                    </div>
                    <input type="hidden" name="officer_id" value="1"> <!-- Set the officer's ID here dynamically based on the message -->
                    <button type="submit" class="btn btn-primary mt-3">Send Reply</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
