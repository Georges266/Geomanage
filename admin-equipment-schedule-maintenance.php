<?php
session_start(); // ADD THIS LINE AT THE TOP
include 'includes/connect.php';

 

// Validate inputs
if (
    !isset($_POST['equipment_id']) ||
    !isset($_POST['maintenance_type']) ||
    !isset($_POST['maintenance_description'])
) {
    echo "Missing required parameters";
    exit();
}

// MOVE THIS AFTER session_start() and add validation
if (!isset($_SESSION['user_id'])) {
    echo "User not authenticated";
    exit();
}

$requester_id = (int)$_SESSION['user_id'];


$equipment_id = (int)$_POST['equipment_id'];
$maintenance_type = mysqli_real_escape_string($con, trim($_POST['maintenance_type']));
$maintenance_description = mysqli_real_escape_string($con, trim($_POST['maintenance_description']));

// Check equipment exists
$checkQuery = "SELECT equipment_name FROM equipment WHERE equipment_id = $equipment_id";
$checkResult = mysqli_query($con, $checkQuery);

if (!$checkResult || mysqli_num_rows($checkResult) === 0) {
    echo "Equipment not found";
    exit();
}

$equipment = mysqli_fetch_assoc($checkResult);
$equipment_name = htmlspecialchars($equipment['equipment_name']);

/* ===============================
   START TRANSACTION
   =============================== */
mysqli_begin_transaction($con);

try {
    // 1️⃣ Insert maintenance (date = CURDATE)
    $insertMaintenance = "
        INSERT INTO maintenance (equipment_id, maintenance_type, description, request_date,requested_by)
        VALUES ($equipment_id, '$maintenance_type', '$maintenance_description', CURDATE(),$requester_id)
    ";

    if (!mysqli_query($con, $insertMaintenance)) {
        throw new Exception(mysqli_error($con));
    }

    // 2️⃣ Update equipment status
    $updateEquipment = "
        UPDATE equipment
        SET status = 'Maintenance'
        WHERE equipment_id = $equipment_id
    ";

    if (!mysqli_query($con, $updateEquipment)) {
        throw new Exception(mysqli_error($con));
    }

    // 3️⃣ Remove equipment from any assigned project
    $deleteAssignment = "
        DELETE FROM uses_project_equipment
        WHERE equipment_id = $equipment_id
    ";

    if (!mysqli_query($con, $deleteAssignment)) {
        throw new Exception(mysqli_error($con));
    }

    // ✅ Commit everything
    mysqli_commit($con);

    echo "Maintenance scheduled successfully for $equipment_name.";

} catch (Exception $e) {
    // ❌ Rollback if anything fails
    mysqli_rollback($con);
    echo "Operation failed: " . $e->getMessage();
}
?>
