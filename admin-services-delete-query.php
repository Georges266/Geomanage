<?php
include 'includes/connect.php';

if(isset($_POST['service_id'])){
    $id = (int)$_POST['service_id'];
    $query="update service set status='Inactive' WHERE service_id = '$id'";
    if(mysqli_query($con, $query)){
        echo "Service deactivated";
    } else {
        echo "error: " . mysqli_error($con);
    }
}

 
exit;
?>
