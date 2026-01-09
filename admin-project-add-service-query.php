<?php
// ========================================================================
// FILE: admin-project-add-service-query.php (UPDATED)
// Add service request to project and recalculate total cost
// ========================================================================
include 'includes/connect.php';

$request_id = (int)$_POST['request_id'];
$project_id = (int)$_POST['project_id'];

// Start transaction for data consistency
mysqli_begin_transaction($con);

try {
    // Update the service_request table - set the project_id
    $query = "UPDATE service_request 
              SET project_id = $project_id 
              WHERE request_id = $request_id";
    
    if (!mysqli_query($con, $query)) {
        throw new Exception("Failed to assign service request");
    }
    
    // Recalculate project total cost based on all assigned service requests
    $costQuery = "
        UPDATE project p
        SET total_cost = (
            SELECT COALESCE(SUM(sr.price), 0)
            FROM service_request sr
            WHERE sr.project_id = $project_id
        )
        WHERE p.project_id = $project_id
    ";
    
    if (!mysqli_query($con, $costQuery)) {
        throw new Exception("Failed to update project cost");
    }
    
    // Commit transaction
    mysqli_commit($con);
    echo "success";
    
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($con);
    echo "error: " . $e->getMessage();
}

mysqli_close($con);
?>