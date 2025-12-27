<?php
include 'includes/connect.php';

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Months of the year
$months = [];
for($i=1; $i<=12; $i++){
    $months[] = sprintf('%04d-%02d', $year, $i);
}

$categories = ['Maintenance','Salaries','Equipment'];
$amounts = [];
foreach($categories as $cat){
    $amounts[$cat] = array_fill(0, count($months), 0);
}

// Maintenance
$sql = "SELECT DATE_FORMAT(maintenance_date, '%Y-%m') as month, SUM(total_cost) as amount
        FROM maintenance
        WHERE YEAR(maintenance_date) = $year
        GROUP BY month";
$result = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($result)){
    $index = array_search($row['month'], $months);
    if($index !== false) $amounts['Maintenance'][$index] = $row['amount'];
}

// Salaries
$sql = "SELECT DATE_FORMAT(date_paid, '%Y-%m') as month, SUM(amount) as amount
        FROM salary_payment
        WHERE YEAR(date_paid) = $year
        GROUP BY month";
$result = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($result)){
    $index = array_search($row['month'], $months);
    if($index !== false) $amounts['Salaries'][$index] = $row['amount'];
}

// Equipment
$sql = "SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(cost) as amount
        FROM equipment
        WHERE YEAR(date) = $year
        GROUP BY month";
$result = mysqli_query($con, $sql);
while($row = mysqli_fetch_assoc($result)){
    $index = array_search($row['month'], $months);
    if($index !== false) $amounts['Equipment'][$index] = $row['amount'];
}

// Return JSON
echo json_encode([
    'months' => $months,
    'amounts' => $amounts,
    'categories' => $categories
]);
?>
