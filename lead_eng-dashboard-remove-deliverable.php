<?php
session_start();
include 'includes/connect.php';

// Check authorization
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "LeadEngineer") {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

// Get deliverable_id from POST
$deliverable_id = isset($_POST['deliverable_id']) ? intval($_POST['deliverable_id']) : 0;

if ($deliverable_id <= 0) {
    http_response_code(400);
    echo "Invalid deliverable ID";
    exit();
}

// Get file path before deleting from database
$query = "SELECT file_path FROM deliverable WHERE deliverable_id = $deliverable_id";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $file_path = $row['file_path'];
    
    // Delete from database
    $delete_query = "DELETE FROM deliverable WHERE deliverable_id = $deliverable_id";
    
    if (mysqli_query($con, $delete_query)) {
        // Delete physical file
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        echo "success";
    } else {
        http_response_code(500);
        echo "Database error";
    }
} else {
    http_response_code(404);
    echo "Deliverable not found";
}
?>