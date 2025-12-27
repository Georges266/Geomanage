<?php
include 'includes/connect.php';

// Get data from form
$project_id = $_POST['project_id'];
$project_name = mysqli_real_escape_string($con, $_POST['project_name']);
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$lead_engineer_id = $_POST['lead_engineer_id'];
$team_size = $_POST['team_size'];
$description = mysqli_real_escape_string($con, $_POST['description']);


 


// Update the project in database
$query = "UPDATE project SET 
    project_name = '$project_name',
    start_date = '$start_date',
    end_date = '$end_date',
    lead_engineer_id = $lead_engineer_id,
    team_size = $team_size,
    description = '$description'
    WHERE project_id = $project_id";

// Execute the query
if (mysqli_query($con, $query)) {
    echo "success";
} else {
    echo "error";
}

mysqli_close($con);
?>

