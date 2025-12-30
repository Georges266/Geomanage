<?php
session_start();
include 'includes/connect.php';

// Check if user is logged in and is a Lead Engineer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "LeadEngineer") {
    echo "Unauthorized";
    exit();
}

// Debug: Check what data is received
error_log("POST data: " . print_r($_POST, true));

$equipment_id = intval($_POST['equipment_id']);
$maintenance_note = mysqli_real_escape_string($con, $_POST['maintenance_note']);
$maintenance_date = date('Y-m-d H:i:s');

// Debug: Log the values
error_log("Equipment ID: " . $equipment_id);
error_log("Maintenance Note: " . $maintenance_note);

// First, check if equipment exists
$checkQuery = "SELECT * FROM equipment WHERE equipment_id = $equipment_id";
$checkResult = mysqli_query($con, $checkQuery);

if (!$checkResult || mysqli_num_rows($checkResult) == 0) {
    echo "Error: Equipment ID $equipment_id not found in database";
    exit();
}

// Update equipment to maintenance status
$sql = "UPDATE equipment
        SET maintenance_note = '$maintenance_note',
            request_date = '$maintenance_date',
            status = 'maintenance'
        WHERE equipment_id = $equipment_id";

error_log("SQL Query: " . $sql);

if (mysqli_query($con, $sql)) {
    $affected = mysqli_affected_rows($con);
    error_log("Affected rows: " . $affected);
    
    if ($affected > 0) {
        echo "Maintenance request submitted successfully!";
    } else {
        echo "Warning: Equipment found but not updated. It may already be in maintenance status.";
    }
} else {
    error_log("MySQL Error: " . mysqli_error($con));
    echo "Error: " . mysqli_error($con);
}
?>