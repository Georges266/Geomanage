<?php
include 'includes/connect.php';
header('Content-Type: application/json');

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get monthly revenue from service requests
$revenueQuery = "
    SELECT 
        DATE_FORMAT(request_date, '%Y-%m') as month,
        SUM(price) as revenue
    FROM service_request
    WHERE status = 'approved' 
        AND YEAR(request_date) = $year
    GROUP BY DATE_FORMAT(request_date, '%Y-%m')
";

$revenueResult = mysqli_query($con, $revenueQuery);
$revenueData = [];
while($row = mysqli_fetch_assoc($revenueResult)) {
    $revenueData[$row['month']] = floatval($row['revenue']);
}

// Get monthly revenue from land listings
$landRevenueQuery = "
    SELECT 
        DATE_FORMAT(listing_date, '%Y-%m') as month,
        SUM(company_profit) as revenue
    FROM land_listing
    WHERE status = 'sold' 
        AND YEAR(listing_date) = $year
    GROUP BY DATE_FORMAT(listing_date, '%Y-%m')
";

$landRevenueResult = mysqli_query($con, $landRevenueQuery);
while($row = mysqli_fetch_assoc($landRevenueResult)) {
    if(isset($revenueData[$row['month']])) {
        $revenueData[$row['month']] += floatval($row['revenue']);
    } else {
        $revenueData[$row['month']] = floatval($row['revenue']);
    }
}

// Get monthly expenses
$expensesQuery = "
    SELECT 
        DATE_FORMAT(expense_date, '%Y-%m') as month,
        SUM(amount) as total_expenses
    FROM expense
    WHERE YEAR(expense_date) = $year
    GROUP BY DATE_FORMAT(expense_date, '%Y-%m')
";

$expensesResult = mysqli_query($con, $expensesQuery);
$expensesData = [];
while($row = mysqli_fetch_assoc($expensesResult)) {
    $expensesData[$row['month']] = floatval($row['total_expenses']);
}

// Combine data for all 12 months
$months = [];
for($i = 1; $i <= 12; $i++) {
    $monthKey = sprintf("%d-%02d", $year, $i);
    $monthName = date('F Y', strtotime($monthKey . '-01'));
    
    $revenue = isset($revenueData[$monthKey]) ? $revenueData[$monthKey] : 0;
    $expenses = isset($expensesData[$monthKey]) ? $expensesData[$monthKey] : 0;
    $profit = $revenue - $expenses;
    $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
    
    $months[] = [
        'month' => $monthName,
        'revenue' => round($revenue, 2),
        'expenses' => round($expenses, 2),
        'profit' => round($profit, 2),
        'margin' => round($margin, 2)
    ];
}

// Reverse to show most recent first
$months = array_reverse($months);

echo json_encode($months);
?>