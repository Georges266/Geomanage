<?php
// ========================================
// FILE: hr-schedule-action-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['schedule'])){
    $user_id = $_SESSION['user_id'];
    $get_hr_id = "SELECT `hr_id` FROM `hr` WHERE user_id = '$user_id'";
    $result = mysqli_query($con, $get_hr_id);
    $row = mysqli_fetch_assoc($result);
    $hr_id = $row['hr_id'];
    
    $applicationId = intval($_POST['application_id']);
    $action = $_POST['action'];
    
    // Check current status
    $check_status_query = "SELECT status FROM job_application WHERE application_id = '$applicationId'";
    $status_result = mysqli_query($con, $check_status_query);
    $current_app = mysqli_fetch_assoc($status_result);
    
    if (!$current_app) {
        echo json_encode(['status' => 'error', 'message' => 'Application not found.']);
        exit();
    }

    if ($action === 'schedule') {
        // Check if already scheduled
        $check_scheduled = "SELECT schedule_id FROM interview_schedule 
                           WHERE application_id = '$applicationId' 
                           AND status IN ('Scheduled', 'Rescheduled')";
        $scheduled_result = mysqli_query($con, $check_scheduled);
        
        if (mysqli_num_rows($scheduled_result) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'This application already has an interview scheduled.']);
            exit();
        }
        
        if ($current_app['status'] === 'Rejected') {
            echo json_encode(['status' => 'error', 'message' => 'Cannot schedule interview - application has been rejected.']);
            exit();
        }
        
        if ($current_app['status'] === 'Hired') {
            echo json_encode(['status' => 'error', 'message' => 'Cannot schedule interview - candidate has already been hired.']);
            exit();
        }
    }

    $interviewDate = mysqli_real_escape_string($con, $_POST['interview_date']);
    $interviewTime = mysqli_real_escape_string($con, $_POST['interview_time']);
    $interviewLocation = mysqli_real_escape_string($con, $_POST['interview_location']);
    $interviewType = mysqli_real_escape_string($con, $_POST['interview_type']);

    // Check for time conflicts
    $conflict_check = "SELECT isch.schedule_id, u.full_name, jo.job_title
                      FROM interview_schedule isch
                      JOIN job_application ja ON isch.application_id = ja.application_id
                      JOIN job_opportunity jo ON ja.job_id = jo.job_id
                      JOIN user u ON ja.user_id = u.user_id
                      WHERE isch.hr_id = '$hr_id'
                      AND isch.interview_date = '$interviewDate'
                      AND isch.interview_time = '$interviewTime'
                      AND isch.application_id != '$applicationId'
                      AND isch.status IN ('Scheduled', 'Rescheduled')";

    $conflict_result = mysqli_query($con, $conflict_check);

    if (mysqli_num_rows($conflict_result) > 0) {
        $conflict = mysqli_fetch_assoc($conflict_result);
        echo json_encode([
            'status' => 'error',
            'message' => "You already have an interview scheduled at this time with {$conflict['full_name']} for {$conflict['job_title']}."
        ]);
        exit();
    }

    mysqli_begin_transaction($con);
    
    try {
        if ($action === 'schedule') {
            $insert_schedule = "INSERT INTO interview_schedule 
                               (interview_date, interview_time, interview_type, interview_location, result, status, hr_id, application_id)
                               VALUES ('$interviewDate', '$interviewTime', '$interviewType', '$interviewLocation', 'Pending', 'Scheduled', '$hr_id', '$applicationId')";
            
            if (!mysqli_query($con, $insert_schedule)) {
                throw new Exception("Failed to create schedule: " . mysqli_error($con));
            }
            
            $update_app = "UPDATE job_application SET status = 'Interview Scheduled' WHERE application_id = '$applicationId'";
            if (!mysqli_query($con, $update_app)) {
                throw new Exception("Failed to update application: " . mysqli_error($con));
            }
            
            mysqli_commit($con);
            echo json_encode(['status' => 'success', 'message' => 'Interview scheduled successfully.']);
            
        } else { // reschedule
            $update_schedule = "UPDATE interview_schedule 
                               SET interview_date = '$interviewDate',
                                   interview_time = '$interviewTime',
                                   interview_type = '$interviewType',
                                   interview_location = '$interviewLocation',
                                   status = 'Rescheduled'
                               WHERE application_id = '$applicationId' AND hr_id = '$hr_id'";
            
            if (!mysqli_query($con, $update_schedule)) {
                throw new Exception("Failed to reschedule: " . mysqli_error($con));
            }
            
            $update_app = "UPDATE job_application SET status = 'Interview Rescheduled' WHERE application_id = '$applicationId'";
            if (!mysqli_query($con, $update_app)) {
                throw new Exception("Failed to update application: " . mysqli_error($con));
            }
            
            mysqli_commit($con);
            echo json_encode(['status' => 'success', 'message' => 'Interview rescheduled successfully.']);
        }
        
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
