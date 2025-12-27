<?php
include 'includes/connect.php';

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $query = "SELECT file_path FROM deliverable WHERE project_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        $file_path = $row['file_path'];
        
        // Check if file actually exists on server
        if(file_exists($file_path)) {
            echo $file_path;
        } else {
            // File path is in database but file doesn't exist
            echo "ERROR: The deliverable file is currently unavailable.";
        }
    } else {
        // No deliverable record found for this project
        echo "ERROR: This project does not have a deliverable PDF available yet.";
    }
} else {
    echo "ERROR: Invalid request. Please try again.";
}
?>