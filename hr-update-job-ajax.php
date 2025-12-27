<?php
// ========================================
// FILE: hr-update-job-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['edit'])){
    $jobId = intval($_POST['job_id']);
    $title = mysqli_real_escape_string($con, $_POST['job_title']);
    $positions = intval($_POST['number_of_positions']);
    $type = mysqli_real_escape_string($con, $_POST['job_type']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $desc = mysqli_real_escape_string($con, $_POST['job_description']);
    $resp = mysqli_real_escape_string($con, $_POST['responsibilities']);
    $req = mysqli_real_escape_string($con, $_POST['requirements']);

    // Normalize status
    $finalStatus = ($status === 'closed_reject') ? 'closed' : $status;

    // Update job
    $sql = "UPDATE job_opportunity 
            SET job_title='$title', 
                number_of_positions=$positions, 
                job_type='$type', 
                status='$finalStatus', 
                job_description='$desc', 
                responsibilities='$resp', 
                requirements='$req' 
            WHERE job_id=$jobId";
    mysqli_query($con, $sql);

    // If HR chose "closed_reject", reject all non-hired applications
    if ($status === 'closed_reject') {
        $rejectSql = "UPDATE job_application 
                      SET status='rejected' 
                      WHERE job_id=$jobId AND status!='hired'";
        mysqli_query($con, $rejectSql);
    }
}
?>