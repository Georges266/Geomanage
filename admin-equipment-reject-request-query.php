<?php
include 'includes/connect.php';

$equipment_id = intval($_POST['equipment_id']);
$project_id   = intval($_POST['project_id']);

if ($equipment_id > 0 && $project_id > 0) {

    mysqli_begin_transaction($con);

    try {
        // Remove request
        mysqli_query($con, "
            DELETE FROM uses_project_equipment
            WHERE equipment_id = $equipment_id
            AND project_id = $project_id
        ");

        // Restore equipment availability
        mysqli_query($con, "
            UPDATE equipment
            SET status = 'available'
            WHERE equipment_id = $equipment_id
            AND status = 'requested'
        ");

        mysqli_commit($con);
        echo "success";

    } catch (Exception $e) {
        mysqli_rollback($con);
        echo "error";
    }

} else {
    echo "error: Invalid data";
}

mysqli_close($con);
?>
