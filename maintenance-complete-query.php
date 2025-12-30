<?php
session_start();
include 'includes/connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Maintenance_Technician') {
    echo "Unauthorized access";
    exit();
}

if (!isset($_POST['maintenance_id'])) {
    echo "Missing maintenance ID";
    exit();
}

$maintenance_id = (int)$_POST['maintenance_id'];

// Handle total cost - can be empty
$total_cost = null;
if (isset($_POST['total_cost']) && $_POST['total_cost'] !== '' && $_POST['total_cost'] > 0) {
    $total_cost = (float)$_POST['total_cost'];
}

// Handle file upload - can be empty
$bill_filename = null;
if (isset($_FILES['bill_file']) && $_FILES['bill_file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/bills/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_tmp = $_FILES['bill_file']['tmp_name'];
    $file_name = $_FILES['bill_file']['name'];
    $file_size = $_FILES['bill_file']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Allowed file types
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    
    if (!in_array($file_ext, $allowed)) {
        echo "Invalid file type. Only JPG, PNG, and PDF allowed.";
        exit();
    }
    
    if ($file_size > 5 * 1024 * 1024) { // 5MB max
        echo "File size too large. Maximum 5MB allowed.";
        exit();
    }
    
    // Generate unique filename
    $bill_filename = 'bill_' . $maintenance_id . '_' . time() . '.' . $file_ext;
    $upload_path = $upload_dir . $bill_filename;
    
    if (!move_uploaded_file($file_tmp, $upload_path)) {
        echo "Failed to upload file";
        exit();
    }
}

// Get equipment_id from maintenance record
$equipmentQuery = "SELECT equipment_id FROM maintenance WHERE maintenance_id = $maintenance_id";
$equipmentResult = mysqli_query($con, $equipmentQuery);

if (!$equipmentResult || mysqli_num_rows($equipmentResult) === 0) {
    echo "Maintenance request not found";
    exit();
}

$equipmentData = mysqli_fetch_assoc($equipmentResult);
$equipment_id = (int)$equipmentData['equipment_id'];

/* ===============================
   START TRANSACTION
   =============================== */
mysqli_begin_transaction($con);

try {
    // 1️⃣ Update maintenance record - set maintenance_date to CURDATE()
    $updateMaintenance = "UPDATE maintenance SET maintenance_date = CURDATE()";
    
    // Only update total_cost if provided
    if ($total_cost !== null) {
        $updateMaintenance .= ", total_cost = $total_cost";
    }
    
    // Only update bill_file_path if file was uploaded
    if ($bill_filename !== null) {
        $bill_filename_escaped = mysqli_real_escape_string($con, $bill_filename);
        $updateMaintenance .= ", bill_file_path = '$bill_filename_escaped'";
    }
    
    $updateMaintenance .= " WHERE maintenance_id = $maintenance_id";
    
    if (!mysqli_query($con, $updateMaintenance)) {
        throw new Exception(mysqli_error($con));
    }
    
    // 2️⃣ Update equipment status back to Available
    $updateEquipment = "UPDATE equipment SET status = 'Available' WHERE equipment_id = $equipment_id";
    
    if (!mysqli_query($con, $updateEquipment)) {
        throw new Exception(mysqli_error($con));
    }
    
    // ✅ Commit everything
    mysqli_commit($con);
    
    echo "success";
    
} catch (Exception $e) {
    // ❌ Rollback if anything fails
    mysqli_rollback($con);
    
    // Delete uploaded file if transaction failed
    if ($bill_filename && file_exists('uploads/bills/' . $bill_filename)) {
        unlink('uploads/bills/' . $bill_filename);
    }
    
    echo "Operation failed: " . $e->getMessage();
}
?>