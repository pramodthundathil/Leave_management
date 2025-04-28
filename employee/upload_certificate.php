<?php
session_start();
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['certificate'])) {
    $employee_id = $_SESSION['user_id']; // make sure session is set
    $file_name = $_FILES['certificate']['name'];
$file_tmp = $_FILES['certificate']['tmp_name'];
$target_dir = __DIR__ . "/uploads/";

if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true); // create if not exists
}

$file_path = $target_dir . basename($file_name);

if (move_uploaded_file($file_tmp, $file_path)) {
    $stmt = $conn->prepare("INSERT INTO employee_certificates (employee_id, file_name, file_path) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $employee_id, $file_name, $file_path);

    if ($stmt->execute()) {
        $message = "Certificate uploaded successfully!";
    } else {
        $message = "Database error: " . $stmt->error;
    }

    $stmt->close();
} else {
    $message = "Failed to upload file.";
}

}
?>


<?php include('header.php'); ?>
<div style="width: 100%; max-width: 600px; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08); padding: 40px; margin: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="background: #4f6cea; width: 60px; height: 60px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
            <i class="fas fa-file-upload" style="color: white; font-size: 1.5rem;"></i>
        </div>
        <h2 style="margin: 0; color: #2d3748; font-size: 1.8rem;">Upload Certificate</h2>
    </div>

    <?php if (!empty($message)): ?>
        <div style="background-color: <?php echo strpos($message, 'successfully') !== false ? '#d1fae5' : '#fee2e2'; ?>; 
             color: <?php echo strpos($message, 'successfully') !== false ? '#065f46' : '#b91c1c'; ?>; 
             padding: 12px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas <?php echo strpos($message, 'successfully') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>" 
               style="color: <?php echo strpos($message, 'successfully') !== false ? '#10b981' : '#ef4444'; ?>; font-size: 1.2rem;"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
        <div>
            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">Select Certificate (PDF, JPG, PNG):</label>
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="padding: 12px 15px; background: #f8fafc; border: 1px dashed #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px;"
                      onmouseover="this.style.borderColor='#4f6cea'; this.style.backgroundColor='#f0f4ff';" 
                      onmouseout="this.style.borderColor='#e2e8f0'; this.style.backgroundColor='#f8fafc';">
                    <i class="fas fa-cloud-upload-alt" style="color: #4f6cea;"></i>
                    <span>Choose File</span>
                    <input type="file" name="certificate" accept=".pdf, .jpg, .jpeg, .png" required 
                           style="display: none;" onchange="updateFileName(this)">
                </label>
                <span id="file-name" style="color: #64748b; font-size: 0.9rem;">No file selected</span>
            </div>
        </div>

        <button type="submit" 
                style="padding: 14px 20px; background: linear-gradient(135deg, #4f6cea 0%, #3a56b2 100%); color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;"
                onmouseover="this.style.opacity='0.9'; this.style.transform='translateY(-2px)';" 
                onmouseout="this.style.opacity='1'; this.style.transform='translateY(0)';">
            <i class="fas fa-upload"></i> Upload Certificate
        </button>
    </form>

    <div style="text-align: center; margin-top: 30px;">
        <a href="dashboard.php" style="color: #4f6cea; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 6px;"
           onmouseover="this.style.textDecoration='underline';" 
           onmouseout="this.style.textDecoration='none';">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<script>
function updateFileName(input) {
    const fileName = input.files[0] ? input.files[0].name : 'No file selected';
    document.getElementById('file-name').textContent = fileName;
}
</script>

<?php include('footer.php'); ?>
