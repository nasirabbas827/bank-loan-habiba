<?php
session_start();
include('config.php');

// Check if the user is logged in as an officer
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "officer") {
    header("Location: officerlogin.php");
    exit;
}

// Fetch the application ID from session or URL (if provided in the URL)
$application_id_session = $_SESSION['application_id'] ?? null; // From session if set

// If application_id is passed in the URL, update the session
if (isset($_GET['application_id']) && is_numeric($_GET['application_id'])) {
    $_SESSION['application_id'] = $_GET['application_id'];
    $application_id_session = $_GET['application_id'];
}

// Handle the form submission for disbursement (create or edit)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['amount_disbursed'], $_POST['repayment_status'], $_POST['disbursement_date'])) {
    $application_id = $_POST['application_id'] ?? $application_id_session; // Ensure it's set
    $amount_disbursed = $_POST['amount_disbursed'];
    $repayment_status = $_POST['repayment_status'];
    $disbursement_date = $_POST['disbursement_date'];

    // Insert the disbursement record into the database (for create)
    if (isset($_POST['disbursement_id']) && $_POST['disbursement_id'] != '') {
        // Edit an existing disbursement
        $disbursement_id = $_POST['disbursement_id'];
        $sql_update = "UPDATE loan_disbursements 
                       SET amount_disbursed = ?, repayment_status = ?, disbursement_date = ?, updated_at = NOW() 
                       WHERE disbursement_id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("dssi", $amount_disbursed, $repayment_status, $disbursement_date, $disbursement_id);
        if ($stmt->execute()) {
            $success_message = "Loan disbursement updated successfully!";
        } else {
            $error_message = "Error updating loan disbursement: " . $conn->error;
        }
        $stmt->close();
    } else {
        // Insert a new disbursement record
        $sql_insert = "INSERT INTO loan_disbursements (application_id, amount_disbursed, disbursement_date, repayment_status, created_at, updated_at)
                       VALUES (?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("idss", $application_id, $amount_disbursed, $disbursement_date, $repayment_status);

        if ($stmt->execute()) {
            $success_message = "Loan disbursed successfully!";
        } else {
            $error_message = "Error disbursing loan: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch loan disbursement records for the specific application ID
$sql_disbursements = "SELECT ld.disbursement_id, la.application_id, la.amount_requested, ld.amount_disbursed, ld.repayment_status, ld.disbursement_date 
                      FROM loan_disbursements ld
                      JOIN loan_applications la ON ld.application_id = la.application_id
                      WHERE ld.application_id = ?";
$stmt = $conn->prepare($sql_disbursements);
$stmt->bind_param("i", $application_id_session);
$stmt->execute();
$result_disbursements = $stmt->get_result();

// Handle delete disbursement
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $sql_delete = "DELETE FROM loan_disbursements WHERE disbursement_id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    header("Location: loan_disbursement.php?application_id=" . $application_id_session); // Refresh the page after deletion
    exit;
}

// Handle edit disbursement
if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];

    $sql_edit = "SELECT * FROM loan_disbursements WHERE disbursement_id = ?";
    $stmt = $conn->prepare($sql_edit);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Loan Disbursement</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f8ff;
        }
    </style>
</head>

<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <div class="row">
            <!-- Left Section (Disbursement Records) -->
            <div class="col-lg-8 col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-list-alt me-2"></i>Loan Disbursement Records</h3>
                    </div>
                    <div class="card-body">
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

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Application ID</th>
                                        <th>Amount Requested</th>
                                        <th>Amount Disbursed</th>
                                        <th>Repayment Status</th>
                                        <th>Disbursement Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result_disbursements->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?php echo $row['application_id']; ?></td>
                                            <td>Pkr <?php echo number_format($row['amount_requested'], 2); ?></td>
                                            <td>Pkr <?php echo number_format($row['amount_disbursed'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($row['repayment_status'] == 'active') ? 'success' : (($row['repayment_status'] == 'overdue') ? 'danger' : 'secondary'); ?>">
                                                    <?php echo ucfirst($row['repayment_status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($row['disbursement_date'])); ?></td>
                                            <td>
                                                <a href="loan_disbursement.php?delete_id=<?php echo $row['disbursement_id']; ?>&application_id=<?php echo $application_id_session; ?>" class="btn btn-sm btn-danger mb-2" onclick="return confirm('Are you sure you want to delete this record?');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                                <a href="loan_disbursement.php?edit_id=<?php echo $row['disbursement_id']; ?>&application_id=<?php echo $application_id_session; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Section (Create / Edit Disbursement) -->
            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-money-bill-alt me-2"></i><?php echo isset($edit_data) ? 'Edit' : 'Create'; ?> Loan Disbursement</h3>
                    </div>
                    <div class="card-body">
                        <form action="loan_disbursement.php" method="POST">
                            <input type="hidden" name="application_id" value="<?php echo $application_id_session; ?>">
                            <?php if (isset($edit_data)) { ?>
                                <input type="hidden" name="disbursement_id" value="<?php echo $edit_data['disbursement_id']; ?>">
                            <?php } ?>
                            <div class="mb-3">
                                <label for="amount_disbursed" class="form-label">Amount Disbursed</label>
                                <input type="number" name="amount_disbursed" id="amount_disbursed" class="form-control" value="<?php echo isset($edit_data) ? $edit_data['amount_disbursed'] : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="repayment_status" class="form-label">Repayment Status</label>
                                <select class="form-control" name="repayment_status" id="repayment_status" class="form-select" required>
                                    <option value="active" <?php echo (isset($edit_data) && $edit_data['repayment_status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="overdue" <?php echo (isset($edit_data) && $edit_data['repayment_status'] == 'overdue') ? 'selected' : ''; ?>>Overdue</option>
                                    <option value="pending" <?php echo (isset($edit_data) && $edit_data['repayment_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="disbursement_date" class="form-label">Disbursement Date</label>
                                <input type="date" name="disbursement_date" id="disbursement_date" class="form-control" value="<?php echo isset($edit_data) ? $edit_data['disbursement_date'] : ''; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary"><?php echo isset($edit_data) ? 'Update' : 'Create'; ?> Disbursement</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>
