<?php
include 'includes/header.php';
include 'includes/connect.php';

// Check authorization
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "HR") {
    exit();
}

// Get HR ID
$user_id = $_SESSION['user_id'];
$get_hr_id = "SELECT `hr_id` FROM `hr` WHERE user_id = '$user_id'";
$result = mysqli_query($con, $get_hr_id);
$row = mysqli_fetch_assoc($result);
$hr_id = $row['hr_id'];

// ==========================================
// HANDLE FORM SUBMISSIONS
// ==========================================

// Handle Create Job Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_job'])) {
    $jobTitle = mysqli_real_escape_string($con, $_POST['job_title']);
    $numberOfPositions = intval($_POST['number_of_positions']);
    $jobType = mysqli_real_escape_string($con, $_POST['job_type']);
    $jobDescription = mysqli_real_escape_string($con, $_POST['job_description']);
    $responsibilities = mysqli_real_escape_string($con, $_POST['responsibilities']);
    $status = "open";

    $create_job = "INSERT INTO job_opportunity
        (job_title, number_of_positions, job_description, responsibilities, status, hr_id, job_type)
        VALUES
        ('$jobTitle', '$numberOfPositions', '$jobDescription', '$responsibilities', '$status', '$hr_id', '$jobType')";

    if (mysqli_query($con, $create_job)) {
        $_SESSION['success_message'] = "Job opportunity created successfully!";
        $_SESSION['job_title'] = $jobTitle;
    } else {
        $_SESSION['error_message'] = "Error creating job: " . mysqli_error($con);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle Edit Job Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_job'])) {
    $jobId = (int)$_POST['job_id'];
    $title = $_POST['job_title'];
    $positions = (int)$_POST['number_of_positions'];
    $type = $_POST['job_type'];
    $status = $_POST['status'];
    $desc = $_POST['job_description'];
    $resp = $_POST['responsibilities'];
    $req  = $_POST['requirements'];

    $stmt = $con->prepare("UPDATE job_opportunity 
        SET job_title=?, number_of_positions=?, job_type=?, status=?, job_description=?, responsibilities=?, requirements=? 
        WHERE job_id=?");
    $stmt->bind_param("sisssssi", $title, $positions, $type, $status, $desc, $resp, $req, $jobId);
    $ok = $stmt->execute();
    $stmt->close();

    $_SESSION['success_message'] = $ok ? "Job updated successfully." : "Failed to update job.";
    header("Location: hr-page1.php");
    exit();
}

// Handle Application Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['perform_action'])) {
    $applicationId = (int)$_POST['application_id'];
    $action = $_POST['action'] ?? '';

    // Normalize status values
    $newStatus = null;
    switch ($action) {
        case 'hire':
            $newStatus = 'Hired';
            break;
        case 'reject':
            $newStatus = 'Rejected';
            break;
        case 'schedule':
            $newStatus = 'Interview Scheduled';
            break;
        case 'reschedule':
            $newStatus = 'Interview Rescheduled';
            break;
    }

    if ($newStatus) {
        $newStatus = mysqli_real_escape_string($con, $newStatus);

        // Check if this is a schedule action
        if ($action === 'schedule' || $action === 'reschedule') {
            $interviewDate = mysqli_real_escape_string($con, $_POST['interview_date'] ?? '');
            $interviewTime = mysqli_real_escape_string($con, $_POST['interview_time'] ?? '');
            $interviewLocation = mysqli_real_escape_string($con, $_POST['interview_location'] ?? '');

            // Check for time conflicts
            $conflict_check = "SELECT ja.application_id, u.full_name, jo.job_title
                              FROM job_application ja
                              JOIN job_opportunity jo ON ja.job_id = jo.job_id
                              JOIN user u ON ja.user_id = u.user_id
                              WHERE jo.hr_id = '$hr_id'
                              AND ja.interview_date = '$interviewDate'
                              AND ja.interview_time = '$interviewTime'
                              AND ja.application_id != '$applicationId'
                              AND ja.status IN ('Interview Scheduled', 'Interview Rescheduled')";

            $conflict_result = mysqli_query($con, $conflict_check);

            if (mysqli_num_rows($conflict_result) > 0) {
                $conflict = mysqli_fetch_assoc($conflict_result);
                $_SESSION['error_message'] = "You already have an interview scheduled at this time with " . $conflict['full_name'] . " for " . $conflict['job_title'] . ".";
                header("Location: hr-page1.php");
                exit();
            }

            // Update with schedule info
            $update_query = "UPDATE job_application 
                            SET status = '$newStatus', 
                                interview_date = '$interviewDate',
                                interview_time = '$interviewTime',
                                interview_location = '$interviewLocation'
                            WHERE application_id = '$applicationId'";
        } else {
            // Regular update without schedule info
            $update_query = "UPDATE job_application 
                            SET status = '$newStatus'
                            WHERE application_id = '$applicationId'";
        }

        $ok = mysqli_query($con, $update_query);
        $_SESSION['success_message'] = $ok ? "Application updated successfully." : "Failed to update application.";
    } else {
        $_SESSION['error_message'] = "Invalid action.";
    }

    header("Location: hr-page1.php");
    exit();
}

