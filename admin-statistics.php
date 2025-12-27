<?php
include 'includes/header.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: ../no_access.php");
    exit();
}
?>
<?php
include 'includes/connect.php';
?>
<?php
$sql = "SELECT 
    s.service_name as service_name,
    COUNT(sr.request_id) as total_requests,
    SUM(sr.price) as total_revenue
FROM 
    service s
    INNER JOIN service_request sr ON s.service_id = sr.service_id
    WHERE sr.status='approved'
GROUP BY 
    s.service_id, s.service_name
ORDER BY 
    total_revenue DESC";

$result = mysqli_query($con, $sql);
$revenueData = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue & Analytics Dashboard</title>
    <!-- Load Chart.js in head -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="site-preloader-wrap">
    <div class="spinner"></div>
</div>

<!-- Page Header -->
<section class="page-header padding bg-grey">
    <div class="container">
        <div class="row d-flex align-items-center">
            <div class="col-lg-8">
                <h1>Revenue & Analytics Dashboard</h1>
                <p>Track financial performance and business insights</p>
            </div>
           
        </div>
    </div>
</section>

<!-- Main Dashboard -->
<section class="padding">
    <div class="container">
        <!-- Key Metrics Summary -->
        <div class="row mb-40">
            <div class="col-md-3 padding-10">
                <div class="metric-card box-shadow text-center" style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px;">
                    <i class="fas fa-dollar-sign" style="font-size: 36px; margin-bottom: 15px;"></i>
                    <h3>$124,580</h3>
                    <p style="margin: 0; opacity: 0.9;">Total Revenue</p>
                    <small style="opacity: 0.8;">+12% from last period</small>
                </div>
            </div>
            <div class="col-md-3 padding-10">
                <div class="metric-card box-shadow text-center" style="padding: 20px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 8px;">
                    <i class="fas fa-project-diagram" style="font-size: 36px; margin-bottom: 15px;"></i>
                    <h3>24</h3>
                    <p style="margin: 0; opacity: 0.9;">Active Projects</p>
                    <small style="opacity: 0.8;">5 completed this month</small>
                </div>
            </div>
            <div class="col-md-3 padding-10">
                <div class="metric-card box-shadow text-center" style="padding: 20px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border-radius: 8px;">
                    <i class="fas fa-users" style="font-size: 36px; margin-bottom: 15px;"></i>
                    <h3>$45,200</h3>
                    <p style="margin: 0; opacity: 0.9;">Salary Costs</p>
                    <small style="opacity: 0.8;">-3% from last month</small>
                </div>
            </div>
            <div class="col-md-3 padding-10">
                <div class="metric-card box-shadow text-center" style="padding: 20px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 8px;">
                    <i class="fas fa-chart-line" style="font-size: 36px; margin-bottom: 15px;"></i>
                    <h3>38%</h3>
                    <p style="margin: 0; opacity: 0.9;">Profit Margin</p>
                    <small style="opacity: 0.8;">+5% improvement</small>
                </div>
            </div>
        </div>

        <!-- Charts and Graphs Row -->
        <div class="row mb-40">
            <!-- Revenue Chart Section -->
            <div class="col-lg-8 padding-10">
                <div class="service-item box-shadow" style="padding: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 style="margin: 0;">Service Revenue Distribution</h4>
                        <select class="form-control" id="periodFilter" style="width: auto;">
                            <option value="Last_30_days"> Last 30 days</option>
                            <option value="Last_3_months">Last 3 months</option>
                            <option value="YTD">YTD</option>
                            <option value="Last_Year">Last Year</option>
                        </select>
                    </div>
                    <div style="height: 400px; background: #f8f9fa; border-radius: 5px; padding: 20px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Service Performance -->
            <div class="col-lg-4 padding-10">
                <div class="service-item box-shadow" style="padding: 20px;">
                    <h4 class="mb-3">Top Performing Services</h4>
                    <div class="service-performance" id="servicePerformanceList">
                        <?php
                        // Total revenue for calculating percentages
                        $totalRevenue = array_sum(array_column($revenueData, 'total_revenue'));
                        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF'];

                        // Loop through all services
                        $i = 0;
                        foreach($revenueData as $service) {
                            $percentage = round(($service['total_revenue'] / $totalRevenue) * 100);
                            $color = $colors[$i % count($colors)];
                        ?>
                        <div class="performance-item mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span style="font-weight: 600;"><?= htmlspecialchars($service['service_name']) ?></span>
                                <span style="font-weight: 600; color: <?= $color ?>;">$<?= number_format($service['total_revenue']) ?></span>
                            </div>
                            <div class="progress" style="height: 8px; background: #e9ecef;">
                                <div class="progress-bar" style="width: <?= $percentage ?>%; background: <?= $color ?>;"></div>
                            </div>
                            <small class="text-muted"><?= $percentage ?>% of total revenue</small>
                        </div>
                        <?php
                            $i++;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row: Expenses and Profitability -->
        <div class="row mb-40">
            <!-- Expense Breakdown -->
            <div class="col-lg-6 padding-10">
                <div class="service-item box-shadow" style="padding: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 style="margin: 0;">Expense Breakdown</h4>
                        <select class="form-control" id="yearSelect" style="width: auto;">
                            <?php
                            // Generate year options (current year and 4 previous years)
                            $currentYear = date('Y');
                            for($i = 0; $i < 5; $i++) {
                                $year = $currentYear - $i;
                                $selected = ($i === 0) ? 'selected' : '';
                                echo "<option value='$year' $selected>$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="chart-placeholder" style="height: 300px; background: #f8f9fa; border-radius: 5px; padding: 10px;">
                        <canvas id="expensesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Profitability Metrics -->
            <!-- Revenue Sources Comparison -->
            <div class="col-lg-6 padding-10">
                <div class="service-item box-shadow" style="padding: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 style="margin: 0;">Revenue Sources Comparison</h4>
                        <select class="form-control" id="revenueSourceYear" style="width: auto;">
                            <?php
                            // Generate year options (current year and 4 previous years)
                            $currentYear = date('Y');
                            for($i = 0; $i < 5; $i++) {
                                $year = $currentYear - $i;
                                $selected = ($i === 0) ? 'selected' : '';
                                echo "<option value='$year' $selected>$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div style="height: 300px; background: #f8f9fa; border-radius: 5px; padding: 10px;">
                        <canvas id="revenueSourceChart"></canvas>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const revenueSourceYear = document.getElementById('revenueSourceYear');
                let revenueSourceChart;

                function loadRevenueSourceChart(year) {
                    fetch('admin-statistics-get_revenue_sources.php?year=' + year)
                        .then(res => {
                            if (!res.ok) {
                                throw new Error('HTTP error! status: ' + res.status);
                            }
                            return res.json();
                        })
                        .then(data => {
                            console.log('Revenue sources data:', data);

                            const chartData = {
                                labels: data.labels,
                                datasets: [
                                    {
                                        label: 'Service Requests',
                                        data: data.service_request_revenue,
                                        backgroundColor: '#36A2EB',
                                        borderColor: '#36A2EB',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Land Listings',
                                        data: data.land_listing_revenue,
                                        backgroundColor: '#4BC0C0',
                                        borderColor: '#4BC0C0',
                                        borderWidth: 1
                                    }
                                ]
                            };

                            if(revenueSourceChart){
                                revenueSourceChart.data = chartData;
                                revenueSourceChart.options.plugins.title.text = 'Revenue Sources - ' + year;
                                revenueSourceChart.update();
                            } else {
                                const ctx = document.getElementById('revenueSourceChart').getContext('2d');
                                revenueSourceChart = new Chart(ctx, {
                                    type: 'bar',
                                    data: chartData,
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: { 
                                                position: 'top',
                                                labels: {
                                                    font: { size: 12 },
                                                    padding: 10
                                                }
                                            },
                                            title: { 
                                                display: true, 
                                                text: 'Revenue Sources - ' + year,
                                                font: { size: 14 }
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    footer: function(tooltipItems) {
                                                        let total = 0;
                                                        tooltipItems.forEach(item => {
                                                            total += item.parsed.y;
                                                        });
                                                        return 'Total: $' + total.toLocaleString();
                                                    },
                                                    label: function(context) {
                                                        return context.dataset.label + ': $' + 
                                                            context.parsed.y.toLocaleString();
                                                    }
                                                }
                                            }
                                        },
                                        scales: {
                                            x: { 
                                                stacked: true,
                                                grid: { display: false }
                                            },
                                            y: { 
                                                stacked: true, 
                                                beginAtZero: true,
                                                ticks: {
                                                    callback: function(value) {
                                                        return '$' + value.toLocaleString();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                        })
                        .catch(err => {
                            console.error('Error loading revenue sources:', err);
                            alert('Failed to load revenue sources data: ' + err.message);
                        });
                }

                // Initial load
                if(revenueSourceYear) {
                    loadRevenueSourceChart(revenueSourceYear.value);
                    
                    // Listen to year changes
                    revenueSourceYear.addEventListener('change', function(){
                        loadRevenueSourceChart(this.value);
                    });
                }
            });
            </script>
        </div>

       <!-- Third Row: Detailed Reports -->
        <div class="row">
            <!-- Monthly Performance -->
            <div class="col-lg-12 padding-10">
                <div class="service-item box-shadow" style="padding: 20px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 style="margin: 0;">Monthly Performance</h4>
                        <select class="form-control" id="performanceYear" style="width: auto;">
                            <?php
                            $currentYear = date('Y');
                            for($i = 0; $i < 5; $i++) {
                                $year = $currentYear - $i;
                                $selected = ($i === 0) ? 'selected' : '';
                                echo "<option value='$year' $selected>$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="table" style="font-size: 13px;">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Revenue</th>
                                    <th>Expenses</th>
                                    <th>Profit</th>
                                    <th>Margin</th>
                                </tr>
                            </thead>
                            <tbody id="performanceTableBody">
                                <tr>
                                    <td colspan="5" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export and Actions -->
        <div class="row mt-40">
            <div class="col-12 text-center">
                <div class="service-item box-shadow" style="padding: 20px;">
                    <h4 class="mb-3">Export Reports</h4>
                    <div class="export-actions" style="gap: 15px; display: flex; justify-content: center;">
                        <button class="default-btn">
                            <i class="fas fa-file-pdf"></i> Export PDF Report
                        </button>
                        <button class="default-btn">
                            <i class="fas fa-file-excel"></i> Export Excel Data
                        </button>
                        <button class="default-btn">
                            <i class="fas fa-chart-bar"></i> Generate Custom Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
.metric-card {
    transition: transform 0.3s ease;
}
.metric-card:hover {
    transform: translateY(-5px);
}
.chart-placeholder {
    border: 2px dashed #dee2e6;
}
.progress {
    border-radius: 4px;
}
.progress-bar {
    border-radius: 4px;
}
.performance-item {
    padding: 10px;
    border-radius: 5px;
}
.performance-item:hover {
    background: #f8f9fa;
}
.metric-row {
    transition: all 0.3s ease;
}
.metric-row:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.client-item {
    transition: all 0.3s ease;
}
.client-item:hover {
    background: #e9ecef !important;
    transform: translateX(3px);
}
.export-actions .default-btn {
    margin: 0 5px;
}
/* Remove hover effect from Monthly Performance table */
#performanceTableBody tr:hover {
    background-color: transparent !important;
}
#performanceTableBody tr {
    transition: none !important;
}
</style>





<script>
(function() {
    // Revenue Chart using IIFE to avoid conflicts
    const ctx = document.getElementById('revenueChart');
    const periodSelect = document.getElementById('periodFilter');
    const serviceList = document.getElementById('servicePerformanceList');
    let revenueChart;

    function loadRevenueChart(period = 'monthly') {
        console.log('Loading revenue chart for period:', period);
        
        fetch('admin-statistics-get_revenue.php?period=' + period)
            .then(res => {
                if (!res.ok) {
                    throw new Error('HTTP error! status: ' + res.status);
                }
                return res.json();
            })
            .then(data => {
                console.log('Revenue data received:', data);
                
                const labels = data.labels;
                const values = data.values;
                const colors = ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF'];

                const chartData = {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors
                    }]
                };

                if(revenueChart){
                    // Update existing chart
                    revenueChart.data = chartData;
                    revenueChart.options.plugins.title.text = 'Service Revenue Distribution (' + period + ')';
                    revenueChart.update();
                } else {
                    // Create new chart
                    revenueChart = new Chart(ctx, {
                        type: 'pie',
                        data: chartData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { 
                                    position: 'right',
                                    labels: {
                                        font: { size: 12 },
                                        padding: 10
                                    }
                                },
                                title: { 
                                    display: true, 
                                    text: 'Revenue Overview (' + period + ')', 
                                    font: { size: 16 }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context){
                                            let value = context.raw;
                                            let sum = context.dataset.data.reduce((a,b)=>a+b,0);
                                            let perc = (value/sum*100).toFixed(1) + '%';
                                            return context.label + ': $' + value.toLocaleString() + ' (' + perc + ')';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Update the service performance list dynamically
                updateServiceList(labels, values, colors);
            })
            .catch(err => {
                console.error('Error loading revenue data:', err);
                alert('Failed to load revenue data: ' + err.message);
            });
    }

    function updateServiceList(labels, values, colors) {
        if (!serviceList) return;

        const totalRevenue = values.reduce((a, b) => a + b, 0);
        let html = '';

        labels.forEach((label, i) => {
            const value = values[i];
            const percentage = Math.round((value / totalRevenue) * 100);
            const color = colors[i % colors.length];

            html += `
                <div class="performance-item mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-weight: 600;">${label}</span>
                        <span style="font-weight: 600; color: ${color};">$${value.toLocaleString()}</span>
                    </div>
                    <div class="progress" style="height: 8px; background: #e9ecef;">
                        <div class="progress-bar" style="width: ${percentage}%; background: ${color};"></div>
                    </div>
                    <small class="text-muted">${percentage}% of total revenue</small>
                </div>
            `;
        });

        serviceList.innerHTML = html;
    }

    // Initialize when ready
    function initRevenueChart() {
        if(typeof Chart !== 'undefined' && ctx && periodSelect) {
            loadRevenueChart('monthly');
            
            periodSelect.addEventListener('change', function() {
                loadRevenueChart(this.value);
            });
        } else {
            console.error('Chart.js not loaded or elements not found');
        }
    }

    // Check if DOM is already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRevenueChart);
    } else {
        if (typeof Chart !== 'undefined') {
            initRevenueChart();
        } else {
            setTimeout(initRevenueChart, 500);
        }
    }
})();

// Expenses Chart
document.addEventListener('DOMContentLoaded', function() {
    const yearSelect = document.getElementById('yearSelect');
    let expensesChart;

    function loadExpenses(year){
        fetch('admin-statistics-get_expenses.php?year=' + year)
            .then(res => res.json())
            .then(data => {
                // Convert month format to readable labels
                const monthLabels = data.months.map(m => {
                    const date = new Date(m + '-01');
                    return date.toLocaleString('default', { month: 'short' });
                });

                const chartData = {
                    labels: monthLabels,
                    datasets: data.categories.map((cat, i) => ({
                        label: cat,
                        data: data.amounts[cat],
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'][i]
                    }))
                };

                if(expensesChart){
                    expensesChart.data = chartData;
                    expensesChart.options.plugins.title.text = 'Expenses Breakdown - ' + year;
                    expensesChart.update();
                } else {
                    const ctx = document.getElementById('expensesChart').getContext('2d');
                    expensesChart = new Chart(ctx, {
                        type: 'bar',
                        data: chartData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'top' },
                                title: { 
                                    display: true, 
                                    text: 'Expenses Breakdown - ' + year,
                                    font: { size: 16 }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': $' + 
                                                context.parsed.y.toLocaleString();
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: { stacked: true },
                                y: { 
                                    stacked: true, 
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error loading expenses:', error);
                alert('Failed to load expense data. Please try again.');
            });
    }

    // Initial load
    if(yearSelect) {
        loadExpenses(yearSelect.value);
        
        // Listen to year changes
        yearSelect.addEventListener('change', function(){
            loadExpenses(this.value);
        });
    }

    // Date range filter functionality
    const dateFilter = document.querySelector('.date-filter select');
    if (dateFilter) {
        dateFilter.addEventListener('change', function() {
            console.log('Date range changed to:', this.value);
        });
    }

    // Export button functionality
    const exportButtons = document.querySelectorAll('.export-actions .default-btn');
    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.textContent.trim();
            alert(`Preparing to ${action}...`);
        });
    });
});



// Monthly Performance Table
document.addEventListener('DOMContentLoaded', function() {
    const performanceYear = document.getElementById('performanceYear');
    const tableBody = document.getElementById('performanceTableBody');

    function loadMonthlyPerformance(year) {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
        
        fetch('admin-statistics-get_monthly_performance.php?year=' + year)
            .then(res => {
                if (!res.ok) {
                    throw new Error('HTTP error! status: ' + res.status);
                }
                return res.text(); // Get as text first to debug
            })
            .then(text => {
                console.log('Response:', text); // Debug: see what we got
                try {
                    const data = JSON.parse(text);
                    
                    // Check if response has an error
                    if (data.error) {
                        throw new Error(data.message);
                    }
                    
                    let html = '';
                    
                    data.forEach(row => {
                        const profitColor = row.profit >= 0 ? '#4caf50' : '#f44336';
                        const marginColor = row.margin >= 0 ? '#4caf50' : '#f44336';
                        
                        html += `
                            <tr>
                                <td>${row.month}</td>
                                <td>$${row.revenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>$${row.expenses.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td style="color: ${profitColor}; font-weight: 600;">
                                    $${row.profit.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                </td>
                                <td style="color: ${marginColor}; font-weight: 600;">
                                    ${row.margin.toFixed(1)}%
                                </td>
                            </tr>
                        `;
                    });
                    
                    if(html === '') {
                        html = '<tr><td colspan="5" class="text-center">No data available for this year</td></tr>';
                    }
                    
                    tableBody.innerHTML = html;
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Response text:', text);
                    throw new Error('Invalid JSON response from server');
                }
            })
            .catch(err => {
                console.error('Error loading monthly performance:', err);
                tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Failed to load data: ${err.message}</td></tr>`;
            });
    }

    if(performanceYear) {
        loadMonthlyPerformance(performanceYear.value);
        
        performanceYear.addEventListener('change', function() {
            loadMonthlyPerformance(this.value);
        });
    }
});

</script>

<?php include 'includes/footer.html'; ?>
 