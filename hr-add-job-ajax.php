<?php
// ========================================
// FILE: hr-add-job-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['add'])){
    $user_id = $_SESSION['user_id'];
    $get_hr_id = "SELECT `hr_id` FROM `hr` WHERE user_id = '$user_id'";
    $result = mysqli_query($con, $get_hr_id);
    $row = mysqli_fetch_assoc($result);
    $hr_id = $row['hr_id'];
    
    $jobTitle = mysqli_real_escape_string($con, $_POST['job_title']);
    $numberOfPositions = intval($_POST['number_of_positions']);
    $jobType = mysqli_real_escape_string($con, $_POST['job_type']);
    $jobDescription = mysqli_real_escape_string($con, $_POST['job_description']);
    $responsibilities = mysqli_real_escape_string($con, $_POST['responsibilities']);
    $status = "open";

    $create_job = "INSERT INTO job_opportunity
        (job_title, number_of_positions, job_description, responsibilities, status, hr_id, job_type)
        VALUES
        ('$jobTitle', '$numberOfPositions', '$jobDescription', '$responsibilities', '$status', '$hr_id', '$jobType')";

    mysqli_query($con, $create_job);
}
?>