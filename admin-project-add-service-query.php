<?php
include 'includes/connect.php';

// Get data from AJAX request
$request_id = $_POST['request_id'];
$project_id = $_POST['project_id'];

// Update the service_request table - set the project_id (foreign key)
$query = "UPDATE service_request 
          SET project_id = $project_id 
          WHERE request_id = $request_id";

// Execute the query
if (mysqli_query($con, $query)) {
    echo "success";
} else {
    echo "error";
}

mysqli_close($con);
?>