<?php
// ========================================
// FILE: hr-update-schedule-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['update'])){
    $user_id = $_SESSION['user_id'];
    $q = mysqli_query($con, "SELECT hr_id FROM hr WHERE user_id='$user_id'");
    $hr = mysqli_fetch_assoc($q);
    $hr_id = $hr['hr_id'];
    
    $applicationId = intval($_POST['application_id']);
    $interviewDate = mysqli_real_escape_string($con, $_POST['interview_date']);
    $interviewTime = mysqli_real_escape_string($con, $_POST['interview_time']);
    $interviewLocation = mysqli_real_escape_string($con, $_POST['interview_location']);
    $interviewType = mysqli_real_escape_string($con, $_POST['interview_type']);

    // CHECK CONFLICTS
    $conflict_sql = "
        SELECT s.schedule_id, u.full_name, jo.job_title
        FROM interview_schedule s
        JOIN job_application ja ON s.application_id = ja.application_id
        JOIN job_opportunity jo ON ja.job_id = jo.job_id
        JOIN user u ON ja.user_id = u.user_id
        WHERE s.hr_id = '$hr_id'
        AND s.interview_date = '$interviewDate'
        AND s.interview_time = '$interviewTime'
        AND s.application_id != '$applicationId'
        AND s.status IN ('Scheduled','Rescheduled')
    ";

    $check = mysqli_query($con, $conflict_sql);

    if (mysqli_num_rows($check) > 0) {
        $conflict = mysqli_fetch_assoc($check);
        
        $response = array(
            'status' => 'error',
            'message' => "This time is already booked with {$conflict['full_name']} ({$conflict['job_title']})."
        );
        
        echo json_encode($response);
        exit();
    }

    // UPDATE interview_schedule
    $update_sql = "
        UPDATE interview_schedule 
        SET interview_date='$interviewDate',
            interview_time='$interviewTime',
            interview_location='$interviewLocation',
            interview_type='$interviewType',
            status='Rescheduled'
        WHERE application_id='$applicationId'
        AND hr_id='$hr_id'
    ";

    mysqli_query($con, $update_sql);

    // UPDATE application status
    mysqli_query($con, "
        UPDATE job_application 
        SET status='Interview Rescheduled'
        WHERE application_id='$applicationId'
    ");

    $response = array(
        'status' => 'success',
        'message' => 'Interview rescheduled successfully.'
    );
    
    echo json_encode($response);
}
?>