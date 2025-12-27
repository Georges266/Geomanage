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
$status_in  = $_POST['status'] ?? ''; // optional (e.g. checkbox or hidden)

/* 4. Validate */
if ($project_id <= 0) {
    die('Invalid project ID');
}

if ($progress < 0 || $progress > 100) {
    die('Progress must be between 0 and 100');
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

/* 7. BUSINESS LOGIC (automatic sync) */

// If status is marked completed → force progress to 100
if ($status_in === 'completed') {
    $progress = 100;
}

// If progress is 100 → completed, else active
$status = ($progress == 100) ? 'completed' : 'active';

/* 8. Single UPDATE */
$update = mysqli_query($con, "
    UPDATE project
    SET progress = $progress,
        status   = '$status'
    WHERE project_id = $project_id
    AND lead_engineer_id = $lead_engineer_id
");

/* 9. Result */
if ($update) {
    echo 'Project updated successfully';
} else {
    echo 'Database error';
}

mysqli_close($con);
?>
