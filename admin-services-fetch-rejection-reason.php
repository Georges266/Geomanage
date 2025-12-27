<?php
include 'includes/connect.php';  

$request_id = $_POST['id']  ;
$query="Select rejection_reason from service_request where request_id= '$request_id'";
$result = mysqli_query($con, $query);

  if ($row = mysqli_fetch_assoc($result)) {
        echo htmlspecialchars($row['rejection_reason']);}
?>