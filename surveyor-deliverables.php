<?php
include 'includes/header.php'; 
include 'includes/connect.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Surveyor") {
    exit();
}

$user_id = $_SESSION['user_id'];

// Get surveyor info (name, project_id)
$get_surveyor = "SELECT s.surveyor_id, u.full_name, s.project_id, s.status
                 FROM surveyor s 
                 JOIN user u ON s.user_id = u.user_id 
                 WHERE s.user_id = '$user_id'";
$surveyor_result = mysqli_query($con, $get_surveyor);
$surveyor_row = mysqli_fetch_assoc($surveyor_result);

$surveyor_id = $surveyor_row['surveyor_id'];
$surveyor_name = $surveyor_row['full_name'];
$project_id  = $surveyor_row['project_id'];
$surveyor_status = $surveyor_row['status'];

// Initialize variables
$project = null;
$engineer_name = 'N/A';
$client_name = 'N/A';
$services_result = null;
$lands_result = null;
$files_result = null;
$deliverables_result = null;
$has_active_project = false;

// Check if surveyor has a project assigned
if (!empty($project_id)) {
    // Get project details first to check status
    $get_project = "SELECT * FROM project WHERE project_id = '$project_id' LIMIT 1";
    $project_result = mysqli_query($con, $get_project);
    $project = mysqli_fetch_assoc($project_result);
    
    // Check if project status is NOT 'active'
    if ($project && strtolower($project['status']) !== 'active') {
        // Project is completed or inactive - update surveyor status to 'not assigned'
        $update_surveyor = "UPDATE surveyor 
                           SET status = 'available', 
                               project_id = NULL 
                           WHERE surveyor_id = '$surveyor_id'";
        mysqli_query($con, $update_surveyor);
        
        // Reset variables
        $project_id = null;
        $surveyor_status = 'available';
        $project = null;
        $has_active_project = false;
        
        // Refresh surveyor data
        $_SESSION['project_completed_notice'] = "Your previous project has been completed. You are now available for new assignments.";
    } else if ($project && strtolower($project['status']) === 'active') {
        // Project is active - surveyor has an active project
        $has_active_project = true;
    }
}

