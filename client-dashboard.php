<?php
include 'includes/header.php';
include 'includes/connect.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Client") {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user_id'];

// Get client_id from user_id
$q1 = "SELECT client_id FROM client WHERE user_id = $user";
$r = mysqli_query($con, $q1);

if (mysqli_num_rows($r) == 0) {
    echo "Client record not found.";
    exit();
}

$row_client = mysqli_fetch_assoc($r);
$client = $row_client['client_id'];

// Count pending requests
$query = "SELECT * FROM service_request WHERE status = 'Pending' AND client_id = $client";
$result = mysqli_query($con, $query);
$count_pending_request = mysqli_num_rows($result);

// Count active projects
$q2 = "SELECT p.* FROM project p
       JOIN service_request sr ON p.project_id = sr.project_id
       WHERE sr.client_id = $client AND p.status = 'In Progress'";
$r2 = mysqli_query($con, $q2);
$count_active_project = mysqli_num_rows($r2);

// Count all requests
$q3 = "SELECT * FROM service_request WHERE client_id = $client";
$r3 = mysqli_query($con, $q3);
$count_all_request = mysqli_num_rows($r3);

// Count finished projects
$q4 = "SELECT p.* FROM project p
       JOIN service_request sr ON p.project_id = sr.project_id
       WHERE sr.client_id = $client AND p.status = 'Completed'";
$r4 = mysqli_query($con, $q4);
$count_finished_project = mysqli_num_rows($r4);

// Get all service requests with details
$q5 = "SELECT sr.*, s.service_name, s.min_price,s.max_price, l.land_address, l.land_number, p.project_id as proj_id
       FROM service_request sr
       JOIN service s ON sr.service_id = s.service_id
       JOIN land l ON sr.land_id = l.land_id
       LEFT JOIN project p ON sr.project_id = p.project_id
       WHERE sr.client_id = $client
       ORDER BY sr.request_date DESC";
$r5 = mysqli_query($con, $q5);

// Get active projects with full details
$q6 = "SELECT p.*, GROUP_CONCAT(DISTINCT l.land_number SEPARATOR ', ') as land_numbers
       FROM project p
       JOIN service_request sr ON p.project_id = sr.project_id
       JOIN land l ON sr.land_id = l.land_id
       WHERE sr.client_id = $client AND p.status = 'In Progress'
       GROUP BY p.project_id
       ORDER BY p.start_date DESC";
$r6 = mysqli_query($con, $q6);

// Get completed projects with full details
$q7 = "SELECT p.*, GROUP_CONCAT(DISTINCT l.land_number SEPARATOR ', ') as land_numbers
       FROM project p
       JOIN service_request sr ON p.project_id = sr.project_id
       JOIN land l ON sr.land_id = l.land_id
       WHERE sr.client_id = $client AND p.status = 'Completed'
       GROUP BY p.project_id
       ORDER BY p.end_date DESC";
$r7 = mysqli_query($con, $q7);

// Function to calculate project duration
function calculateDuration($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    
    if ($interval->y > 0) {
        return $interval->y . ' year' . ($interval->y > 1 ? 's' : '');
    } elseif ($interval->m > 0) {
        return $interval->m . ' month' . ($interval->m > 1 ? 's' : '');
    } else {
        return $interval->d . ' day' . ($interval->d > 1 ? 's' : '');
    }
}
?>

<!doctype html>
<html class="no-js" lang="en"> 
<body>
<div class="site-preloader-wrap">
    <div class="spinner"></div>
</div>

<!-- Page Header -->
<section class="page-header padding">
    <div class="container">
        <div class="page-content text-center">
            <h2>My Dashboard</h2>
            <p>Track your service requests and projects</p>
        </div>
    </div>
</section>

