<?php
// ========================================================================
// FILE: admin-project-remove-service-query.php
// Remove service request from project and recalculate total cost
// ========================================================================
include 'includes/connect.php';

$request_id = (int)$_POST['request_id'];
$project_id = (int)$_POST['project_id'];

// Remove the service request from project (set project_id to NULL)
$query = "UPDATE service_request 
          SET project_id = NULL 
          WHERE request_id = $request_id";

if (mysqli_query($con, $query)) {
    
    // Recalculate project total cost based on remaining service requests
    $costQuery = "
        SELECT COALESCE(SUM(sr.price), 0) as total_cost
        FROM service_request sr
        WHERE sr.project_id = $project_id
    ";
    
    $costResult = mysqli_query($con, $costQuery);
    
    if ($costResult && $costRow = mysqli_fetch_assoc($costResult)) {
        $newTotalCost = floatval($costRow['total_cost']);
        
        // Update project with new total cost
        $updateCostQuery = "
            UPDATE project 
            SET total_cost = $newTotalCost 
            WHERE project_id = $project_id
        ";
        
        mysqli_query($con, $updateCostQuery);
    }
    
    echo "success";
} else {
    echo "error: " . mysqli_error($con);
}

mysqli_close($con);
?>