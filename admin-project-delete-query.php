<?php
include 'includes/connect.php';

// Get data from AJAX request
$project_id = $_POST['project_id'];

// Update project status to 'deleted' instead of actually deleting
$query = "UPDATE project 
          SET status = 'deleted' 
          WHERE project_id = $project_id";

// Execute the query
if (mysqli_query($con, $query)) {
    // Update service request status to 'project_deleted' but KEEP project_id
    $updateRequests = "UPDATE service_request 
                       SET status = 'project_deleted' 
                       WHERE project_id = $project_id";
    mysqli_query($con, $updateRequests);
    
    // Get all equipment assigned to this project
    $getEquipment = "SELECT equipment_id FROM uses_project_equipment 
                     WHERE project_id = $project_id";
    $result = mysqli_query($con, $getEquipment);
    
    // Update each equipment status back to 'Available'
    while ($row = mysqli_fetch_assoc($result)) {
        $updateEquipment = "UPDATE equipment 
                           SET status = 'Available' 
                           WHERE equipment_id = {$row['equipment_id']}";
        mysqli_query($con, $updateEquipment);
    }
    
    // Remove equipment assignments from the project
    $removeEquipment = "DELETE FROM uses_project_equipment 
                        WHERE project_id = $project_id";
    mysqli_query($con, $removeEquipment);
    
    echo "success";
} else {
    echo "error";
}

mysqli_close($con);
?>