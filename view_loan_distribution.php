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

// Get the application ID from the URL parameter
if (isset($_GET['application_id'])) {
    $application_id = $_GET['application_id'];

    // Fetch loan disbursements for the selected loan application
    $sql_disbursements = "SELECT ld.disbursement_id, la.application_id, ld.amount_disbursed, ld.disbursement_date, ld.repayment_status, ld.transaction_image, ld.created_at, ld.updated_at
                          FROM loan_disbursements ld
                          JOIN loan_applications la ON ld.application_id = la.application_id
                          WHERE la.customer_id = ? AND la.application_id = ?";
    $stmt = $conn->prepare($sql_disbursements);
    $stmt->bind_param("ii", $user_id, $application_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($disbursement_id, $application_id, $amount_disbursed, $disbursement_date, $repayment_status, $transaction_image, $created_at, $updated_at);
} else {
    header("location: view_loan_application.php");
    exit;
}

// Handle the image upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["transaction_image"])) {
    if (isset($_POST["disbursement_id"])) {
        $disbursement_id = $_POST["disbursement_id"];

        // Check if a file is uploaded
        if ($_FILES["transaction_image"]["error"] == 0) {
            // Get file details
            $file_name = $_FILES["transaction_image"]["name"];
            $file_tmp = $_FILES["transaction_image"]["tmp_name"];
            $file_size = $_FILES["transaction_image"]["size"];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            // Set the destination directory for the uploaded image
            $upload_dir = 'uploads/';
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_file_name;

            // Check if the file is an image
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($file_ext), $allowed_exts)) {
                // Move the file to the destination folder
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Update the repayment status to 'paid' and save the transaction image in the database
                    $sql_update = "UPDATE loan_disbursements SET repayment_status = 'closed', transaction_image = ? WHERE disbursement_id = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("si", $upload_path, $disbursement_id);

                    if ($stmt_update->execute()) {
                        echo "Record updated successfully.";
                    } else {
                        echo "Error updating record: " . $stmt_update->error;
                    }

                    // Redirect to the same page to reflect the changes
                    header("Location: view_loan_distribution.php?application_id=" . $application_id);
                    exit;
                } else {
                    echo "<div class='alert alert-danger'>File upload failed.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Only image files are allowed.</div>";
            }
        } else {
            echo "File upload error: " . $_FILES["transaction_image"]["error"];
        }
    } else {
        echo "<div class='alert alert-danger'>Invalid disbursement ID.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Loan Distribution</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container mt-5">
        <h2>Loan Distribution for Application ID: <?php echo $application_id; ?></h2>

        <?php if ($stmt->num_rows == 0) { ?>
            <div class="alert alert-info">No loan distribution found for this application.</div>
        <?php } else { ?>
            <div class="loan-table-container">
                <table class="table table-bordered loan-table">
                    <thead>
                        <tr>
                            <th>Amount Disbursed</th>
                            <th>Disbursement Date</th>
                            <th>Repayment Status</th>
                            <th>Created At</th>
                            <th>Last Updated</th>
                            <th>Transaction Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($stmt->fetch()) { ?>
                            <tr>
                                <td><?php echo number_format($amount_disbursed, 2); ?></td>
                                <td><?php echo date("Y-m-d H:i:s", strtotime($disbursement_date)); ?></td>
                                <td>
                                    <?php
                                    if ($repayment_status == 'active') {
                                        echo '<span class="badge badge-warning">Active</span>';
                                        echo '<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#payNowModal-' . $disbursement_id . '">Pay Now</button>';
                                    } elseif ($repayment_status == 'closed') {
                                        echo '<span class="badge badge-success">Paid</span>';
                                    } elseif ($repayment_status == 'overdue') {
                                        echo '<span class="badge badge-danger">Overdue</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo date("Y-m-d H:i:s", strtotime($created_at)); ?></td>
                                <td><?php echo date("Y-m-d H:i:s", strtotime($updated_at)); ?></td>
                                <td>
                                    <?php
                                    if ($transaction_image) {
                                        echo '<a href="' . htmlspecialchars($transaction_image) . '" target="_blank"><img src="' . htmlspecialchars($transaction_image) . '" alt="Transaction Image" width="100"></a>';
                                    }
                                    ?>
                                </td>
                            </tr>

                            <!-- Modal for Pay Now -->
                            <div class="modal fade" id="payNowModal-<?php echo $disbursement_id; ?>" tabindex="-1" role="dialog" aria-labelledby="payNowModalLabel-<?php echo $disbursement_id; ?>" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="payNowModalLabel-<?php echo $disbursement_id; ?>">Upload Transaction Image</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" enctype="multipart/form-data">
                                                <div class="form-group">
                                                    <label for="transaction_image">Choose Transaction Image</label>
                                                    <input type="file" name="transaction_image" class="form-control" required>
                                                </div>
                                                <input type="hidden" name="disbursement_id" value="<?php echo $disbursement_id; ?>">
                                                <button type="submit" class="btn btn-primary">Upload and Mark as Paid</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

    </div>

    <!-- Bootstrap JS & dependencies -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