if ($has_active_project) {
    // Handle land info update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_land_info'])) {
        $land_id = mysqli_real_escape_string($con, $_POST['land_id']);
        $land_address = mysqli_real_escape_string($con, $_POST['land_address']);
        $land_area = mysqli_real_escape_string($con, $_POST['land_area']);
        $land_type = mysqli_real_escape_string($con, $_POST['land_type']);
        $coordinates_latitude = mysqli_real_escape_string($con, $_POST['coordinates_latitude']);
        $coordinates_longitude = mysqli_real_escape_string($con, $_POST['coordinates_longitude']);
        $specific_location_notes = mysqli_real_escape_string($con, $_POST['specific_location_notes']);
        $land_number = mysqli_real_escape_string($con, $_POST['land_number']);
        $elevation_avg = mysqli_real_escape_string($con, $_POST['elevation_avg']);
        $slope = mysqli_real_escape_string($con, $_POST['slope']);
        $distance_from_office = mysqli_real_escape_string($con, $_POST['distance_from_office']);
        $elevation_min = mysqli_real_escape_string($con, $_POST['elevation_min']);
        $elevation_max = mysqli_real_escape_string($con, $_POST['elevation_max']);
        $terrain_factor = mysqli_real_escape_string($con, $_POST['terrain_factor']);

        // Get current land data to check what changed
        $check_land = "SELECT * FROM land WHERE land_id = '$land_id'";
        $check_result = mysqli_query($con, $check_land);
        $current_land = mysqli_fetch_assoc($check_result);

        // Determine approval flags
        $geometry_approved = 0;
        $terrain_approved = 0;

        // Helper function to compare values (handles null, empty string, and numeric comparisons)
        function valuesChanged($new_val, $old_val) {
            // Normalize empty values to null for comparison
            $new_normalized = ($new_val === '' || $new_val === null) ? null : $new_val;
            $old_normalized = ($old_val === '' || $old_val === null) ? null : $old_val;
            
            // If both are null, they haven't changed
            if ($new_normalized === null && $old_normalized === null) {
                return false;
            }
            
            // If one is null and the other isn't, they changed
            if (($new_normalized === null) !== ($old_normalized === null)) {
                return true;
            }
            
            // For numeric comparisons, convert to float and compare
            if (is_numeric($new_normalized) && is_numeric($old_normalized)) {
                return (float)$new_normalized !== (float)$old_normalized;
            }
            
            // For string comparisons, use strict comparison
            return (string)$new_normalized !== (string)$old_normalized;
        }

        // Check if geometry data changed (area or coordinates)
        if (valuesChanged($land_area, $current_land['land_area']) || 
            valuesChanged($coordinates_latitude, $current_land['coordinates_latitude']) || 
            valuesChanged($coordinates_longitude, $current_land['coordinates_longitude'])) {
            $geometry_approved = 1;
        }

        // Check if terrain data changed (compare with existing values)
        if (valuesChanged($land_address, $current_land['land_address']) || 
            valuesChanged($land_type, $current_land['land_type']) || 
            valuesChanged($specific_location_notes, $current_land['specific_location_notes']) || 
            valuesChanged($land_number, $current_land['land_number']) || 
            valuesChanged($elevation_avg, $current_land['elevation_avg']) || 
            valuesChanged($slope, $current_land['slope']) || 
            valuesChanged($distance_from_office, $current_land['distance_from_office']) || 
            valuesChanged($elevation_min, $current_land['elevation_min']) || 
            valuesChanged($elevation_max, $current_land['elevation_max']) || 
            valuesChanged($terrain_factor, $current_land['terrain_factor'])) {
            $terrain_approved = 1;
        }

        // Update land info
        $update_land = "UPDATE land SET 
                        land_address = '$land_address',
                        land_area = '$land_area',
                        land_type = '$land_type',
                        coordinates_latitude = '$coordinates_latitude',
                        coordinates_longitude = '$coordinates_longitude',
                        specific_location_notes = '$specific_location_notes',
                        land_number = '$land_number',
                        elevation_avg = '$elevation_avg',
                        slope = '$slope',
                        distance_from_office = '$distance_from_office',
                        elevation_min = '$elevation_min',
                        elevation_max = '$elevation_max',
                        terrain_factor = '$terrain_factor'";

        // Add approval flags if they were set
        if ($geometry_approved == 1) {
            $update_land .= ", geometry_approved = 1";
        }
        if ($terrain_approved == 1) {
            $update_land .= ", terrain_approved = 1";
        }

        $update_land .= " WHERE land_id = '$land_id'";

        if (mysqli_query($con, $update_land)) {
            $_SESSION['update_success'] = "Land information updated successfully!";
            if ($geometry_approved == 1) {
                $_SESSION['update_success'] .= " (Geometry Approved)";
            }
            if ($terrain_approved == 1) {
                $_SESSION['update_success'] .= " (Terrain Approved)";
            }
        } else {
            $_SESSION['update_error'] = "Error updating land information.";
        }

        header("Location: surveyor-deliverables.php");
        exit();
    }

    // Get lead engineer name
    $get_engineer = "SELECT u.full_name 
                     FROM lead_engineer le 
                     JOIN user u ON le.user_id = u.user_id 
                     WHERE le.lead_engineer_id = '{$project['lead_engineer_id']}'";
    $engineer_result = mysqli_query($con, $get_engineer);
    $engineer_row = mysqli_fetch_assoc($engineer_result);
    $engineer_name = $engineer_row['full_name'] ?? 'N/A';

    // Get client name
    $get_client = "SELECT u.full_name 
                   FROM includes_project_land pl 
                   JOIN client c ON pl.client_id = c.client_id 
                   JOIN user u ON c.user_id = u.user_id 
                   WHERE pl.project_id = '$project_id' 
                   LIMIT 1";
    $client_result = mysqli_query($con, $get_client);
    $client_row = mysqli_fetch_assoc($client_result);
    $client_name = $client_row['full_name'] ?? 'N/A';

    // Get services for this project
    $get_services = "SELECT DISTINCT s.service_name 
                     FROM service_request sr 
                     JOIN service s ON sr.service_id = s.service_id 
                     WHERE sr.project_id = '$project_id'";
    $services_result = mysqli_query($con, $get_services);

    // Get lands assigned via includes_project_land
    $get_lands = "SELECT l.* 
                  FROM includes_project_land pl
                  JOIN land l ON pl.land_id = l.land_id
                  WHERE pl.project_id = '$project_id'";
    $lands_result = mysqli_query($con, $get_lands);

    // Handle land file upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_landfile'])) {
        $land_id = mysqli_real_escape_string($con, $_POST['land_id']);
        $file_type = mysqli_real_escape_string($con, $_POST['file_type']);
        $file_description = mysqli_real_escape_string($con, $_POST['file_description']);
        $additional_notes = mysqli_real_escape_string($con, $_POST['additional_notes']);

        // File upload handling
        if (!empty($_FILES['land_file']['name'])) {
            $file_name = basename($_FILES['land_file']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = array('pdf', 'dwg', 'dxf', 'shp', 'csv', 'jpg', 'jpeg', 'png');
            
            if (in_array($file_ext, $allowed)) {
                $upload_dir = "uploads/land_files/";
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_file_name = time() . "_" . uniqid() . "." . $file_ext;
                $file_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($_FILES['land_file']['tmp_name'], $file_path)) {
                    $upload_date = date('Y-m-d');
                    
                    // Insert into submitted_document
                    $insert_doc = "INSERT INTO submitted_document (upload_date, file_name, file_path, document_type_id) 
                                   VALUES ('$upload_date', '$file_description', '$file_path', 1)";
                    mysqli_query($con, $insert_doc);
                    $document_id = mysqli_insert_id($con);
                    
                    // Link to service_request for this land
                    $get_request = "SELECT request_id FROM service_request 
                                   WHERE project_id = '$project_id' AND land_id = '$land_id' 
                                   LIMIT 1";
                    $req_result = mysqli_query($con, $get_request);
                    
                    if (mysqli_num_rows($req_result) > 0) {
                        $req_row = mysqli_fetch_assoc($req_result);
                        $request_id = $req_row['request_id'];
                        
                        $insert_link = "INSERT INTO has_servicerequest_submitteddocument 
                                       (service_request_id, submitted_document_id) 
                                       VALUES ('$request_id', '$document_id')";
                        mysqli_query($con, $insert_link);
                    }
                    
                    $_SESSION['upload_success'] = "Land file uploaded successfully!";
                    header("Location: surveyor-deliverables.php");
                    exit();
                }
            }
        }
    }

    // Handle deliverable upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_deliverable'])) {
        $deliverable_type = mysqli_real_escape_string($con, $_POST['deliverable_type']);
        $deliverable_title = mysqli_real_escape_string($con, $_POST['deliverable_title']);
        $description = mysqli_real_escape_string($con, $_POST['description']);
        $version_notes = mysqli_real_escape_string($con, $_POST['version_notes']);
        $related_land = !empty($_POST['related_land']) ? mysqli_real_escape_string($con, $_POST['related_land']) : null;

        // File upload handling
        if (!empty($_FILES['deliverable_file']['name'])) {
            $file_name = basename($_FILES['deliverable_file']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = array('pdf', 'dwg', 'dxf', 'doc', 'docx');
            
            if (in_array($file_ext, $allowed)) {
                $upload_dir = "deliverables/";
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $new_file_name = time() . "_" . uniqid() . "." . $file_ext;
                $file_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($_FILES['deliverable_file']['tmp_name'], $file_path)) {
                    $upload_date = date('Y-m-d');
                    
                    // Insert into deliverable table
                    $insert_deliverable = "INSERT INTO deliverable 
                                          (deliverable_name, deliverable_type, description, submission_date, file_path, project_id) 
                                          VALUES ('$deliverable_title', '$deliverable_type', '$description', '$upload_date', '$file_path', '$project_id')";
                    mysqli_query($con, $insert_deliverable);
                    
                    $_SESSION['upload_success'] = "Deliverable uploaded successfully!";
                    header("Location: surveyor-deliverables.php");
                    exit();
                }
            }
        }
    }

    // Get uploaded land files for this project
    $get_files = "
        SELECT sd.document_id, sd.upload_date, sd.file_name, sd.file_path,
               l.land_number, l.land_address, l.land_area, l.land_id
        FROM submitted_document sd
        JOIN has_servicerequest_submitteddocument hs 
            ON sd.document_id = hs.submitted_document_id
        JOIN service_request sr 
            ON hs.service_request_id = sr.request_id
        JOIN land l 
            ON sr.land_id = l.land_id
        WHERE sr.project_id = '$project_id'
        ORDER BY sd.upload_date DESC
    ";
    $files_result = mysqli_query($con, $get_files);

    // Fetch existing deliverables for this project
    $get_deliverables = "
        SELECT d.*
        FROM deliverable d
        WHERE d.project_id = '$project_id'
        ORDER BY d.submission_date DESC
    ";
    $deliverables_result = mysqli_query($con, $get_deliverables);

    // Calculate status class
    $status_class = 'status-planning';
    $status_text = $project['status'];
    switch(strtolower($project['status'])) {
        case 'planning':
            $status_class = 'status-planning';
            break;
        case 'active':
            $status_class = 'status-in-progress';
            $status_text = 'Active - ' . $project['progress'] . '%';
            break;
        case 'on hold':
            $status_class = 'status-on-hold';
            break;
        case 'completed':
            $status_class = 'status-completed';
            break;
    }
}

