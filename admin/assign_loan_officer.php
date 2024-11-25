<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch loan applications that are pending along with all relevant details
$sql_applications = "SELECT la.application_id, lt.loan_name, la.amount_requested, la.application_status, la.customer_id,
                            la.feedback, la.assigned_officer_id, la.created_at, la.updated_at
                     FROM loan_applications la
                     JOIN loan_types lt ON la.loan_type_id = lt.loan_type_id";


$result_applications = $conn->query($sql_applications);

// Fetch loan officers
$sql_officers = "SELECT id, username FROM loanofficers";
$result_officers = $conn->query($sql_officers);

// Check if form is submitted to assign an officer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["application_id"]) && isset($_POST["officer_id"])) {
    $application_id = $_POST["application_id"];
    $officer_id = $_POST["officer_id"];

    // Update loan application with the assigned officer
    $sql_update = "UPDATE loan_applications SET assigned_officer_id = ? WHERE application_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ii", $officer_id, $application_id);

    if ($stmt->execute()) {
        $success_message = "Officer assigned successfully!";
    } else {
        $error_message = "Error assigning officer: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Assign Loan Officer</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include('admin_navbar.php'); ?>

    <div class="container mt-5">
        <h1>Assign Loan Officer</h1>

        <?php if (isset($success_message)) { ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php } elseif (isset($error_message)) { ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php } ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Loan Type</th>
                    <th>Amount Requested</th>
                    <th>Customer ID</th>
                    <th>Feedback</th>
                    <th>Assigned Officer</th>
                    <th>Assign Officer</th>
                    <th>View Details</th> <!-- New column for view details -->
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_applications->fetch_assoc()) { 
                    // Fetch assigned officer details if assigned
                    $assigned_officer_name = "Not Assigned";
                    if ($row['assigned_officer_id']) {
                        $sql_officer = "SELECT username FROM loanofficers WHERE id = ?";
                        $stmt = $conn->prepare($sql_officer);
                        $stmt->bind_param("i", $row['assigned_officer_id']);
                        $stmt->execute();
                        $stmt->bind_result($assigned_officer_name);
                        $stmt->fetch();
                        $stmt->close();
                    }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['loan_name']); ?></td>
                        <td><?php echo number_format($row['amount_requested'], 2); ?></td>
                        <td><?php echo $row['customer_id']; ?></td>
                        <td><?php echo $row['feedback'] ? htmlspecialchars($row['feedback']) : 'No feedback'; ?></td>
                        <td><?php echo $assigned_officer_name; ?></td>
                        <td>
                            <form method="POST" action="assign_loan_officer.php">
                                <input type="hidden" name="application_id" value="<?php echo $row['application_id']; ?>" />
                                <select name="officer_id" class="form-control" required>
                                    <option value="">Select Officer</option>
                                    <?php while ($officer = $result_officers->fetch_assoc()) { ?>
                                        <option value="<?php echo $officer['id']; ?>"><?php echo htmlspecialchars($officer['username']); ?></option>
                                    <?php } ?>
                                </select>
                                <button type="submit" class="btn btn-primary mt-2">Assign Officer</button>
                            </form>
                        </td>
                        <!-- View Details Button -->
                        <td><a href="view_application_details.php?application_id=<?php echo $row['application_id']; ?>" class="btn btn-info btn-sm">View Details</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>
