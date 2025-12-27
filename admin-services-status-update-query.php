<?php
include 'includes/connect.php';

if(isset($_POST['request_id'], $_POST['status'])) {
    $id = mysqli_real_escape_string($con, $_POST['request_id']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    
    // Initialize variables
    $price = isset($_POST['price']) ? mysqli_real_escape_string($con, $_POST['price']) : null;
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($con, $_POST['notes']) : null;

    // Validate price only if status is 'approved'
    if ($status === 'approved') {
        if (empty($price)) {
            echo "error: Price is required for approval";
            exit;
        }

        // Fetch min and max price from the database for this request/service
        $queryPrice = "SELECT min_price, max_price FROM service_request sr 
                       JOIN service s ON sr.service_id = s.service_id 
                       WHERE sr.request_id = '$id' LIMIT 1";
        $resultPrice = mysqli_query($con, $queryPrice);

        if ($resultPrice && mysqli_num_rows($resultPrice) > 0) {
            $row = mysqli_fetch_assoc($resultPrice);
            $minPrice = floatval($row['min_price']);
            $maxPrice = floatval($row['max_price']);

            if ($price < $minPrice || $price > $maxPrice) {
                echo "error: Price must be between $minPrice and $maxPrice";
                exit;
            }
        }
        
        // Update query for approval
        $query = "UPDATE service_request 
                  SET status='$status', 
                      price='$price', 
                      approval_status=CURDATE() 
                  WHERE request_id='$id'";
    } 
    // Handle rejection
    else if ($status === 'rejected') {
        if (empty($notes)) {
            echo "error: Rejection reason is required";
            exit;
        }
        
        // Update query for rejection
        $query = "UPDATE service_request 
                  SET status='$status', 
                      rejection_reason='$notes' 
                  WHERE request_id='$id'";
    } 
    else {
        echo "error: Invalid status";
        exit;
    }

    // Execute the query
    if(mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($con);
    }
} else {
    echo "error: Missing required parameters";
}

mysqli_close($con);
?>