// Prepare lands data with services for JavaScript
$lands_data = array();
if ($has_active_project && $lands_result) {
    mysqli_data_seek($lands_result, 0);
    while($land = mysqli_fetch_assoc($lands_result)) {
        // Get service requests for this land in this project
        $get_land_services = "SELECT s.service_name 
                              FROM service_request sr
                              JOIN service s ON sr.service_id = s.service_id
                              WHERE sr.project_id = '$project_id' 
                              AND sr.land_id = '{$land['land_id']}'";
        $land_services_result = mysqli_query($con, $get_land_services);
        
        $services = array();
        while($service_row = mysqli_fetch_assoc($land_services_result)) {
            $services[] = $service_row['service_name'];
        }
        
        $land['services'] = $services;
        $lands_data[] = $land;
    }
}
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                <h1>Surveyor Dashboard</h1>
                <p>View assigned projects, upload land files, and manage deliverables</p>
            </div>
            <div class="col-lg-4 text-right">
                <div class="engineer-info">
                    <strong>Surveyor: <?= htmlspecialchars($surveyor_name); ?></strong><br>
                    <small>Status: <?= htmlspecialchars(ucfirst($surveyor_status)); ?></small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Project Sections -->
<section class="padding">
    <div class="container">
        <?php if (isset($_SESSION['project_completed_notice'])) { ?>
            <!-- Project Completed Notice -->
            <div class="alert alert-info" style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <i class="fas fa-info-circle"></i> <?= htmlspecialchars($_SESSION['project_completed_notice']); ?>
            </div>
            <?php unset($_SESSION['project_completed_notice']); ?>
        <?php } ?>

        <?php if (!$has_active_project) { ?>
            <!-- No Project Assigned Message -->
            <div class="no-project-message">
                <div class="service-item box-shadow text-center" style="padding: 60px 20px; background: #f8f9fa;">
                    <i class="fas fa-clipboard-list" style="font-size: 64px; color: #6c757d; margin-bottom: 20px;"></i>
                    <h3 style="color: #263a4f; margin-bottom: 15px;">No Active Project</h3>
                    <p style="font-size: 16px; color: #666; margin-bottom: 20px;">
                        You are not assigned to any project right now.
                    </p>
                    <p style="font-size: 14px; color: #999;">
                        Your status is currently set to <strong><?= htmlspecialchars(ucfirst($surveyor_status)); ?></strong>. 
                        You will be notified when a new project is assigned to you by the Lead Engineer.
                    </p>
                </div>
            </div>
        <?php } else { ?>
            <!-- Project Details Section -->
            <div class="project-section mb-40">
                <div class="service-item box-shadow" style="padding: 20px;">
                    <!-- Project Header -->
                    <div class="project-header mb-4">
                        <div class="row d-flex align-items-center">
                            <div class="col-md-8">
                                <h3 style="margin: 0; color: #263a4f;">
                                    <?= htmlspecialchars($project['project_name']); ?>
                                </h3>
                                <p style="margin: 5px 0; color: #666;">
                                    Client: <?= htmlspecialchars($client_name); ?> | 
                                    Timeline: <?= date('M d, Y', strtotime($project['start_date'])); ?> - 
                                    <?= date('M d, Y', strtotime($project['end_date'])); ?>
                                </p>
                                <p style="margin: 5px 0; color: #666;">
                                    <strong>Lead Engineer:</strong> <?= htmlspecialchars($engineer_name); ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <span class="status-badge <?= $status_class; ?>">
                                    <?= $status_text; ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Services Section -->
                    <?php if ($services_result && mysqli_num_rows($services_result) > 0) { ?>
                    <div class="services-section mb-4">
                        <h5>All Services in Project</h5>
                        <div class="services-tags">
                            <?php 
                            mysqli_data_seek($services_result, 0);
                            while($service = mysqli_fetch_assoc($services_result)) { 
                            ?>
                            <span class="service-tag"><?= htmlspecialchars($service['service_name']); ?></span>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>

                    <!-- Land Information Section -->
                    <?php 
                    if ($lands_result) {
                        mysqli_data_seek($lands_result, 0);
                        if (mysqli_num_rows($lands_result) > 0) { 
                    ?>
                    <div class="land-info-section mb-4">
                        <h5>Assigned Lands</h5>
                        <div class="row">
                            <?php 
                            $land_index = 0;
                            while($land = mysqli_fetch_assoc($lands_result)) { 
                                $geometry_approved = isset($land['geometry_approved']) ? $land['geometry_approved'] : 0;
                                $terrain_approved = isset($land['terrain_approved']) ? $land['terrain_approved'] : 0;
                                
                                // Get services for this land
                                $get_land_services = "SELECT s.service_name 
                                                      FROM service_request sr
                                                      JOIN service s ON sr.service_id = s.service_id
                                                      WHERE sr.project_id = '$project_id' 
                                                      AND sr.land_id = '{$land['land_id']}'";
                                $land_services_result = mysqli_query($con, $get_land_services);
                            ?>
                                <div class="col-md-6">
                                    <div class="land-info-card">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0">Land #<?= $land['land_id']; ?> - <?= htmlspecialchars($land['land_number'] ?? 'Lot ' . $land['land_id']); ?></h6>
                                            <div class="approval-badges">
                                                <span class="approval-badge <?= $geometry_approved == 1 ? 'approved' : 'pending'; ?>" title="Geometry Approval">
                                                    <i class="fas fa-map-marker-alt"></i> <?= $geometry_approved == 1 ? 'Geo ✓' : 'Geo ✗'; ?>
                                                </span>
                                                <span class="approval-badge <?= $terrain_approved == 1 ? 'approved' : 'pending'; ?>" title="Terrain Approval">
                                                    <i class="fas fa-mountain"></i> <?= $terrain_approved == 1 ? 'Ter ✓' : 'Ter ✗'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <p class="mb-1"><strong>Address:</strong> <?= htmlspecialchars($land['land_address']); ?></p>
                                        <p class="mb-1"><strong>Area:</strong> <?= number_format($land['land_area'], 1); ?> acres</p>
                                        <p class="mb-1"><strong>Type:</strong> <?= htmlspecialchars($land['land_type']); ?></p>
                                        <p class="mb-2"><strong>Coordinates:</strong> <?= htmlspecialchars($land['coordinates_latitude'] ?? 'N/A'); ?>, <?= htmlspecialchars($land['coordinates_longitude'] ?? 'N/A'); ?></p>
                                        
                                        <!-- Services for this land -->
                                        <?php if (mysqli_num_rows($land_services_result) > 0) { ?>
                                        <div class="land-services mb-2">
                                            <strong style="font-size: 12px;">Requested Services:</strong>
                                            <div class="land-services-tags" style="margin-top: 5px;">
                                                <?php while($land_service = mysqli_fetch_assoc($land_services_result)) { ?>
                                                <span class="land-service-tag"><?= htmlspecialchars($land_service['service_name']); ?></span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <?php } else { ?>
                                        <p class="mb-2" style="font-size: 12px; color: #999;"><em>No services requested for this land</em></p>
                                        <?php } ?>
                                        
                                        <button class="edit-land-btn" onclick="openEditLandModal(<?= $land['land_id']; ?>)">
                                            <i class="fas fa-edit mr-1"></i> Edit Land Info
                                        </button>
                                    </div>
                                </div>
                            <?php 
                                $land_index++;
                            } 
                            ?>
                        </div>
                    </div>
                    <?php 
                        }
                    } 
                    ?>

                    <!-- Action Buttons -->
                    <div class="action-buttons mb-4">
                        <button class="default-btn mr-2" onclick="toggleSection('project-landfiles')">
                            <i class="fas fa-map mr-1"></i> Upload Land Files
                        </button>
                        <button class="default-btn" onclick="toggleSection('project-deliverables')">
                            <i class="fas fa-file-alt mr-1"></i> Upload Deliverables
                        </button>
                    </div>

                    <!-- Upload Land Files (Collapsible) -->
                    <div id="project-landfiles" class="collapsible-section" style="display: none;">
                        <div class="row">
                            <div class="col-12">
                                <div class="service-item box-shadow" style="padding: 15px; background: #f8f9fa;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 style="margin: 0;">Upload Land Files</h5>
                                        <button class="close-btn" onclick="toggleSection('project-landfiles')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Upload New Land File -->
                                    <div class="upload-section mb-4">
                                        <h6>Upload New Land File</h6>
                                        <form class="landfile-form" method="POST" action="" enctype="multipart/form-data">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Select Land</label>
                                                        <select class="form-control" name="land_id" required>
                                                            <option value="">Choose land...</option>
                                                            <?php 
                                                            if ($lands_result) {
                                                                mysqli_data_seek($lands_result, 0);
                                                                while($land = mysqli_fetch_assoc($lands_result)) { 
                                                            ?>
                                                            <option value="<?= $land['land_id']; ?>">
                                                                Land #<?= $land['land_id']; ?> - <?= htmlspecialchars($land['land_number'] ?? 'Lot ' . $land['land_id']); ?>
                                                            </option>
                                                            <?php 
                                                                }
                                                            } 
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>File Type</label>
                                                        <select class="form-control" name="file_type" required>
                                                            <option value="">Select file type...</option>
                                                            <option value="Survey Data">Survey Data</option>
                                                            <option value="Field Notes">Field Notes</option>
                                                            <option value="GPS Data">GPS Data</option>
                                                            <option value="Site Photos">Site Photos</option>
                                                            <option value="Raw Data">Raw Data</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>File Description</label>
                                                <input type="text" class="form-control" name="file_description" placeholder="Brief description of the land file..." required>
                                            </div>

                                            <div class="form-group">
                                                <label>Upload File</label>
                                                <div class="file-upload-area" style="border: 2px dashed #ccc; border-radius: 5px; padding: 20px; text-align: center; background: #fff;">
                                                    <i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: #4caf50; margin-bottom: 10px;"></i>
                                                    <p style="margin: 0; font-size: 14px;">Drop file here or click to browse</p>
                                                    <p style="margin: 5px 0 0 0; font-size: 11px; color: #666;">Supported formats: PDF, DWG, DXF, SHP, CSV, JPG, PNG (Max 50MB)</p>
                                                    <input type="file" name="land_file" class="form-control-file" style="display: none;" accept=".pdf,.dwg,.dxf,.shp,.csv,.jpg,.jpeg,.png" required>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Additional Notes</label>
                                                <textarea class="form-control" name="additional_notes" rows="2" placeholder="Add any additional information..."></textarea>
                                            </div>

                                            <button type="submit" name="upload_landfile" class="default-btn" style="width: 100%;">Upload Land File</button>
                                        </form>
                                    </div>

                                    <!-- Existing Land Files -->
                                    <div class="existing-files">
                                        <h6>Uploaded Land Files</h6>
                                        <?php 
                                        if ($files_result && mysqli_num_rows($files_result) > 0) {
                                            mysqli_data_seek($files_result, 0);
                                            while($file = mysqli_fetch_assoc($files_result)) { 
                                        ?>
                                        <div class="file-item mb-3" style="background: #fff; border-radius: 5px; padding: 12px; border-left: 3px solid #4caf50;">
                                            <div class="row d-flex align-items-center">
                                                <div class="col-md-8">
                                                    <p style="margin: 0; font-weight: 600; font-size: 13px;">
                                                        <?= htmlspecialchars($file['file_name']); ?>
                                                    </p>
                                                    <p style="margin: 3px 0; font-size: 11px; color: #666;">
                                                        <span><strong>Land:</strong> <?= htmlspecialchars($file['land_number'] ?? 'Lot ' . $file['land_id']); ?></span> | 
                                                        <span><strong>Date:</strong> <?= date('M d, Y', strtotime($file['upload_date'])); ?></span>
                                                    </p>
                                                </div>
                                                <div class="col-md-4 text-right">
                                                    <a href="<?= $file['file_path']; ?>" download class="dl-btn" style="padding: 5px 10px; font-size: 11px; background: #2196F3; text-decoration: none; color: #fff;">
                                                        <i class="fas fa-download mr-1"></i> Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php 
                                            }
                                        } else {
                                            echo '<p style="color: #666; font-size: 13px;">No land files uploaded yet.</p>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deliverables Management (Collapsible) -->
                    <div id="project-deliverables" class="collapsible-section" style="display: none;">
                        <div class="row">
                            <div class="col-12">
                                <div class="service-item box-shadow" style="padding: 15px; background: #f8f9fa;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 style="margin: 0;">Upload Deliverables</h5>
                                        <button class="close-btn" onclick="toggleSection('project-deliverables')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Upload New Deliverable -->
                                    <div class="upload-section mb-4">
                                        <h6>Upload New Deliverable</h6>
                                        <form class="deliverable-form" method="POST" action="" enctype="multipart/form-data">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Deliverable Type</label>
                                                        <select class="form-control" name="deliverable_type" required>
                                                            <option value="">Select type...</option>
                                                            <option value="Survey Plan">Survey Plan</option>
                                                            <option value="Topographic Map">Topographic Map</option>
                                                            <option value="Boundary Report">Boundary Report</option>
                                                            <option value="CAD Drawing">CAD Drawing</option>
                                                            <option value="Legal Description">Legal Description</option>
                                                            <option value="Final Report">Final Report</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Related Land (Optional)</label>
                                                        <select class="form-control" name="related_land">
                                                            <option value="">All Lands</option>
                                                            <?php 
                                                            if ($lands_result) {
                                                                mysqli_data_seek($lands_result, 0);
                                                                while($land = mysqli_fetch_assoc($lands_result)) { 
                                                            ?>
                                                            <option value="<?= $land['land_id']; ?>">
                                                                Land #<?= $land['land_id']; ?> - <?= htmlspecialchars($land['land_number'] ?? 'Lot ' . $land['land_id']); ?>
                                                            </option>
                                                            <?php 
                                                                }
                                                            } 
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Deliverable Title</label>
                                                <input type="text" class="form-control" name="deliverable_title" placeholder="Enter deliverable title..." required>
                                            </div>

                                            <div class="form-group">
                                                <label>Description</label>
                                                <textarea class="form-control" name="description" rows="2" placeholder="Brief description of the deliverable..." required></textarea>
                                            </div>

                                            <div class="form-group">
                                                <label>Upload File</label>
                                                <div class="file-upload-area" style="border: 2px dashed #ccc; border-radius: 5px; padding: 20px; text-align: center; background: #fff;">
                                                    <i class="fas fa-cloud-upload-alt" style="font-size: 32px; color: #4caf50; margin-bottom: 10px;"></i>
                                                    <p style="margin: 0; font-size: 14px;">Drop file here or click to browse</p>
                                                    <p style="margin: 5px 0 0 0; font-size: 11px; color: #666;">Supported formats: PDF, DWG, DXF, DOC, DOCX (Max 100MB)</p>
                                                    <input type="file" name="deliverable_file" class="form-control-file" style="display: none;" accept=".pdf,.dwg,.dxf,.doc,.docx" required>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Version Notes</label>
                                                <textarea class="form-control" name="version_notes" rows="2" placeholder="Version or revision notes..."></textarea>
                                            </div>

                                            <button type="submit" name="upload_deliverable" class="default-btn" style="width: 100%;">Upload Deliverable</button>
                                        </form>
                                    </div>

                                    <!-- Existing Deliverables -->
                                    <div class="existing-deliverables">
                                        <h6>My Uploaded Deliverables</h6>
                                        <?php 
                                        if ($deliverables_result && mysqli_num_rows($deliverables_result) > 0) {
                                            mysqli_data_seek($deliverables_result, 0);
                                            while($deliverable = mysqli_fetch_assoc($deliverables_result)) { 
                                        ?>
                                        <div class="file-item mb-3" style="background: #fff; border-radius: 5px; padding: 12px; border-left: 3px solid #2196F3;">
                                            <div class="row d-flex align-items-center">
                                                <div class="col-md-8">
                                                    <p style="margin: 0; font-weight: 600; font-size: 13px;">
                                                        <?= htmlspecialchars($deliverable['deliverable_name']); ?>
                                                    </p>
                                                    <p style="margin: 3px 0; font-size: 11px; color: #666;">
                                                        <span><strong>Type:</strong> <?= htmlspecialchars($deliverable['deliverable_type']); ?></span> | 
                                                        <span><strong>Date:</strong> <?= date('M d, Y', strtotime($deliverable['submission_date'])); ?></span>
                                                    </p>
                                                    <p style="margin: 3px 0 0 0; font-size: 11px; color: #666;">
                                                        <?= htmlspecialchars($deliverable['description']); ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-4 text-right">
                                                    <a href="<?= $deliverable['file_path']; ?>" download class="dl-btn" style="padding: 5px 10px; font-size: 11px; background: #2196F3; text-decoration: none; color: #fff;">
                                                        <i class="fas fa-download mr-1"></i> Download
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php 
                                            }
                                        } else {
                                            echo '<p style="color: #666; font-size: 13px;">No deliverables uploaded yet.</p>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</section>

<!-- Edit Land Info Modal -->
<div id="editLandModal" class="land-modal" style="display: none;">
    <div class="land-modal-content" style="max-width: 800px; margin: 5% auto; background: #fff; padding: 25px; border-radius: 8px; max-height: 85vh; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 id="modalTitle" style="margin: 0; color: #263a4f;">Edit Land Information</h4>
            <button class="close-btn" onclick="closeModal('editLandModal')" style="font-size: 24px;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="editLandForm" method="POST" action="">
            <input type="hidden" name="land_id" id="edit_land_id">
            
            <div class="approval-status-display mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="approval-info-box" style="background: #f0f8ff; padding: 12px; border-radius: 5px; border-left: 3px solid #2196F3;">
                            <i class="fas fa-map-marker-alt"></i> <strong>Geometry Data</strong>
                            <p style="font-size: 12px; margin: 5px 0 0 0; color: #666;">Updating Area or Coordinates will mark this as Geometry Approved</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="approval-info-box" style="background: #f0fff4; padding: 12px; border-radius: 5px; border-left: 3px solid #4caf50;">
                            <i class="fas fa-mountain"></i> <strong>Terrain Data</strong>
                            <p style="font-size: 12px; margin: 5px 0 0 0; color: #666;">Updating other fields will mark this as Terrain Approved</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Land Number</label>
                        <input type="text" class="form-control" name="land_number" id="edit_land_number" placeholder="Enter land number">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-map"></i> Land Type</label>
                        <select class="form-control" name="land_type" id="edit_land_type">
                            <option value="">Select type...</option>
                            <option value="Residential">Residential</option>
                            <option value="Commercial">Commercial</option>
                            <option value="Industrial">Industrial</option>
                            <option value="Agricultural">Agricultural</option>
                            <option value="Mixed Use">Mixed Use</option>
                            <option value="Vacant">Vacant</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Address</label>
                <input type="text" class="form-control" name="land_address" id="edit_land_address" placeholder="Enter land address">
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="geometry-section" style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                        <h6 style="color: #2196F3; margin-bottom: 12px;"><i class="fas fa-ruler-combined"></i> Geometry Data (Will trigger Geometry Approval)</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Area (acres)</label>
                                    <input type="number" step="0.01" class="form-control" name="land_area" id="edit_land_area" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Latitude</label>
                                    <input type="text" class="form-control" name="coordinates_latitude" id="edit_coordinates_latitude" placeholder="e.g., 33.7490">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Longitude</label>
                                    <input type="text" class="form-control" name="coordinates_longitude" id="edit_coordinates_longitude" placeholder="e.g., 35.5628">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="terrain-section" style="background: #f0fff4; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                <h6 style="color: #4caf50; margin-bottom: 12px;"><i class="fas fa-mountain"></i> Terrain Data (Will trigger Terrain Approval)</h6>
                
                <div class="form-group">
                    <label>Specific Location Notes</label>
                    <textarea class="form-control" name="specific_location_notes" id="edit_specific_location_notes" rows="2" placeholder="Add specific location details, landmarks, or access information..."></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Average Elevation (m)</label>
                            <input type="number" step="0.1" class="form-control" name="elevation_avg" id="edit_elevation_avg" placeholder="0.0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Min Elevation (m)</label>
                            <input type="number" step="0.1" class="form-control" name="elevation_min" id="edit_elevation_min" placeholder="0.0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Max Elevation (m)</label>
                            <input type="number" step="0.1" class="form-control" name="elevation_max" id="edit_elevation_max" placeholder="0.0">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Slope (%)</label>
                            <input type="number" step="0.1" class="form-control" name="slope" id="edit_slope" placeholder="0.0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Distance from Office (km)</label>
                            <input type="number" step="0.1" class="form-control" name="distance_from_office" id="edit_distance_from_office" placeholder="0.0">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Terrain Factor</label>
                            <input type="number" step="0.1" class="form-control" name="terrain_factor" id="edit_terrain_factor" placeholder="0.0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions" style="border-top: 1px solid #e0e0e0; padding-top: 15px; margin-top: 15px;">
                <button type="submit" name="update_land_info" class="default-btn" style="width: 100%; padding: 12px;">
                    <i class="fas fa-check mr-1"></i> Approve & Update Land Information
                </button>
                <p style="font-size: 11px; color: #666; margin: 10px 0 0 0; text-align: center;">
                    By clicking this button, you confirm that the land information is accurate and complete
                </p>
            </div>
        </form>
    </div>
</div>

<!-- Success Message Modal -->
<div id="successModal" class="land-modal" style="display: none;">
    <div class="land-modal-content" style="max-width: 350px; margin: 15% auto; background: #fff; padding: 20px; border-radius: 5px;">
        <div class="land-modal-body text-center">
            <i class="fas fa-check-circle" style="font-size: 48px; color: #4caf50; margin-bottom: 15px;"></i>
            <h4 id="successMessage">Success!</h4>
            <p id="successDetails" style="font-size: 14px;"></p>
            <button type="button" class="dl-btn" style="background: #4caf50; padding: 8px 20px; margin-top: 15px; border: none; color: #fff; border-radius: 3px; cursor: pointer;" onclick="closeModal('successModal')">OK</button>
        </div>
    </div>
</div>

<style>
.service-tag {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 15px;
    font-size: 11px;
    margin: 2px;
}
.land-service-tag {
    display: inline-block;
    background: #fff3e0;
    color: #e65100;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 10px;
    margin: 2px;
    font-weight: 600;
}
.services-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 15px;
}
.land-services-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}
.status-badge {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}
.status-planning { background: #fff3cd; color: #856404; }
.status-in-progress { background: #cce7ff; color: #004085; }
.status-on-hold { background: #ffeaa7; color: #856404; }
.status-completed { background: #d4edda; color: #155724; }
.file-upload-area {
    transition: all 0.3s ease;
    cursor: pointer;
}
.file-upload-area:hover {
    border-color: #4caf50;
    background: #f8fff8;
}
.project-section {
    border-radius: 8px;
}
.land-info-card {
    background: #f8f9fa;
    border-radius: 5px;
    padding: 12px;
    margin-bottom: 10px;
    border-left: 3px solid #4caf50;
    position: relative;
}
.land-info-card h6 {
    margin-bottom: 8px;
    color: #263a4f;
    font-size: 14px;
}
.land-info-card p {
    font-size: 12px;
    margin-bottom: 3px;
}
.approval-badges {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}
.approval-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
}
.approval-badge.approved {
    background: #d4edda;
    color: #155724;
}
.approval-badge.pending {
    background: #fff3cd;
    color: #856404;
}
.approval-badge i {
    font-size: 9px;
    margin-right: 2px;
}
.edit-land-btn {
    background: #2196F3;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    margin-top: 8px;
    transition: all 0.3s ease;
}
.edit-land-btn:hover {
    background: #1976D2;
    transform: translateY(-1px);
}
.edit-land-btn i {
    font-size: 11px;
}
.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.collapsible-section {
    margin-top: 15px;
    border-top: 1px solid #e0e0e0;
    padding-top: 15px;
}
.close-btn {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    font-size: 16px;
}
.close-btn:hover {
    color: #333;
}
.services-section h5, .land-info-section h5 {
    color: #263a4f;
    margin-bottom: 10px;
    font-size: 16px;
}
.engineer-info {
    background: #fff;
    padding: 12px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.file-item {
    transition: all 0.3s ease;
}
.file-item:hover {
    transform: translateX(3px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.land-modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}
.no-project-message {
    min-height: 400px;
    display: flex;
    align-items: center;
}
.geometry-section, .terrain-section {
    border: 1px solid rgba(0,0,0,0.1);
}
.approval-info-box {
    height: 100%;
}
.approval-info-box i {
    margin-right: 5px;
}
.form-group label {
    font-weight: 600;
    font-size: 13px;
    color: #263a4f;
    margin-bottom: 5px;
}
.form-group label i {
    margin-right: 5px;
    color: #666;
}
.form-control {
    color: #263a4f !important;
    background-color: #fff !important;
}
.form-control::placeholder {
    color: #999 !important;
}
.form-control:focus {
    border-color: #4caf50;
    box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
}
textarea.form-control {
    color: #263a4f !important;
}
.alert {
    display: flex;
    align-items: center;
}
.alert i {
    margin-right: 10px;
    font-size: 18px;
}
</style>

<script>
// Store lands data in JavaScript
const landsData = <?= json_encode($lands_data); ?>;

// Toggle collapsible sections
function toggleSection(sectionId) {
    var section = document.getElementById(sectionId);
    if (section.style.display === "none") {
        section.style.display = "block";
    } else {
        section.style.display = "none";
    }
}

// Open edit land modal
function openEditLandModal(landId) {
    const land = landsData.find(l => l.land_id == landId);
    if (!land) return;

    // Fill form with current land data
    document.getElementById('edit_land_id').value = land.land_id;
    document.getElementById('edit_land_number').value = land.land_number || '';
    document.getElementById('edit_land_type').value = land.land_type || '';
    document.getElementById('edit_land_address').value = land.land_address || '';
    document.getElementById('edit_land_area').value = land.land_area || '';
    document.getElementById('edit_coordinates_latitude').value = land.coordinates_latitude || '';
    document.getElementById('edit_coordinates_longitude').value = land.coordinates_longitude || '';
    document.getElementById('edit_specific_location_notes').value = land.specific_location_notes || '';
    document.getElementById('edit_elevation_avg').value = land.elevation_avg || '';
    document.getElementById('edit_elevation_min').value = land.elevation_min || '';
    document.getElementById('edit_elevation_max').value = land.elevation_max || '';
    document.getElementById('edit_slope').value = land.slope || '';
    document.getElementById('edit_distance_from_office').value = land.distance_from_office || '';
    document.getElementById('edit_terrain_factor').value = land.terrain_factor || '';

    // Update modal title
    document.getElementById('modalTitle').textContent = 'Edit Land #' + land.land_id + ' - ' + (land.land_number || 'Lot ' + land.land_id);

    openModal('editLandModal');
}

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).style.display = "block";
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modals = document.getElementsByClassName('land-modal');
    for (var i = 0; i < modals.length; i++) {
        if (event.target == modals[i]) {
            modals[i].style.display = "none";
        }
    }
}

// File upload area click handler
document.querySelectorAll('.file-upload-area').forEach(function(area) {
    area.onclick = function() {
        this.querySelector('input[type="file"]').click();
    }
});

// File input change handler
document.querySelectorAll('input[type="file"]').forEach(function(input) {
    input.onchange = function() {
        if (this.files.length > 0) {
            var fileName = this.files[0].name;
            var fileSize = (this.files[0].size / 1024 / 1024).toFixed(2);
            var uploadArea = this.closest('.file-upload-area');
            var paragraphs = uploadArea.querySelectorAll('p');
            if (paragraphs.length > 0) {
                paragraphs[0].textContent = fileName + ' (' + fileSize + ' MB)';
            }
            uploadArea.style.borderColor = '#4caf50';
            uploadArea.style.background = '#f8fff8';
        }
    }
});

// Show success message if exists
<?php if (isset($_SESSION['upload_success'])) { ?>
    document.getElementById('successMessage').textContent = 'Success!';
    document.getElementById('successDetails').textContent = '<?= addslashes($_SESSION['upload_success']); ?>';
    openModal('successModal');
    <?php unset($_SESSION['upload_success']); ?>
<?php } ?>

// Show update success message
<?php if (isset($_SESSION['update_success'])) { ?>
    document.getElementById('successMessage').textContent = 'Land Updated!';
    document.getElementById('successDetails').textContent = '<?= addslashes($_SESSION['update_success']); ?>';
    openModal('successModal');
    <?php unset($_SESSION['update_success']); ?>
<?php } ?>

// Show update error message
<?php if (isset($_SESSION['update_error'])) { ?>
    alert('<?= addslashes($_SESSION['update_error']); ?>');
    <?php unset($_SESSION['update_error']); ?>
<?php } ?>
</script>

<?php include 'includes/footer.html'; ?>
</body>
</html>