<!-- Dashboard Overview -->
<section class="contact-section padding bg-grey">
    <div class="dots"></div>
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-sm-6 padding-15">
                <div class="service-item box-shadow text-center">
                    <div class="counter" style="font-size: 36px; color: #ff7607;"><?php echo $count_pending_request; ?></div>
                    <h3>Pending Requests</h3>
                    <p>Requests pending review</p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 padding-15">
                <div class="service-item box-shadow text-center">
                    <div class="counter" style="font-size: 36px; color: #28a745;"><?php echo $count_active_project; ?></div>
                    <h3>Active Projects</h3>
                    <p>Projects in progress</p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 padding-15">
                <div class="service-item box-shadow text-center">
                    <div class="counter" style="font-size: 36px; color: #17a2b8;"><?php echo $count_all_request; ?></div>
                    <h3>Total Requests</h3>
                    <p>All-time submissions</p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 padding-15">
                <div class="service-item box-shadow text-center">
                    <div class="counter" style="font-size: 36px; color: #6c757d;"><?php echo $count_finished_project; ?></div>
                    <h3>Completed</h3>
                    <p>Finished projects</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Service Requests Status -->
<section class="service-section padding">
    <div class="container">
        <div class="section-heading mb-40">
            <span>Your Requests</span>
            <h2>Service Request Status</h2>
            <a href="services.php" class="default-btn" style="float: right;">New Request</a>
        </div>

        <div class="row">
            <?php 
            if (mysqli_num_rows($r5) > 0) {
                while ($row = mysqli_fetch_assoc($r5)) {
                    // Determine badge color based on status
                    $status = $row['status'];
                    switch (strtolower($status)) {
                        case 'pending':
                            $color = '#ffa500'; // orange
                            break;
                        case 'approved':
                            $color = '#28a745'; // green
                            break;
                        case 'rejected':
                            $color = '#dc3545'; // red
                            break;
                        case 'in progress':
                            $color = '#17a2b8'; // blue
                            break;
                        default:
                            $color = '#6c757d'; // gray
                    }

                    // Convert date format
                    $submitted = date('M d, Y', strtotime($row['request_date']));
            ?>
            <div class="col-lg-6 padding-15">
                <div class="service-item box-shadow">
                    <div class="service-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="margin: 0;"><?php echo htmlspecialchars($row['service_name']); ?></h3>
                        <span class="status-badge" style="background: <?php echo $color; ?>; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            <?php echo ucfirst($status); ?>
                        </span>
                    </div>
                    <p><strong>Land Number:</strong> <?php echo htmlspecialchars($row['land_number']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($row['land_address']); ?></p>
                    <?php if( $row['status']=='Approved') {?>
                        <p><strong>Service Cost:</strong> $<?php echo number_format($row['min_price'], 2); ?></p><?php } else{?>
                    <p><strong>Minimum Service Cost:</strong> $<?php echo number_format($row['min_price'], 2); ?></p>
                    <p><strong> Maximum Service Cost:</strong> $<?php echo number_format($row['max_price'], 2); ?></p><?php }?>
                    <p><strong>Submitted:</strong> <?php echo $submitted; ?></p>
                    <?php if (!empty($row['proj_id'])) { ?>
                        <p><strong>Linked to Project:</strong> <?php echo htmlspecialchars($row['proj_id']); ?> - <a href="#project-<?php echo $row['proj_id']; ?>" style="color: #000000ff;">View Project</a></p>
                    <?php } ?>
                    <?php if (!empty($row['rejection_reason'])) { ?>
                        <p style="color: #dc3545;"><strong>Rejection Reason:</strong> <?php echo htmlspecialchars($row['rejection_reason']); ?></p>
                    <?php } ?>
                    <?php if (!empty($row['approval_status'])) { ?>
                        <p><strong>Approval Date:</strong> <?php echo date('M d, Y', strtotime($row['approval_status'])); ?></p>
                    <?php } ?>
                </div>
            </div>
            <?php 
                }
            } else {
                echo '<div class="col-12 text-center"><p style="font-size: 18px; color: #6c757d; padding: 40px;">No requests yet. <a href="services.php" style="color: #ff7607; font-weight: 600;">Submit one now</a>.</p></div>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Active Projects -->
<?php if (mysqli_num_rows($r6) > 0) { ?>
<section class="projects-section padding bg-grey">
    <div class="container">
        <div class="section-heading mb-40">
            <span>Current Work</span>
            <h2>Active Projects</h2>
        </div>
        <div class="row">
            <?php 
            mysqli_data_seek($r6, 0); // Reset pointer
            while ($project = mysqli_fetch_assoc($r6)) {
                $start_date_formatted = date('M d, Y', strtotime($project['start_date']));
                $end_date_formatted = date('M d, Y', strtotime($project['end_date']));
                
                // Get service requests for this project
                $proj_id = $project['project_id'];
                $q_services = "SELECT sr.request_id, s.service_name, s.min_price,s.max_price, l.land_number, sr.status
                               FROM service_request sr
                               JOIN service s ON sr.service_id = s.service_id
                               JOIN land l ON sr.land_id = l.land_id
                               WHERE sr.project_id = $proj_id AND sr.client_id = $client";
                $r_services = mysqli_query($con, $q_services);
            ?>
            <div class="col-lg-6 padding-15">
                <div class="project-card box-shadow" id="project-<?php echo $project['project_id']; ?>" style="background: #fff; padding: 25px; border-radius: 5px; border-left: 4px solid #ff7607;">
                    <div class="project-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="margin: 0; color: #263a4f;"><?php echo htmlspecialchars($project['project_name']); ?></h3>
                        <span class="status-badge" style="background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">In Progress</span>
                    </div>
                    <div class="project-info" style="margin-bottom: 20px;">
                        <p style="margin-bottom: 8px;"><strong>Land Numbers:</strong> <?php echo htmlspecialchars($project['land_numbers']); ?></p>
                        <p style="margin-bottom: 8px;"><strong>Start Date:</strong> <?php echo $start_date_formatted; ?></p>
                        <p style="margin-bottom: 8px;"><strong>Estimated Completion:</strong> <?php echo $end_date_formatted; ?></p>
                        <p style="margin-bottom: 8px;"><strong>Total Project Cost:</strong> $<?php echo number_format($project['total_cost'], 2); ?></p>
                        <p style="margin-bottom: 8px;"><strong>Payment Status:</strong> <span style="color: <?php echo $project['payment_status'] == 'Paid' ? '#28a745' : '#ffa500'; ?>;"><?php echo htmlspecialchars($project['payment_status']); ?></span></p>
                    </div>
                    <?php if (!empty($project['description'])) { ?>
                        <p style="margin-bottom: 15px; color: #6c757d; font-size: 14px;"><?php echo htmlspecialchars($project['description']); ?></p>
                    <?php } ?>
                    
                    <!-- Service Breakdown -->
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                        <h4 style="margin: 0 0 10px 0; font-size: 16px; color: #263a4f;">Service Cost Breakdown</h4>
                        <?php 
                        if (mysqli_num_rows($r_services) > 0) {
                            while ($service = mysqli_fetch_assoc($r_services)) {
                        ?>
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #dee2e6;">
                            <div>
                                <strong style="color: #263a4f;"><?php echo htmlspecialchars($service['service_name']); ?></strong>
                                <br>
                                <small style="color: #6c757d;">Land: <?php echo htmlspecialchars($service['land_number']); ?></small>
                            </div>
                            <div style="text-align: right;">
                                <strong style="color: #ff7607;">$<?php echo number_format($service['min_price'], 2); ?></strong>
                            </div>
                        </div>
                        <?php 
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php } ?>

<!-- Completed Projects -->
<?php if (mysqli_num_rows($r7) > 0) { ?>
<section class="service-section padding">
    <div class="container">
        <div class="section-heading mb-40">
            <span>Past Work</span>
            <h2>Completed Projects</h2>
        </div>
        <div class="row">
            <?php 
            mysqli_data_seek($r7, 0); // Reset pointer
            while ($project = mysqli_fetch_assoc($r7)) {
                $completed_date = date('M d, Y', strtotime($project['end_date']));
                $duration = calculateDuration($project['start_date'], $project['end_date']);
                
                // Get service requests for this project
                $proj_id = $project['project_id'];
                $q_services = "SELECT sr.request_id, s.service_name, s.min_price,s.max_price, l.land_number, sr.status
                               FROM service_request sr
                               JOIN service s ON sr.service_id = s.service_id
                               JOIN land l ON sr.land_id = l.land_id
                               WHERE sr.project_id = $proj_id AND sr.client_id = $client";
                $r_services = mysqli_query($con, $q_services);
            ?>
            <div class="col-lg-6 padding-15">
                <div class="project-card box-shadow" id="project-<?php echo $project['project_id']; ?>" style="background: #fff; padding: 25px; border-radius: 5px; border-left: 4px solid #6c757d;">
                    <div class="project-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="margin: 0; color: #263a4f;"><?php echo htmlspecialchars($project['project_name']); ?></h3>
                        <span class="status-badge" style="background: #6c757d; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Completed</span>
                    </div>
                    <div class="project-info" style="margin-bottom: 20px;">
                        <p style="margin-bottom: 8px;"><strong>Land Numbers:</strong> <?php echo htmlspecialchars($project['land_numbers']); ?></p>
                        <p style="margin-bottom: 8px;"><strong>Completed:</strong> <?php echo $completed_date; ?></p>
                        <p style="margin-bottom: 8px;"><strong>Duration:</strong> <?php echo $duration; ?></p>
                        <p style="margin-bottom: 8px;"><strong>Total Project Cost:</strong> $<?php echo number_format($project['total_cost'], 2); ?></p>
                        <p style="margin-bottom: 8px;"><strong>Payment Status:</strong> <span style="color: <?php echo $project['payment_status'] == 'Paid' ? '#28a745' : '#dc3545'; ?>;"><?php echo htmlspecialchars($project['payment_status']); ?></span></p>
                    </div>
                    <?php if (!empty($project['description'])) { ?>
                        <p style="margin-bottom: 15px; color: #6c757d; font-size: 14px;"><?php echo htmlspecialchars($project['description']); ?></p>
                    <?php } ?>
                    
                    <!-- Service Breakdown -->
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                        <h4 style="margin: 0 0 10px 0; font-size: 16px; color: #263a4f;">Service Cost Breakdown</h4>
                        <?php 
                        if (mysqli_num_rows($r_services) > 0) {
                            while ($service = mysqli_fetch_assoc($r_services)) {
                        ?>
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #dee2e6;">
                            <div>
                                <strong style="color: #263a4f;"><?php echo htmlspecialchars($service['service_name']); ?></strong>
                                <br>
                                <small style="color: #6c757d;">Land: <?php echo htmlspecialchars($service['land_number']); ?></small>
                            </div>
                            <div style="text-align: right;">
                                <strong style="color: #28a745;">$<?php echo number_format($service['min_price'], 2); ?></strong>
                            </div>
                        </div>
                        <?php 
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php } ?>

<!-- No Projects Message -->
<?php if (mysqli_num_rows($r6) == 0 && mysqli_num_rows($r7) == 0 && $count_all_request == 0) { ?>
<section class="service-section padding bg-grey">
    <div class="container">
        <div class="text-center" style="padding: 60px 20px;">
            <div style="font-size: 80px; color: #e9ecef; margin-bottom: 20px;">
                <i class="flaticon-construction"></i>
            </div>
            <h3 style="color: #263a4f; margin-bottom: 15px;">No Projects Yet</h3>
            <p style="font-size: 18px; color: #6c757d; margin-bottom: 30px;">
                Start your first project by submitting a service request
            </p>
            <a href="services.php" class="default-btn">Request a Service</a>
        </div>
    </div>
</section>
<?php } ?>

<?php include 'includes/footer.html'; ?>

</body>
</html>