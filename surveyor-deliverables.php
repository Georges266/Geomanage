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

// Check if surveyor has an active project assigned
$has_active_project = !empty($project_id);

// Initialize variables
$project = null;
$engineer_name = 'N/A';
$client_name = 'N/A';
$services_result = null;
$lands_result = null;
$files_result = null;
$deliverables_result = null;

if ($has_active_project) {
    // Get project details
    $get_project = "SELECT * FROM project WHERE project_id = '$project_id' LIMIT 1";
    $project_result = mysqli_query($con, $get_project);
    $project = mysqli_fetch_assoc($project_result);

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
                    header("Location: surveyor_dashboard.php");
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
                $upload_dir = "uploads/deliverables/";
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
                    header("Location: surveyor_dashboard.php");
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
        case 'in progress':
        case 'active':
            $status_class = 'status-in-progress';
            $status_text = 'In Progress - ' . $project['progress'] . '%';
            break;
        case 'on hold':
            $status_class = 'status-on-hold';
            break;
        case 'completed':
            $status_class = 'status-completed';
            break;
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
                        <h5>Assigned Services</h5>
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
                            <?php while($land = mysqli_fetch_assoc($lands_result)) { ?>
                                <div class="col-md-6">
                                    <div class="land-info-card">
                                        <h6>Land #<?= $land['land_id']; ?> - <?= htmlspecialchars($land['land_number'] ?? 'Lot ' . $land['land_id']); ?></h6>
                                        <p class="mb-1"><strong>Address:</strong> <?= htmlspecialchars($land['land_address']); ?></p>
                                        <p class="mb-1"><strong>Area:</strong> <?= number_format($land['land_area'], 1); ?> acres</p>
                                        <p class="mb-0"><strong>Type:</strong> <?= htmlspecialchars($land['land_type']); ?></p>
                                    </div>
                                </div>
                            <?php } ?>
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
.services-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 15px;
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
</style>

<script>
// Toggle collapsible sections
function toggleSection(sectionId) {
    var section = document.getElementById(sectionId);
    if (section.style.display === "none") {
        section.style.display = "block";
    } else {
        section.style.display = "none";
    }
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
</script>

<?php include 'includes/footer.html'; ?>
</body>
</html>