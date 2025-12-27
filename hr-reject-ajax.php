
<?php
// ========================================
// FILE: hr-reject-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['reject'])){
    $applicationId = intval($_POST['application_id']);
    
    // Check current status
    $check_status_query = "SELECT status FROM job_application WHERE application_id = '$applicationId'";
    $status_result = mysqli_query($con, $check_status_query);
    $current_app = mysqli_fetch_assoc($status_result);
    
    if (!$current_app) {
        echo json_encode(['status' => 'error', 'message' => 'Application not found.']);
        exit();
    }

    if ($current_app['status'] === 'Rejected') {
        echo json_encode(['status' => 'error', 'message' => 'Application is already rejected.']);
        exit();
    }
    
    if ($current_app['status'] === 'Hired') {
        echo json_encode(['status' => 'error', 'message' => 'Cannot reject - candidate has already been hired.']);
        exit();
    }

    $update_query = "UPDATE job_application 
                    SET status = 'Rejected'
                    WHERE application_id = '$applicationId'";

    if (mysqli_query($con, $update_query)) {
        echo json_encode(['status' => 'success', 'message' => 'Application rejected successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to reject application.']);
    }
}