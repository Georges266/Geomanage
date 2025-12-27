<?php
// ========================================================================
// FILE: lead_eng-dashboard-remove-surveyor-query.php
// ========================================================================
include 'includes/connect.php';
$project_id = intval($_POST['project_id']);
$surveyor_id = intval($_POST['surveyor_id']);

// FIXED: Remove project assignment AND update status to available
mysqli_query($con, "
    UPDATE surveyor 
    SET status='available', project_id=NULL 
    WHERE surveyor_id=$surveyor_id
");

echo "success"; // Return success message
?>