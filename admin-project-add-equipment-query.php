<?php
include 'includes/connect.php';

// Get data from AJAX request
$equipment_id = $_POST['equipment_id'];
$project_id = $_POST['project_id'];

// Insert into uses_project_equipment table (many-to-many relationship)
$query = "INSERT INTO uses_project_equipment (project_id, equipment_id) 
          VALUES ($project_id, $equipment_id)";

// Execute the query
if (mysqli_query($con, $query)) {
    // Update equipment status to 'Assigned'
    $updateStatus = "UPDATE equipment 
                     SET status = 'Assigned' 
                     WHERE equipment_id = $equipment_id";
    mysqli_query($con, $updateStatus);
    
    echo "success";
} else {
    echo "error";
}

mysqli_close($con);
?>