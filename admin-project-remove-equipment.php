<?php
include 'includes/connect.php';

// Get data from AJAX request
$equipment_id = $_POST['equipment_id'];
$project_id = $_POST['project_id'];

// Delete from uses_project_equipment table (remove the relationship)
$query = "DELETE FROM uses_project_equipment 
          WHERE project_id = $project_id AND equipment_id = $equipment_id";

// Execute the query
if (mysqli_query($con, $query)) {
    // Update equipment status back to 'Available'
    $updateStatus = "UPDATE equipment 
                     SET status = 'Available' 
                     WHERE equipment_id = $equipment_id";
    mysqli_query($con, $updateStatus);
    
    echo "success";
} else {
    echo "error";
}

mysqli_close($con);
?>