// ==========================================
// FETCH DATA FOR MODALS
// ==========================================

$viewJob = null;
if (isset($_GET['action'], $_GET['job_id']) && $_GET['action'] === 'view_job') {
    $jobId = (int)$_GET['job_id'];
    $stmt = $con->prepare("SELECT * FROM job_opportunity WHERE job_id = ?");
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $result = $stmt->get_result();
    $viewJob = $result->fetch_assoc();
    $stmt->close();
}

$viewApplication = null;
if (isset($_GET['action'], $_GET['application_id']) && $_GET['action'] === 'view') {
    $appId = (int)$_GET['application_id'];
    $stmt = $con->prepare("
        SELECT ja.*, jo.job_title, u.full_name
        FROM job_application AS ja
        JOIN job_opportunity AS jo ON ja.job_id = jo.job_id
        JOIN user AS u ON ja.user_id = u.user_id
        WHERE ja.application_id = ?
    ");
    $stmt->bind_param("i", $appId);
    $stmt->execute();
    $result = $stmt->get_result();
    $viewApplication = $result->fetch_assoc();
    $stmt->close();
}

$selectedJob = null;
if (isset($_GET['action'], $_GET['job_id']) && $_GET['action'] === 'edit') {
    $jobId = (int)$_GET['job_id'];
    $stmt = $con->prepare("SELECT * FROM job_opportunity WHERE job_id = ?");
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $result = $stmt->get_result();
    $selectedJob = $result->fetch_assoc();
    $stmt->close();
}

$selectedApplication = null;
if (isset($_GET['action'], $_GET['application_id']) && $_GET['action'] === 'manage') {
    $appId = (int)$_GET['application_id'];
    $stmt = $con->prepare("
        SELECT ja.*, jo.job_title, u.full_name
        FROM job_application AS ja
        JOIN job_opportunity AS jo ON ja.job_id = jo.job_id
        JOIN user AS u ON ja.user_id = u.user_id
        WHERE ja.application_id = ?
    ");
    $stmt->bind_param("i", $appId);
    $stmt->execute();
    $result = $stmt->get_result();
    $selectedApplication = $result->fetch_assoc();
    $stmt->close();
}

// ==========================================
// FETCH LIST DATA
// ==========================================

// List all job opportunities
$show_oportunity = "SELECT * FROM `job_opportunity`";
$show_oportunity_result = mysqli_query($con, $show_oportunity);

// List job applications with filters
$search    = $_GET['search'] ?? '';
$position  = $_GET['position'] ?? '';
$status    = $_GET['status'] ?? '';

$sql = "SELECT ja.*, jo.job_title, u.full_name
        FROM job_application AS ja
        JOIN job_opportunity AS jo ON ja.job_id = jo.job_id
        JOIN user AS u ON ja.user_id = u.user_id
        WHERE 1=1";

$params = [];
$types  = "";

if (!empty($search)) {
    $sql .= " AND u.full_name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if (!empty($position)) {
    $sql .= " AND jo.job_title = ?";
    $params[] = $position;
    $types .= "s";
}

if (!empty($status)) {
    $sql .= " AND ja.status = ?";
    $params[] = $status;
    $types .= "s";
}

$stmt = $con->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$list_applications_result = $stmt->get_result();

// Check for flash messages
$showSuccess = false;
$successMsg = '';
if (isset($_SESSION['success_message'])) {
    $showSuccess = true;
    $successMsg = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
    if (isset($_SESSION['job_title'])) {
        unset($_SESSION['job_title']);
    }
}

$errorMsg = '';
if (isset($_SESSION['error_message'])) {
    $errorMsg = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<!doctype html>
<html class="no-js" lang="en">

<body>
    <div class="site-preloader-wrap">
        <div class="spinner"></div>
    </div>

    <!-- Page Header -->
    <section class="page-header padding bg-grey">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-lg-8">
                    <h1>HR Recruitment</h1>
                    <p>Manage job opportunities, applications, and interviews</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tab Navigation -->
    <section class="padding">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="tab-navigation mb-40">
                        <button class="tab-btn active" onclick="openTab('applications')">
                            <i class="fas fa-file-alt"></i> Applications (3)
                        </button>
                        <button class="tab-btn" onclick="openTab('opportunities')">
                            <i class="fas fa-briefcase"></i> Opportunities (2)
                        </button>
                        <button class="tab-btn" onclick="openTab('schedule')">
                            <i class="fas fa-calendar-alt"></i> Schedule (1)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Applications Tab -->
            <div id="applications" class="tab-content active">
                <!-- Search and Filter -->
                <form method="GET" action="hr-page1.php">
                    <div class="row mb-30">
                        <div class="col-md-12">
                            <div class="service-item box-shadow padding-15">
                                <div class="row">
                                    <div class="col-md-3 padding-10">
                                        <input type="text" name="search" class="form-control" placeholder="Search applicants...">
                                    </div>
                                    <div class="col-md-3 padding-10">
                                        <select name="position" class="form-control" style="height: 40px; padding: 8px 12px;">
                                            <option value="">All Positions</option>
                                            <option value="Land Surveyor">Land Surveyor</option>
                                            <option value="CAD Technician">CAD Technician</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 padding-10">
                                        <select name="status" class="form-control" style="height: 40px; padding: 8px 12px;">
                                            <option value="">All Status</option>
                                            <option value="New">New</option>
                                            <option value="Under Review">Under Review</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 padding-10">
                                        <button type="submit" class="default-btn" style="width: 100%; padding: 10px;">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Applications List -->
                <div class="row">
                    <?php while ($row = mysqli_fetch_assoc($list_applications_result)) { ?>
                        <div class="col-lg-4 col-md-6 padding-10">
                            <div class="service-item box-shadow" style="padding: 15px;">
                                <div class="service-content" style="padding: 0;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h4 style="font-size: 16px; margin: 0;"><?php echo $row['applicant_name']; ?></h4>
                                        <span class="status-badge status-new"><?php echo $row['status']; ?></span>
                                    </div>
                                    <p style="font-size: 13px; margin: 5px 0;"><strong>For:</strong> <?php echo $row['job_title']; ?></p>
                                    <p style="font-size: 12px; margin: 3px 0; color: #666;"><?php echo $row['application_date']; ?></p>
                                    <div class="dl-btn-group mt-2" style="gap: 5px;">
                                        <a href="hr-page1.php?action=view&application_id=<?php echo $row['application_id']; ?>" class="dl-btn"
                                            style="padding: 5px 10px; font-size: 12px;">View</a>
                                        <a href="hr-page1.php?action=manage&application_id=<?php echo $row['application_id']; ?>" class="dl-btn"
                                            style="padding: 5px 10px; font-size: 12px; background: #4caf50;"> Actions</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Opportunities Tab -->
            <div id="opportunities" class="tab-content">
                <!-- Create Job Button -->
                <div class="row mb-30">
                    <div class="col-md-12 text-right">
                        <a href="#" class="default-btn" onclick="event.preventDefault(); openModal('createJobModal')">
                            <i class="fas fa-plus"></i> Create New Job
                        </a>
                    </div>
                </div>
                <div class="row">
                    <?php while ($row = mysqli_fetch_assoc($show_oportunity_result)) {
                        $job_id = $row['job_id'];
                        $countQuery = "SELECT COUNT(*) AS apps FROM job_application WHERE job_id = '$job_id'";
                        $countResult = mysqli_query($con, $countQuery);
                        $appCount = 0;
                        if ($countResult) {
                            $countRow = mysqli_fetch_assoc($countResult);
                            $appCount = $countRow['apps'];
                        } ?>
                        <div class="col-lg-4 col-md-6 padding-10">
                            <div class="service-item box-shadow" style="padding: 15px;">
                                <div class="service-content" style="padding: 0;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h4 style="font-size: 16px; margin: 0;">
                                            <?php echo $row['job_title']; ?>
                                        </h4>
                                        <span class="status-badge status-active">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </div>
                                    <p style="font-size: 13px; margin: 5px 0;">
                                        <strong>Hires Needed:</strong> <?php echo $row['number_of_positions']; ?>
                                    </p>
                                    <p style="font-size: 12px; margin: 3px 0; color: #666;">
                                        Applications: <?php echo $appCount; ?>
                                    </p>
                                    <div class="dl-btn-group mt-2" style="gap: 5px;">
                                        <a href="hr-page1.php?action=view_job&job_id=<?php echo (int)$row['job_id']; ?>" class="dl-btn"
                                            style="padding: 5px 10px; font-size: 12px;">View</a>
                                        <a href="hr-page1.php?action=edit&job_id=<?php echo (int)$row['job_id']; ?>" class="dl-btn"
                                            style="background:#4caf50;">Edit</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php
// Fetch scheduled interviews for the Schedule tab
$today = date('Y-m-d');
$schedule_query = "SELECT ja.*, jo.job_title, u.full_name, u.email
    FROM job_application ja
    JOIN job_opportunity jo ON ja.job_id = jo.job_id
    JOIN user u ON ja.user_id = u.user_id
    WHERE jo.hr_id = '$hr_id'
    AND ja.status IN ('Interview Scheduled', 'Interview Rescheduled')
    AND ja.interview_date >= '$today'
    ORDER BY ja.interview_date ASC, ja.interview_time ASC";

$schedule_result = mysqli_query($con, $schedule_query);

// Group interviews by date
$grouped_interviews = [];
if ($schedule_result && mysqli_num_rows($schedule_result) > 0) {
    while ($interview = mysqli_fetch_assoc($schedule_result)) {
        $date = $interview['interview_date'];
        if (!isset($grouped_interviews[$date])) {
            $grouped_interviews[$date] = [];
        }
        $grouped_interviews[$date][] = $interview;
    }
}
?>

<!-- Add this PHP code right before the Schedule Tab HTML (in the FETCH LIST DATA section) -->

<?php
// Fetch scheduled interviews for the Schedule tab
$today = date('Y-m-d');
$schedule_query = "SELECT ja.*, jo.job_title, u.full_name, u.email
    FROM job_application ja
    JOIN job_opportunity jo ON ja.job_id = jo.job_id
    JOIN user u ON ja.user_id = u.user_id
    WHERE jo.hr_id = '$hr_id'
    AND ja.status IN ('Interview Scheduled', 'Interview Rescheduled')
    AND ja.interview_date >= '$today'
    ORDER BY ja.interview_date ASC, ja.interview_time ASC";

$schedule_result = mysqli_query($con, $schedule_query);
?>

<!-- Replace your Schedule Tab with this: -->

<!-- Schedule Tab -->
<div id="schedule" class="tab-content">
    <!-- Calendar View -->
    <div class="row mb-30">
        <div class="col-md-12">
            <div class="service-item box-shadow padding-15">
                <h4 class="mb-3">Upcoming Interviews</h4>
                
                <?php if ($schedule_result && mysqli_num_rows($schedule_result) > 0): ?>
                    <div class="row">
                        <?php while ($interview = mysqli_fetch_assoc($schedule_result)): 
                            $date_obj = new DateTime($interview['interview_date']);
                            $date_formatted = $date_obj->format('l, M j, Y');
                            
                            $time_obj = DateTime::createFromFormat('H:i:s', $interview['interview_time']);
                            $time_formatted = $time_obj ? $time_obj->format('g:i A') : $interview['interview_time'];
                        ?>
                            <div class="col-md-6 padding-10">
                                <div class="service-item box-shadow" style="border-left: 4px solid #2196F3; padding: 12px;">
                                    <div class="service-content" style="padding: 0;">
                                        <div class="interview-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div style="flex: 1;">
                                                    <strong style="font-size: 14px; display: block; margin-bottom: 5px;">
                                                        <?php echo htmlspecialchars($interview['full_name']); ?>
                                                    </strong>
                                                    <small style="font-size: 12px; color: #666; display: block;">
                                                        <?php echo htmlspecialchars($interview['job_title']); ?>
                                                    </small>
                                                    <small style="font-size: 12px; color: #2196F3; display: block; margin-top: 5px;">
                                                        <i class="fas fa-calendar"></i> <?php echo $date_formatted; ?>
                                                    </small>
                                                    <small style="font-size: 12px; color: #666; display: block;">
                                                        <i class="fas fa-clock"></i> <?php echo $time_formatted; ?>
                                                    </small>
                                                    <?php if (!empty($interview['interview_location'])): ?>
                                                        <small style="font-size: 11px; color: #999; display: block;">
                                                            <i class="fas fa-map-marker-alt"></i> 
                                                            <?php echo htmlspecialchars($interview['interview_location']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="margin-left: 10px;">
                                                    <a href="hr-page1.php?action=manage&application_id=<?php echo $interview['application_id']; ?>" 
                                                       class="dl-btn" style="padding: 5px 10px; font-size: 11px;">
                                                        Reschedule
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="row">
                        <div class="col-md-12">
                            <div style="padding: 40px; text-align: center; background: #f9f9f9; border-radius: 8px;">
                                <i class="fas fa-calendar-times" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                                <p style="color: #999;">No upcoming interviews scheduled</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</div>
        </div>
    </section>

    <!-- ==========================================
     MODALS SECTION
     ========================================== -->

    <!-- Create Job Modal -->
    <div id="createJobModal" class="land-modal">
        <div class="land-modal-content" style="max-width: 500px;">
            <div class="land-modal-header">
                <h3>Create New Job Opportunity</h3>
                <span class="land-modal-close">&times;</span>
            </div>
            <div class="land-modal-body">
                <form method="POST" action="" id="createJobForm">
                    <input type="hidden" name="create_job" value="1">

                    <div class="form-group">
                        <label>Job Title</label>
                        <input name="job_title" type="text" class="form-control" placeholder="e.g., Senior Land Surveyor" required>
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <select name="department" class="form-control" required>
                            <option value="">Select Department</option>
                            <option value="field">Field Operations</option>
                            <option value="technical">Technical</option>
                            <option value="management">Management</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Number of Hires Needed</label>
                        <input name="number_of_positions" type="number" class="form-control" min="1" value="1" required>
                    </div>

                    <div class="form-group">
                        <label>Job Type</label>
                        <select name="job_type" class="form-control" required>
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Job Description</label>
                        <textarea name="job_description" class="form-control" rows="3" placeholder="Enter job description..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Responsibilities</label>
                        <textarea name="responsibilities" class="form-control" rows="3" placeholder="Enter responsibilities..." required></textarea>
                    </div>

                    <div class="form-group text-right" style="gap: 10px; display: flex; justify-content: flex-end;">
                        <button type="button" class="dl-btn" style="background: #f44336; padding: 8px 15px;" onclick="closeModal('createJobModal')">Cancel</button>
                        <button type="submit" class="dl-btn" style="background: #4caf50; padding: 8px 15px;" id="submitJobBtn">Create Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Job Modal -->
    <?php if ($selectedJob): ?>
        <div id="editJobModal" class="land-modal" style="display:block;">
            <div class="land-modal-content" style="max-width: 500px;">
                <div class="land-modal-header">
                    <h3>Edit Job Opportunity</h3>
                    <a href="hr-page1.php" class="land-modal-close">&times;</a>
                </div>
                <div class="land-modal-body">
                    <form method="POST" action="hr-page1.php">
                        <input type="hidden" name="edit_job" value="1">
                        <input type="hidden" name="job_id" value="<?php echo (int)$selectedJob['job_id']; ?>">

                        <div class="form-group">
                            <label>Job Title</label>
                            <input type="text" class="form-control" name="job_title"
                                value="<?php echo htmlspecialchars($selectedJob['job_title']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Number of Hires Needed</label>
                            <input type="number" class="form-control" name="number_of_positions"
                                value="<?php echo (int)$selectedJob['number_of_positions']; ?>" min="1" required>
                        </div>

                        <div class="form-group">
                            <label>Job Type</label>
                            <select class="form-control" name="job_type">
                                <option value="full-time" <?php if ($selectedJob['job_type'] == 'full-time') echo 'selected'; ?>>Full-time</option>
                                <option value="part-time" <?php if ($selectedJob['job_type'] == 'part-time') echo 'selected'; ?>>Part-time</option>
                                <option value="contract" <?php if ($selectedJob['job_type'] == 'contract') echo 'selected'; ?>>Contract</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                <option value="open" <?php if ($selectedJob['status'] == 'open') echo 'selected'; ?>>Open</option>
                                <option value="closed" <?php if ($selectedJob['status'] == 'closed') echo 'selected'; ?>>Closed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Job Description</label>
                            <textarea class="form-control" name="job_description" rows="3"><?php echo htmlspecialchars($selectedJob['job_description']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Responsibilities</label>
                            <textarea class="form-control" name="responsibilities" rows="3"><?php echo htmlspecialchars($selectedJob['responsibilities']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Requirements</label>
                            <textarea class="form-control" name="requirements" rows="3"><?php echo htmlspecialchars($selectedJob['requirements']); ?></textarea>
                        </div>

                        <div class="form-group text-right" style="gap: 10px; display: flex; justify-content: flex-end;">
                            <a href="hr-page1.php" class="dl-btn" style="background:#666; padding:8px 15px;">Cancel</a>
                            <button type="submit" class="dl-btn" style="background:#4caf50; padding:8px 15px;">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Application Action Modal -->
    <?php if ($selectedApplication): ?>
        <div id="actionModal" class="land-modal" style="display:block;">
            <div class="land-modal-content" style="max-width: 450px;">
                <div class="land-modal-header">
                    <h3>Manage application</h3>
                    <a href="hr-page1.php" class="land-modal-close" aria-label="Close">&times;</a>
                </div>
                <div class="land-modal-body">
                    <div class="application-info mb-3" style="padding: 10px; background: #f9f9f9; border-radius: 5px;">
                        <strong><?php echo htmlspecialchars($selectedApplication['applicant_name']); ?></strong><br>
                        <small>Position: <?php echo htmlspecialchars($selectedApplication['job_title']); ?></small><br>
                        <small>Status: <?php echo htmlspecialchars($selectedApplication['status']); ?></small><br>
                        <small>Applied on: <?php echo htmlspecialchars($selectedApplication['application_date']); ?></small>
                    </div>

                    <form method="POST" action="hr-page1.php">
                        <input type="hidden" name="perform_action" value="1">
                        <input type="hidden" name="application_id" value="<?php echo (int)$selectedApplication['application_id']; ?>">

                        <div class="form-group">
                            <label>Action</label>
                            <select class="form-control" name="action" id="actionSelect" required
                                style="height: 40px; padding: 8px 12px; line-height: normal; font-size: 14px;"
                                onchange="toggleScheduleFields()">
                                <option value="">Select action</option>
                                <option value="schedule">Schedule interview</option>
                                <option value="hire">Hire candidate</option>
                                <option value="reject">Reject application</option>
                                <option value="reschedule">Reschedule interview</option>
                            </select>
                        </div>

                        <!-- Schedule Fields (Hidden by default) -->
                        <div id="scheduleFields" style="display: none;">
                            <div class="form-group">
                                <label>Interview Date</label>
                                <input type="date" class="form-control" name="interview_time" id="interviewTimeSelect"
                                style="height: 40px; padding: 8px 12px; line-height: normal; font-size: 14px;">
                                <option value="">Select time</option>
                                <option value="09:00:00">9:00 AM</option>
                                <option value="09:30:00">9:30 AM</option>
                                <option value="10:00:00">10:00 AM</option>
                                <option value="10:30:00">10:30 AM</option>
                                <option value="11:00:00">11:00 AM</option>
                                <option value="11:30:00">11:30 AM</option>
                                <option value="12:00:00">12:00 PM</option>
                                <option value="12:30:00">12:30 PM</option>
                                <option value="13:00:00">1:00 PM</option>
                                <option value="13:30:00">1:30 PM</option>
                                <option value="14:00:00">2:00 PM</option>
                                <option value="14:30:00">2:30 PM</option>
                                <option value="15:00:00">3:00 PM</option>
                                <option value="15:30:00">3:30 PM</option>
                                <option value="16:00:00">4:00 PM</option>
                                <option value="16:30:00">4:30 PM</option>
                                <option value="17:00:00">5:00 PM</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Location / Meeting Link</label>
                            <input type="text" class="form-control" name="interview_location"
                                placeholder="e.g., Office Room 5 or Zoom link"
                                style="height: 40px; padding: 8px 12px; line-height: normal; font-size: 14px;">
                        </div>
                    </div>
                    <div class="form-group text-right" style="gap: 10px; display: flex; justify-content: flex-end;">
                        <a href="hr-page1.php" class="dl-btn" style="background: #f44336; padding: 8px 15px;">Cancel</a>
                        <button type="submit" class="dl-btn" style="background: #4caf50; padding: 8px 15px;">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- View Application Modal -->
<?php if (isset($viewApplication)): ?>
    <div id="viewApplicationModal" class="land-modal" style="display:block;">
        <div class="land-modal-content" style="max-width: 600px;">
            <div class="land-modal-header">
                <h3>Application Details</h3>
                <a href="hr-page1.php" class="land-modal-close">&times;</a>
            </div>
            <div class="land-modal-body">
                <div class="application-info mb-3" style="padding: 10px; background: #f9f9f9; border-radius: 5px;">
                    <strong><?php echo htmlspecialchars($viewApplication['applicant_name']); ?></strong><br>
                    <small>Applied for: <?php echo htmlspecialchars($viewApplication['job_title']); ?></small><br>
                    <small>Status: <?php echo htmlspecialchars($viewApplication['status']); ?></small><br>
                    <small>Date: <?php echo htmlspecialchars($viewApplication['application_date']); ?></small><br>
                    <small>email: <?php echo htmlspecialchars($viewApplication['applicant_email']); ?></small>
                </div>

                <div class="form-group">
                    <label>Resume</label><br>
                    <?php if (!empty($viewApplication['cv_file_path'])): ?>
                        <a href="<?php echo 'uploads/resumes/' . basename($viewApplication['cv_file_path']); ?>"
                            class="dl-btn" style="background:#4caf50; padding:8px 15px;" download>
                            Download Resume
                        </a>
                    <?php else: ?>
                        <p>No resume uploaded.</p>
                    <?php endif; ?>
                </div>

                <div class="form-group text-right">
                    <a href="hr-page1.php" class="dl-btn" style="background:#666; padding:8px 15px;">Close</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- View Job Modal -->
<?php if ($viewJob): ?>
    <div id="viewJobModal" class="land-modal" style="display:block;">
        <div class="land-modal-content" style="max-width: 600px;">
            <div class="land-modal-header">
                <h3>Job Opportunity Details</h3>
                <a href="hr-page1.php" class="land-modal-close">&times;</a>
            </div>
            <div class="land-modal-body">
                <div class="job-info mb-3" style="padding: 10px; background: #f9f9f9; border-radius: 5px;">
                    <strong><?php echo htmlspecialchars($viewJob['job_title']); ?></strong><br>
                    <small>Type: <?php echo htmlspecialchars($viewJob['job_type']); ?></small><br>
                    <small>Status: <?php echo htmlspecialchars($viewJob['status']); ?></small><br>
                    <small>Positions: <?php echo (int)$viewJob['number_of_positions']; ?></small>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <p style="white-space: pre-line;"><?php echo htmlspecialchars($viewJob['job_description']); ?></p>
                </div>

                <div class="form-group">
                    <label>Responsibilities</label>
                    <p style="white-space: pre-line;"><?php echo htmlspecialchars($viewJob['responsibilities']); ?></p>
                </div>

                <div class="form-group">
                    <label>Requirements</label>
                    <p style="white-space: pre-line;"><?php echo htmlspecialchars($viewJob['requirements']); ?></p>
                </div>

                <div class="form-group text-right">
                    <a href="hr-page1.php" class="dl-btn" style="background:#666; padding:8px 15px;">Close</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Success Message Modal -->
<div id="successModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 350px;">
        <div class="land-modal-body text-center">
            <i class="fas fa-check-circle" style="font-size: 48px; color: #4caf50; margin-bottom: 15px;"></i>
            <h4 id="successMessage">Success!</h4>
            <p style="font-size: 14px;" id="successDetails">Action completed successfully</p>
            <button type="button" class="dl-btn" style="background: #4caf50; padding: 8px 20px; margin-top: 15px;" onclick="closeModal('successModal')">OK</button>
        </div>
    </div>
</div>

<script>
    // Update available times when date is selected
    function updateAvailableTimes() {
        const dateInput = document.getElementById('interviewDate');
        const timeSelect = document.getElementById('interviewTimeSelect');

        if (!dateInput.value) {
            return;
        }

        // Fetch booked times for the selected date
        fetch('get_booked_times.php?date=' + dateInput.value)
            .then(response => response.json())
            .then(data => {
                if (data.bookedTimes) {
                    const bookedTimes = data.bookedTimes;

                    // Reset all options to enabled first
                    Array.from(timeSelect.options).forEach(option => {
                        if (option.value !== '') {
                            option.disabled = false;
                            option.style.color = '';
                            option.textContent = option.textContent.replace(' (Booked)', '');
                        }
                    });

                    // Disable booked times
                    Array.from(timeSelect.options).forEach(option => {
                        if (bookedTimes.includes(option.value)) {
                            option.disabled = true;
                            option.style.color = '#999';
                            option.textContent += ' (Booked)';
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching booked times:', error);
            });
    }

    // Show/hide schedule fields
    function toggleScheduleFields() {
        const actionSelect = document.getElementById('actionSelect');
        const scheduleFields = document.getElementById('scheduleFields');

        if (actionSelect && scheduleFields) {
            if (actionSelect.value === 'schedule' || actionSelect.value === 'reschedule') {
                scheduleFields.style.display = 'block';
                document.querySelector('input[name="interview_date"]').required = true;
                document.querySelector('select[name="interview_time"]').required = true;
            } else {
                scheduleFields.style.display = 'none';
                document.querySelector('input[name="interview_date"]').required = false;
                document.querySelector('select[name="interview_time"]').required = false;
            }
        }
    }

    // Show success/error messages after page load
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($showSuccess): ?>
            setTimeout(function() {
                const successMsg = document.getElementById('successMessage');
                const successDetails = document.getElementById('successDetails');
                if (successMsg) successMsg.textContent = 'Job Created Successfully!';
                if (successDetails) successDetails.textContent = '<?php echo addslashes($successMsg); ?>';
                openModal('successModal');
            }, 300);
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            alert('<?php echo addslashes($errorMsg); ?>');
        <?php endif; ?>
    });
</script>

<?php include 'includes/footer.html'; ?>