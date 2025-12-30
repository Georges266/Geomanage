<?php
session_start();
include 'includes/connect.php';

// 1. Check login & role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'LeadEngineer') {
    echo 'unauthorized';
    exit;
}

// 2. Validate inputs
if (!isset($_POST['equipment_id']) || !isset($_POST['project_id'])) {
    echo 'invalid';
    exit;
}

$equipment_id = (int) $_POST['equipment_id'];
$project_id = (int) $_POST['project_id'];

if ($equipment_id <= 0 || $project_id <= 0) {
    echo 'invalid';
    exit;
}

// 3. Get lead engineer ID
$user_id = $_SESSION['user_id'];
$q = mysqli_query($con, "SELECT lead_engineer_id FROM lead_engineer WHERE user_id = $user_id");
if (mysqli_num_rows($q) == 0) {
    echo 'unauthorized';
    exit;
}
$lead_engineer_id = mysqli_fetch_assoc($q)['lead_engineer_id'];

// 4. Verify project ownership
$check = mysqli_query($con, "
    SELECT project_id FROM project
    WHERE project_id = $project_id
    AND lead_engineer_id = $lead_engineer_id
");

if (mysqli_num_rows($check) == 0) {
    echo 'unauthorized';
    exit;
}

// 5. Check if equipment exists and is available
$check = mysqli_query($con, "SELECT status FROM equipment WHERE equipment_id = $equipment_id");
if (!$check || mysqli_num_rows($check) == 0) {
    echo 'invalid equipment';
    exit;
}

$row = mysqli_fetch_assoc($check);
$status = strtolower(trim($row['status'])); // Normalize status

// Check if equipment is available (accept both 'available' and 'Available')
if ($status != 'available') {
    // Return actual status for debugging
    echo 'equipment not available (current status: ' . $row['status'] . ')';
    exit;
}

// 6. Check if equipment is already assigned to this project
$already_assigned = mysqli_query($con, "
    SELECT * FROM uses_project_equipment
    WHERE project_id = $project_id
    AND equipment_id = $equipment_id
");

if (mysqli_num_rows($already_assigned) > 0) {
    echo 'already assigned';
    exit;
}

// 7. Insert into uses_project_equipment
$insert = mysqli_query(
    $con,
    "INSERT INTO uses_project_equipment (equipment_id, project_id) 
     VALUES ($equipment_id, $project_id)"
);

if (!$insert) {
    echo 'error: ' . mysqli_error($con);
    exit;
}

// 8. Update equipment status to 'requested'
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