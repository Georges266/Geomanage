<?php
// ========================================
// FILE: hr-get-job-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['get'])){
    $jobId = intval($_POST['job_id']);
    $query = "SELECT * FROM job_opportunity WHERE job_id = '$jobId'";
    $result = mysqli_query($con, $query);
    $job = mysqli_fetch_assoc($result);
    
    echo json_encode($job);
}
?>