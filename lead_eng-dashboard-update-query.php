<?php
session_start();
include 'includes/connect.php';

/* 1. Check login & role */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'LeadEngineer') {
    die('Unauthorized access');
}

/* 2. Only POST allowed */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request');
}

/* 3. Get data */
$project_id = (int)($_POST['project_id'] ?? 0);
$progress   = (int)($_POST['progress'] ?? 0);
$status_in  = $_POST['status'] ?? ''; // User's selected status from modal

/* 4. Validate */
if ($project_id <= 0) {
    die('Invalid project ID');
}

if ($progress < 0 || $progress > 100) {
    die('Progress must be between 0 and 100');
}

if (!in_array($status_in, ['active', 'completed'])) {
    die('Invalid status');
}

/* 5. Get lead engineer ID */
$user_id = $_SESSION['user_id'];

$q = mysqli_query($con, "SELECT lead_engineer_id FROM lead_engineer WHERE user_id = $user_id");
if (mysqli_num_rows($q) == 0) {
    die('Lead engineer not found');
}

$lead_engineer_id = mysqli_fetch_assoc($q)['lead_engineer_id'];

/* 6. Verify project ownership */
$check = mysqli_query($con, "
    SELECT project_id FROM project
    WHERE project_id = $project_id
    AND lead_engineer_id = $lead_engineer_id
");

if (mysqli_num_rows($check) == 0) {
    die('You do not have permission to update this project');
}

/* 7. BUSINESS LOGIC - REVISED */

// Use the user's selected status directly
$status = $status_in;

// If user selects "completed", enforce progress = 100
if ($status === 'completed' && $progress < 100) {
    $progress = 100;
}

// If user selects "active" but progress is 100, allow it (they're reopening the project)
// No forced status change based on progress

/* 8. Update project */
$update = mysqli_query($con, "
    UPDATE project
    SET progress = $progress,
        status   = '$status'
    WHERE project_id = $project_id
    AND lead_engineer_id = $lead_engineer_id
");

/* 9. If project completed → free equipment & surveyors */
if ($update && $status === 'completed') {
    // 9a. Free equipment & remove from uses_project_equipment
    mysqli_query($con, "
        UPDATE equipment e
        JOIN uses_project_equipment pe ON e.equipment_id = pe.equipment_id
        SET e.status = 'available'
        WHERE pe.project_id = $project_id
    ");

    mysqli_query($con, "
        DELETE FROM uses_project_equipment
        WHERE project_id = $project_id
    ");

    // 9b. Free surveyors by setting project_id to NULL
    mysqli_query($con, "
        UPDATE surveyor
        SET project_id = NULL,
            status = 'available'
        WHERE project_id = $project_id
    ");
}

/* 10. Result */
if ($update) {
    echo 'Project updated successfully';
} else {
    echo 'Database error';
}

mysqli_close($con);
?>