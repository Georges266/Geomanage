<?php
include 'includes/connect.php';

if (!isset($_POST['equipment_id']) || !isset($_POST['project_id'])) {
    echo 'invalid';
    exit;
}

$equipment_id = (int) $_POST['equipment_id'];
$project_id = (int) $_POST['project_id'];

// Check if equipment is available
$check = mysqli_query($con, "SELECT status FROM equipment WHERE equipment_id = $equipment_id");
if (!$check || mysqli_num_rows($check) == 0) {
    echo 'invalid equipment';
    exit;
}

$row = mysqli_fetch_assoc($check);
if ($row['status'] != 'Available'||$row['status'] != 'available' ) {
    echo 'equipment not available';
    exit;
}

// Insert request
$insert = mysqli_query(
    $con,
    "INSERT INTO uses_project_equipment (equipment_id, project_id) 
     VALUES ($equipment_id, $project_id)"
);

if (!$insert) {
    echo 'error: ' . mysqli_error($con);
    exit;
}

// Update equipment status to requested
$update = mysqli_query(
    $con,
    "UPDATE equipment 
     SET status = 'requested'
     WHERE equipment_id = $equipment_id"
);

if (!$update) {
    echo 'error: ' . mysqli_error($con);
    exit;
}

echo 'success';
mysqli_close($con);
?>
