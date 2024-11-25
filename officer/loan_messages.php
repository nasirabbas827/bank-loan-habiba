<?php
session_start();
include('config.php');

// Check if the user is logged in as an officer
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "officer") {
    header("Location: officerlogin.php");
    exit;
}

// Get the officer's ID from the session
$officer_id = $_SESSION["id"];

// Get the application ID from the URL
$application_id = $_GET['application_id'] ?? null;
if ($application_id === null) {
    echo "No application ID provided.";
    exit;
}

// Fetch the application and customer details
$sql_application = "SELECT la.application_id, la.amount_requested, u.full_name AS customer_name, u.id AS customer_id
                    FROM loan_applications la
                    JOIN users u ON la.customer_id = u.id
                    WHERE la.application_id = ?";
$stmt = $conn->prepare($sql_application);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result_application = $stmt->get_result();
$application = $result_application->fetch_assoc();
$stmt->close();

// Handle message submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $message = $_POST['message'];
    $customer_id = $application['customer_id'];

    $sql_insert_message = "INSERT INTO loan_messages (customer_id, officer_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert_message);
    $stmt->bind_param("iis", $customer_id, $officer_id, $message);

    if ($stmt->execute()) {
        $success_message = "Message sent successfully!";
    } else {
        $error_message = "Error sending message: " . $conn->error;
    }
    $stmt->close();
}

// Fetch message history for the application
$sql_messages = "SELECT lm.message, lm.log_date, lo.username AS officer_name
                 FROM loan_messages lm
                 JOIN loanofficers lo ON lm.officer_id = lo.id
                 WHERE lm.customer_id = ?
                 ORDER BY lm.log_date DESC";
$stmt = $conn->prepare($sql_messages);
$stmt->bind_param("i", $application['customer_id']);
$stmt->execute();
$result_messages = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Loan Messages</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h3>Messages for Application ID: <?php echo htmlspecialchars($application['application_id']); ?> - Customer: <?php echo htmlspecialchars($application['customer_name']); ?></h3>

        <!-- Display success or error messages -->
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

        <!-- Send Message Form -->
        <div class="card">
            <div class="card-header">
                <h5>Send a Message to Customer</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="loan_messages.php?application_id=<?php echo $application_id; ?>">
                    <div class="form-group">
                        <textarea class="form-control" name="message" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>
