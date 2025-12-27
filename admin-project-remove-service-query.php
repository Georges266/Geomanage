<?php
include 'includes/connect.php';

// Get data from AJAX request
$request_id = $_POST['request_id'];

// Update the service_request table - set project_id to NULL (remove the assignment)
$query = "UPDATE service_request 
          SET project_id = NULL 
          WHERE request_id = $request_id";

// Execute the query
if (mysqli_query($con, $query)) {
    echo "success";
} else {
    echo "error";
}

mysqli_close($con);
?>