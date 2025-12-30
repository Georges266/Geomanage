<?php
session_start();
include 'includes/connect.php';

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    echo "Unauthorized";
    exit();
}

// Validate required fields
if (!isset($_POST['equipment_name']) || !isset($_POST['equipment_type'])) {
    echo "Missing required fields";
    exit();
}

// Get and sanitize data
$equipment_name = mysqli_real_escape_string($con, trim($_POST['equipment_name']));
$equipment_type = mysqli_real_escape_string($con, trim($_POST['equipment_type']));
$serial_number = mysqli_real_escape_string($con, trim($_POST['serial_number'] ?? ''));
$model = mysqli_real_escape_string($con, trim($_POST['model'] ?? ''));
$cost = floatval($_POST['cost'] ?? 0);
$date = !empty($_POST['date']) ? mysqli_real_escape_string($con, $_POST['date']) : date('Y-m-d');
$status = mysqli_real_escape_string($con, $_POST['status'] ?? 'Available');

// Insert equipment
$insertQuery = "INSERT INTO equipment 
    (equipment_name, equipment_type, serial_number, model, cost, date, status) 
    VALUES 
    ('$equipment_name', '$equipment_type', '$serial_number', '$model', $cost, '$date', '$status')";

if (mysqli_query($con, $insertQuery)) {
    echo 'success';
} else {
    echo 'Database error: ' . mysqli_error($con);
}

mysqli_close($con);
?>