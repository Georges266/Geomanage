<?php
// ========================================
// FILE: hr-get-application-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['get'])){
    $appId = intval($_POST['application_id']);
    
    $query = "SELECT ja.*, jo.job_title, u.full_name
              FROM job_application AS ja
              JOIN job_opportunity AS jo ON ja.job_id = jo.job_id
              JOIN user AS u ON ja.user_id = u.user_id
              WHERE ja.application_id = '$appId'";
    
    $result = mysqli_query($con, $query);
    $data = mysqli_fetch_assoc($result);
    
    if($data){
        // Check if already scheduled
        $check_scheduled = "SELECT schedule_id FROM interview_schedule 
                           WHERE application_id = '$appId' 
                           AND status IN ('Scheduled', 'Rescheduled')";
        $is_scheduled = mysqli_num_rows(mysqli_query($con, $check_scheduled)) > 0;
        
        $data['is_scheduled'] = $is_scheduled;
        
        echo json_encode($data);
    }
}
?>