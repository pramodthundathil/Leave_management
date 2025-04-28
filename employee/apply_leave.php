<?php
session_start();
include('../config/db.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leave_type_id = $_POST['leave_type_id']; // Changed from leave_type to leave_type_id
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];
    // Calculate the number of days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $days = $interval->days + 1; // Include the start date as a full day

    // Validate dates
    if (strtotime($start_date) > strtotime($end_date)) {
        $error = "<div style='background-color: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 8px; margin: 0 auto 20px auto; display: flex; align-items: center; gap: 10px; max-width: 500px;'>
                  <i class='fas fa-exclamation-circle' style='color: #ef4444; font-size: 1.2rem;'></i>
                  Error: Start date cannot be after end date.
                </div>";
    } else {
        // Handle medical certificate upload
        $cert_path = null;
        if (!empty($_FILES['medical_cert']['name'])) {
            $upload_dir = __DIR__ . '/../uploads/certs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $tmp  = $_FILES['medical_cert']['tmp_name'];
            $name = basename($_FILES['medical_cert']['name']);
            $ext  = pathinfo($name, PATHINFO_EXTENSION);
            $newName = uniqid('cert_') . '.' . $ext;
            
            if (move_uploaded_file($tmp, $upload_dir . $newName)) {
                // store web‐accessible path
                $cert_path = 'uploads/certs/' . $newName;
            }
        }

        // Insert leave request into the database
        $sql = "INSERT INTO leave_requests 
                  (user_id, leave_type_id, start_date, end_date, reason, medical_certificate, days, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iissssi", // Changed from issssss to iissssi (i for integer leave_type_id)
            $user_id,
            $leave_type_id, // Changed from leave_type to leave_type_id
            $start_date,
            $end_date,
            $reason,
            $cert_path,
            $days
        );

        if ($stmt->execute()) {
            $message = "<div style='background-color: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin: 0 auto 20px auto; display: flex; align-items: center; gap: 10px; max-width: 500px;'>
                          <i class='fas fa-check-circle' style='color: #10b981; font-size: 1.2rem;'></i>
                          Leave request submitted successfully.
                        </div>";
        } else {
            $error = "<div style='background-color: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 8px; margin: 0 auto 20px auto; display: flex; align-items: center; gap: 10px; max-width: 500px;'>
                        <i class='fas fa-exclamation-circle' style='color: #ef4444; font-size: 1.2rem;'></i>
                        Failed to submit leave request: " . $stmt->error . "
                      </div>";
        }
    }
}
?>
<?php include('header.php'); ?>
<div style="width: 100%; max-width: 600px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); padding: 40px;margin:auto; margin-top: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="background: #4f6cea; width: 60px; height: 60px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
            <i class="fas fa-calendar-plus" style="color: white; font-size: 1.5rem;"></i>
        </div>
        <h2 style="margin: 0; color: #2d3748; font-size: 1.8rem;">Apply for Leave</h2>
        
        <?php 
        if (!empty($error)) echo $error;
        if (!empty($message)) echo $message;
        ?>
    </div>

    <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;" onsubmit="return validateDates()">
    <div>
    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">Leave Type</label>
    <select name="leave_type_id" required style="width: 100%; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0; background-color: #f8fafc; transition: all 0.3s; font-size: 1rem; color: #334155;"
            onfocus="this.style.borderColor='#4f6cea'; this.style.boxShadow='0 0 0 3px rgba(79, 108, 234, 0.1)';" 
            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
        <option value="" disabled selected>Select leave type</option>
        <?php
        // Query to get leave types from the database
        $leaveTypesQuery = "SELECT LeaveTypeID, LeaveName FROM leavetypes";
        $leaveTypesResult = mysqli_query($conn, $leaveTypesQuery);
        
        // Check if query was successful
        if ($leaveTypesResult) {
            // Loop through results and create options
            while($row = mysqli_fetch_assoc($leaveTypesResult)) {
                echo '<option value="' . $row["LeaveTypeID"] . '">' . $row["LeaveName"] . '</option>';
            }
        }
        ?>
    </select>
</div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">Start Date</label>
                <input type="date" name="start_date" id="start_date" required 
                       style="width: 100%; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0; background-color: #f8fafc; transition: all 0.3s; font-size: 1rem; color: #334155;"
                       onfocus="this.style.borderColor='#4f6cea'; this.style.boxShadow='0 0 0 3px rgba(79, 108, 234, 0.1)';" 
                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">End Date</label>
                <input type="date" name="end_date" id="end_date" required 
                       style="width: 100%; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0; background-color: #f8fafc; transition: all 0.3s; font-size: 1rem; color: #334155;"
                       onfocus="this.style.borderColor='#4f6cea'; this.style.boxShadow='0 0 0 3px rgba(79, 108, 234, 0.1)';" 
                       onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
            </div>
        </div>

        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">Reason</label>
            <textarea name="reason" rows="4" required 
                      style="width: 100%; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0; background-color: #f8fafc; transition: all 0.3s; font-size: 1rem; color: #334155; resize: vertical; min-height: 100px;"
                      onfocus="this.style.borderColor='#4f6cea'; this.style.boxShadow='0 0 0 3px rgba(79, 108, 234, 0.1)';" 
                      onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
                      placeholder="Please provide details about your leave request..."></textarea>
        </div>

        <!-- Medical Certificate upload -->
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">
              Medical Certificate <small>(optional)</small>
            </label>
            <input 
              type="file" 
              name="medical_cert" 
              accept=".jpg,.jpeg,.png,.pdf"
              style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #f8fafc;"
            />
        </div>

        <button type="submit" 
                style="padding: 14px 20px; background: linear-gradient(135deg, #4f6cea 0%, #3a56b2 100%); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;"
                onmouseover="this.style.opacity='0.9'; this.style.transform='translateY(-2px)';" 
                onmouseout="this.style.opacity='1'; this.style.transform='translateY(0)';">
            <i class="fas fa-paper-plane"></i> Submit Request
        </button>
    </form>
</div>

<script>
function validateDates() {
    const startDate = new Date(document.getElementById('start_date').value);
    const endDate = new Date(document.getElementById('end_date').value);
    if (startDate > endDate) {
        alert('Error: Start date cannot be after end date.');
        return false;
    }
    return true;
}


    document.addEventListener("DOMContentLoaded", function () {
        const today = new Date().toISOString().split("T")[0];
        const startDateInput = document.getElementById("start_date");
        const endDateInput = document.getElementById("end_date");

        // Set minimum start date as today
        startDateInput.setAttribute("min", today);

        // Update end date minimum based on start date selection
        startDateInput.addEventListener("change", function () {
            endDateInput.value = ''; // clear previously selected end date
            endDateInput.setAttribute("min", this.value);
        });
    });


</script>

<?php include('footer.php'); ?>
