<?php
session_start();
include 'includes/connect.php';

// Check authorization
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "LeadEngineer") {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

// Get project_id from POST
$project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;

if ($project_id <= 0) {
    http_response_code(400);
    echo "Invalid project ID";
    exit();
}

// Set upload directory
$uploadDir = 'deliverables/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Check if file was uploaded
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileName = time() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Insert into deliverable table
            $deliverable_name = mysqli_real_escape_string($con, $file['name']);
            $file_path = mysqli_real_escape_string($con, $targetPath);
            $deliverable_type = pathinfo($fileName, PATHINFO_EXTENSION);
            $submission_date = date('Y-m-d');
            
            $query = "INSERT INTO deliverable (project_id, deliverable_name, deliverable_type, file_path, submission_date) 
                      VALUES ($project_id, '$deliverable_name', '$deliverable_type', '$file_path', '$submission_date')";
            
            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                http_response_code(500);
                echo "Database error";
            }
        } else {
            http_response_code(500);
            echo "Failed to move file";
        }
    } else {
        http_response_code(400);
        echo "Upload error: " . $file['error'];
    }
} else {
    http_response_code(400);
    echo "No file received";
}
?>