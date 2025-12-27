<?php
// Start output buffering
ob_start();

// Set JSON header immediately
header('Content-Type: application/json');

// Suppress error display
ini_set('display_errors', 0);

// Include database connection
include 'includes/connect.php';

// Clear any output from includes
ob_clean();

// Validate and sanitize period input
$allowedPeriods = ['Last_30_days', 'Last_3_months', 'YTD', 'Last_Year'];
$period = isset($_GET['period']) && in_array($_GET['period'], $allowedPeriods) 
    ? $_GET['period'] 
    : 'Last_30_days';

// Get current year safely
$year = (int)date('Y');

// Build SQL based on period
switch($period){
    case 'Last_30_days':
        // Last 30 days
        $sql = "SELECT 
                    s.service_name,
                    SUM(sr.price) AS total_revenue
                FROM service_request sr
                INNER JOIN service s ON s.service_id = sr.service_id
                WHERE sr.status = 'approved'
                    AND sr.request_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY s.service_id, s.service_name
                ORDER BY total_revenue DESC";
        break;
        
    case 'Last_3_months':
        // Last 3 months
        $sql = "SELECT 
                    s.service_name,
                    SUM(sr.price) AS total_revenue
                FROM service_request sr
                INNER JOIN service s ON s.service_id = sr.service_id
                WHERE sr.status = 'approved'
                    AND sr.request_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                GROUP BY s.service_id, s.service_name
                ORDER BY total_revenue DESC";
        break;
        
    case 'YTD':
        // Year to Date (from January 1 to today)
        $sql = "SELECT 
                    s.service_name,
                    SUM(sr.price) AS total_revenue
                FROM service_request sr
                INNER JOIN service s ON s.service_id = sr.service_id
                WHERE sr.status = 'approved'
                    AND YEAR(sr.request_date) = $year
                    AND sr.request_date <= CURDATE()
                GROUP BY s.service_id, s.service_name
                ORDER BY total_revenue DESC";
        break;
        
    case 'Last_Year':
        // Last year (all of previous year)
        $lastYear = $year - 1;
        $sql = "SELECT 
                    s.service_name,
                    SUM(sr.price) AS total_revenue
                FROM service_request sr
                INNER JOIN service s ON s.service_id = sr.service_id
                WHERE sr.status = 'approved'
                    AND YEAR(sr.request_date) = $lastYear
                GROUP BY s.service_id, s.service_name
                ORDER BY total_revenue DESC";
        break;
}

// Execute query
$result = mysqli_query($con, $sql);

// Fetch results
$labels = [];
$values = [];

if($result){
    while($row = mysqli_fetch_assoc($result)){
        $labels[] = $row['service_name'];
        $values[] = (float)$row['total_revenue'];
    }
}

// If no data, provide default
if(empty($labels)){
    $labels = ['No Data'];
    $values = [0];
}

// Output JSON
echo json_encode([
    'labels' => $labels,
    'values' => $values
]);

ob_end_flush();
?>