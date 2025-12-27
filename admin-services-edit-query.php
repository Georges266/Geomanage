<?php
include 'includes/connect.php';

if (isset($_POST['service_id'])) {
    $id = (int)$_POST['service_id'];
    $name = mysqli_real_escape_string($con, $_POST['service_name']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $min = (float)$_POST['min_price'];
    $max = (float)$_POST['max_price'];
    $description = mysqli_real_escape_string($con, $_POST['description']);

    // Validate that min price is not greater than max price
    if ($min > $max) {
        echo "Error: Minimum price cannot be greater than maximum price.";
        exit;
    }

    // Validate that prices are not negative
    if ($min < 0 || $max < 0) {
        echo "Error: Prices cannot be negative.";
        exit;
    }

    $query = "
        UPDATE service SET 
            service_name = '$name',
            status = '$status',
            min_price = '$min',
            max_price = '$max',
            description = '$description'
        WHERE service_id = '$id'
    ";

    if (mysqli_query($con, $query)) {
        echo "Service updated successfully.";
    } else {
        echo "Error: " . mysqli_error($con);
    }
} else {
    echo "Error: Missing service ID.";
}

mysqli_close($con);
?>