<?php
include 'includes/connect.php';

if (isset($_POST['service_name'])) {
    $name = mysqli_real_escape_string($con, $_POST['service_name']);
    $min = (float)$_POST['min_price'];
    $description = mysqli_real_escape_string($con, $_POST['description']);

    // Server-side validation
    if (empty($name) || empty($description)) {
        echo "Error: All fields are required.";
        exit;
    }

    if ($min < 0) {
        echo "Error: Price cannot be negative.";
        exit;
    }

    $query = "INSERT INTO service (service_name, min_price, description, status) 
              VALUES ('$name', '$min', '$description', 'active')";

    if (mysqli_query($con, $query)) {
        echo "Service added successfully!";
    } else {
        echo "Error: " . mysqli_error($con);
    }
} else {
    echo "Error: Missing required fields.";
}

mysqli_close($con);
?>