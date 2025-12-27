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

// Get and validate year parameter
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Validate year is reasonable (between 2000 and current year + 1)
$currentYear = (int)date('Y');
if($year < 2000 || $year > $currentYear + 1) {
    $year = $currentYear;
}

// Get revenue from Service Requests by month for selected year
$sqlServiceRequests = "SELECT 
                        DATE_FORMAT(sr.request_date, '%Y-%m') as month,
                        SUM(sr.price) as revenue
                    FROM service_request sr
                    WHERE sr.status = 'approved'
                        AND YEAR(sr.request_date) = $year
                    GROUP BY DATE_FORMAT(sr.request_date, '%Y-%m')
                    ORDER BY month";

// Get revenue from Land Listings by month for selected year
$sqlLandListings = "SELECT 
                        DATE_FORMAT(l.listing_date, '%Y-%m') as month,
                        SUM(l.company_profit) as revenue
                    FROM land_listing l
                    WHERE YEAR(l.listing_date) = $year and l.status='sold'
                    GROUP BY DATE_FORMAT(l.listing_date, '%Y-%m')
                    ORDER BY month";

// Execute queries
$resultSR = mysqli_query($con, $sqlServiceRequests);
$resultLL = mysqli_query($con, $sqlLandListings);

// Collect data
$serviceRequestData = [];
$landListingData = [];

// Process Service Request data
if($resultSR){
    while($row = mysqli_fetch_assoc($resultSR)){
        $month = $row['month'];
        $serviceRequestData[$month] = (float)$row['revenue'];
    }
}

// Process Land Listing data
if($resultLL){
    while($row = mysqli_fetch_assoc($resultLL)){
        $month = $row['month'];
        $landListingData[$month] = (float)$row['revenue'];
    }
}

// Always generate all 12 months for the selected year
$allMonths = [];
for($m = 1; $m <= 12; $m++){
    $allMonths[] = sprintf('%d-%02d', $year, $m);
}

// Prepare final arrays with 0 for missing months
$labels = [];
$serviceRequestRevenue = [];
$landListingRevenue = [];

foreach($allMonths as $month){
    // Convert month to readable format (e.g., "2024-12" to "Dec")
    $date = DateTime::createFromFormat('Y-m', $month);
    $labels[] = $date->format('M');
    
    $serviceRequestRevenue[] = isset($serviceRequestData[$month]) ? $serviceRequestData[$month] : 0;
    $landListingRevenue[] = isset($landListingData[$month]) ? $landListingData[$month] : 0;
}

// Output JSON
echo json_encode([
    'labels' => $labels,
    'service_request_revenue' => $serviceRequestRevenue,
    'land_listing_revenue' => $landListingRevenue,
    'year' => $year
]);

ob_end_flush();
?>