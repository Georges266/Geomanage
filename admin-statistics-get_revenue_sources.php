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

// Get revenue from Projects by month for selected year
$sqlProjects = "SELECT 
                    DATE_FORMAT(p.start_date, '%Y-%m') as month,
                    SUM(p.total_cost) as revenue
                FROM project p
                WHERE p.status = 'done'
                    AND YEAR(p.start_date) = $year
                GROUP BY DATE_FORMAT(p.start_date, '%Y-%m')
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
$resultProjects = mysqli_query($con, $sqlProjects);
$resultLL = mysqli_query($con, $sqlLandListings);

// Collect data
$projectData = [];
$landListingData = [];

// Process Project data
if($resultProjects){
    while($row = mysqli_fetch_assoc($resultProjects)){
        $month = $row['month'];
        $projectData[$month] = (float)$row['revenue'];
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
$projectRevenue = [];
$landListingRevenue = [];

foreach($allMonths as $month){
    // Convert month to readable format (e.g., "2024-12" to "Dec")
    $date = DateTime::createFromFormat('Y-m', $month);
    $labels[] = $date->format('M');
    
    $projectRevenue[] = isset($projectData[$month]) ? $projectData[$month] : 0;
    $landListingRevenue[] = isset($landListingData[$month]) ? $landListingData[$month] : 0;
}

// Output JSON
echo json_encode([
    'labels' => $labels,
    'project_revenue' => $projectRevenue,
    'land_listing_revenue' => $landListingRevenue,
    'year' => $year
]);

ob_end_flush();
?>