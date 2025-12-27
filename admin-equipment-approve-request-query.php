<?php
include 'includes/connect.php';

$equipment_id = intval($_POST['equipment_id']);

if ($equipment_id > 0) {
    $updateQuery = "
        UPDATE equipment 
        SET status = 'assigned' 
        WHERE equipment_id = $equipment_id
    ";

    if (mysqli_query($con, $updateQuery)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($con);
    }
} else {
    echo "error: Invalid data";
}

mysqli_close($con);
?>
