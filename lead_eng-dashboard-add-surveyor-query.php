<?php
// ========================================================================
// FILE: lead_eng-dashboard-add-single-surveyor-query.php
// NEW FILE - Add ONE surveyor at a time
// ========================================================================
include 'includes/connect.php';

$project_id = intval($_POST['project_id']);
$surveyor_id = intval($_POST['surveyor_id']); // Just ONE surveyor ID

// Update surveyor with project assignment
$result = mysqli_query($con, "
    UPDATE surveyor 
    SET status='assigned', project_id=$project_id 
    WHERE surveyor_id=$surveyor_id AND status='available'
");

if($result && mysqli_affected_rows($con) > 0) {
    echo "success";
} else {
    echo "error";
}
?>