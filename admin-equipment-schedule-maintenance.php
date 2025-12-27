<?php

include 'includes/connect.php';

// Validate required parameters
if (!isset($_POST['equipment_id']) || !isset($_POST['maintenance_note'])) {
    echo "Missing required parameters";
    exit();
}

$equipment_id = (int)$_POST['equipment_id'];
$maintenance_note = mysqli_real_escape_string($con, trim($_POST['maintenance_note']));
$maintenance_date = date('Y-m-d');

// Validate equipment exists
$checkQuery = "SELECT equipment_name, status FROM equipment WHERE equipment_id = $equipment_id";
$checkResult = mysqli_query($con, $checkQuery);

if (!$checkResult || mysqli_num_rows($checkResult) === 0) {
    echo "Equipment not found";
    exit();
}

$equipment = mysqli_fetch_assoc($checkResult);
$equipment_name = htmlspecialchars($equipment['equipment_name']);

$updateQuery = "UPDATE equipment 
                SET status = 'Maintenance', 
                    maintenance_date = '$maintenance_date',
                    maintenance_note = '$maintenance_note'
                WHERE equipment_id = $equipment_id";

if (mysqli_query($con, $updateQuery)) {
    echo "Maintenance scheduled successfully for $equipment_name.";
} else {
    echo "Database error: " . mysqli_error($con);
}
?>
