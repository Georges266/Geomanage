<?php
// ========================================
// FILE: hr-get-schedule-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['get'])){
    $id = intval($_POST['application_id']);

    $sql = "
        SELECT ja.*, jo.job_title, u.full_name, 
               s.interview_date, s.interview_time, 
               s.interview_type, s.interview_location
        FROM job_application ja
        JOIN job_opportunity jo ON ja.job_id = jo.job_id
        JOIN user u ON ja.user_id = u.user_id
        JOIN interview_schedule s ON s.application_id = ja.application_id
        WHERE ja.application_id = '$id'
    ";

    $res = mysqli_query($con, $sql);
    $data = mysqli_fetch_assoc($res);
    
    if($data){
        // Add formatted date and time for display
        $data['formatted_date'] = date("M j, Y", strtotime($data['interview_date']));
        $data['formatted_time'] = date("g:i A", strtotime($data['interview_time']));
        
        echo json_encode($data);
    }
}
?>