<?php
session_start();
include 'includes/connect.php';

// Check authorization
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "HR") {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['date'])) {
    $date = mysqli_real_escape_string($con, $_GET['date']);
    if (isset($_GET['date'], $_GET['application_id'])) {
    $date = mysqli_real_escape_string($con, $_GET['date']);
    $applicationId = (int)$_GET['application_id'];

    // Get HR ID
    $user_id = $_SESSION['user_id'];
    $get_hr_id = "SELECT hr_id FROM hr WHERE user_id = '$user_id'";
    $result = mysqli_query($con, $get_hr_id);
    $row = mysqli_fetch_assoc($result);
    $hr_id = $row['hr_id'];

    // Get booked times for this HR on the selected date, excluding the current application
    $booked_query = "SELECT ja.interview_time
                     FROM job_application ja
                     JOIN job_opportunity jo ON ja.job_id = jo.job_id
                     WHERE jo.hr_id = '$hr_id'
                       AND ja.interview_date = '$date'
                       AND ja.status IN ('Interview Scheduled', 'Interview Rescheduled')
                       AND ja.interview_time IS NOT NULL
                       AND ja.application_id != $applicationId";

    $booked_result = mysqli_query($con, $booked_query);
    $bookedTimes = [];

    while ($row = mysqli_fetch_assoc($booked_result)) {
        $bookedTimes[] = $row['interview_time'];
    }

    echo json_encode(['bookedTimes' => $bookedTimes]);
}}
?>rgba(102, 102, 102, 0.96)