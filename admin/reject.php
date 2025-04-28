<?php
session_start();
include('../config/db.php');

// Check if the user is logged in and has the right role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'approver')) {
    header('Location: ../login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $leave_id = intval($_POST['id']);
    
    // Check if a reason is provided
    if (!isset($_POST['reason']) || empty(trim($_POST['reason']))) {
        echo json_encode(['status' => 'error', 'message' => 'Rejection reason is required.']);
        exit;
    }

    $reason = trim($_POST['reason']);
    
    // Start transaction to ensure both operations succeed or fail together
    $conn->begin_transaction();
    
    try {
        // Insert the rejection reason into the database
        // $stmt_reason = $conn->prepare("UPDATE leave_requests SET reason = ? WHERE id = ?");
        // $stmt_reason->bind_param("si", $reason);
        // $stmt_reason->execute();
        // $stmt_reason->close();
        
        // Update leave status to rejected
        $stmt = $conn->prepare("UPDATE leave_requests SET rejection_reason = ?,  status = 'Rejected' WHERE id = ?");
        $stmt->bind_param("si", $reason ,$leave_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $conn->commit();
            echo "
                <script>
                    alert('Leave request rejected successfully.');
                    setTimeout(function() {
                        window.location.href = 'manage_leaves.php';
                    }, 1500);
                </script>
            ";
        } else {
            throw new Exception("No changes made to leave request.");
        }
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
        
    exit;
}

// If it's a GET request, display the rejection modal form
if (isset($_GET['id'])) {
    $leave_id = intval($_GET['id']);
    
    // Fetch leave request details for display
    $stmt = $conn->prepare("SELECT l.*, u.name FROM leave_requests l 
                           JOIN users u ON l.user_id = u.id 
                           WHERE l.id = ?");
    $stmt->bind_param("i", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "Leave request not found.";
        exit;
    }
    
    $leave = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reject Leave Request</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h4>Reject Leave Request</h4>
            </div>
            <div class="card-body">
                <?php if (isset($leave)): ?>
                <div class="leave-details mb-4">
                    <h5>Leave Request Details</h5>
                    <p><strong>Employee:</strong> <?php echo htmlspecialchars($leave['name']); ?></p>
                    <p><strong>From:</strong> <?php echo htmlspecialchars($leave['start_date']); ?></p>
                    <p><strong>To:</strong> <?php echo htmlspecialchars($leave['end_date']); ?></p>
                    <p><strong>Type:</strong> <?php echo htmlspecialchars($leave['leave_type']); ?></p>
                    <p><strong>Reason:</strong> <?php echo htmlspecialchars($leave['reason']); ?></p>
                </div>
                
                <form id="rejectForm">
                    <input type="hidden" name="id" value="<?php echo $leave_id; ?>">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Rejection Reason:</label>
                        <textarea class="form-control" name="reason" id="reason" rows="4" required></textarea>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                        <a href="leaves.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
                
                <div id="responseMessage" class="mt-3"></div>
                <?php else: ?>
                <div class="alert alert-danger">Invalid leave request ID.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Include Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $("#rejectForm").on("submit", function(e) {
            e.preventDefault();
            
            $.ajax({
                type: "POST",
                url: "reject.php",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        $("#responseMessage").html('<div class="alert alert-success">' + response.message + '</div>');
                        setTimeout(function() {
                            window.location.href = "leaves.php";
                        }, 2000);
                    } else {
                        $("#responseMessage").html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $("#responseMessage").html('<div class="alert alert-danger">An error occurred while processing your request.</div>');
                }
            });
        });
    });
    </script>
</body>
</html>