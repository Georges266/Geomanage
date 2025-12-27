<?php
session_start();
include 'includes/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "LeadEngineer") {
    echo "<p class='text-center text-danger'>Unauthorized</p>";
    exit();
}

// Get lead engineer ID
$user_id = $_SESSION['user_id'];
$q = mysqli_query($con, "SELECT lead_engineer_id FROM lead_engineer WHERE user_id='$user_id'");
$lead_engineer = mysqli_fetch_assoc($q);
$lead_engineer_id = $lead_engineer['lead_engineer_id'];

// Get filters
$projectName = isset($_POST['projectName']) ? mysqli_real_escape_string($con, $_POST['projectName']) : '';
$clientName = isset($_POST['clientName']) ? mysqli_real_escape_string($con, $_POST['clientName']) : '';
$status = isset($_POST['status']) ? mysqli_real_escape_string($con, $_POST['status']) : 'active';
?>

<!-- Projects List -->
<div class="row">
    <div class="col-12">
        <?php
        // Build query - Join with client to get client name
        $query = "SELECT project.*, client.client_id, user.full_name as client_name
                  FROM project 
                  LEFT JOIN includes_project_land ON project.project_id = includes_project_land.project_id
                  LEFT JOIN client ON includes_project_land.client_id = client.client_id
                  LEFT JOIN user ON client.user_id = user.user_id
                  WHERE project.lead_engineer_id = $lead_engineer_id
                  AND project.status = '$status'";

        // Add project name filter
        if (!empty($projectName)) {
            $query .= " AND project.project_name LIKE '%$projectName%'";
        }

        // Add client name filter
        if (!empty($clientName)) {
            $query .= " AND user.full_name LIKE '%$clientName%'";
        }

        $query .= " GROUP BY project.project_id ORDER BY project.start_date DESC";

        $result = mysqli_query($con, $query);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $project_id = $row['project_id'];
                $project_name = $row['project_name'];
                $client_name = $row['client_name'] ?? 'N/A';
                $start_date = date('M d, Y', strtotime($row['start_date']));
                $end_date = date('M d, Y', strtotime($row['end_date']));
                $project_status = $row['status'];
                $progress = $row['progress'];; // Default progress since column doesn't exist
                $description = $row['description'] ?? '';
                
      
                // Get services for this project
                $service_query = "SELECT service_request.request_id, service.service_name
                                    FROM service_request
                                    JOIN service ON service_request.service_id = service.service_id
                                 WHERE service_request.project_id = $project_id";
                $service_result = mysqli_query($con, $service_query);
                $services = [];
                if ($service_result) {
                    while ($service = mysqli_fetch_assoc($service_result)) {
                        $services[] = $service['service_name'];
                    }
                }
                
                // Get lands for this project
                $land_query = "SELECT land.*
                                FROM land 
                                JOIN includes_project_land ON includes_project_land.land_id = land.land_id
                              WHERE includes_project_land.project_id = $project_id";
                $land_result = mysqli_query($con, $land_query);
                $lands = [];
                if ($land_result) {
                    while ($land = mysqli_fetch_assoc($land_result)) {
                        $lands[] = $land;
                    }
                }
                
                // Status badge class
                $status_class = 'status-' . str_replace(' ', '-', strtolower($project_status));
                ?>
                
                <!-- Project Card -->
                <div class="project-section mb-40">
                    <div class="service-item box-shadow" style="padding: 20px;">
                        <!-- Project Header -->
                        <div class="project-header mb-4">
                            <div class="row d-flex align-items-center">
                                <div class="col-md-8">
                                    <h3 style="margin: 0; color: #263a4f;"><?php echo $project_name; ?></h3>
                                    <p style="margin: 5px 0; color: #666;">
                                        Client: <?php echo $client_name; ?> | Timeline: <?php echo $start_date; ?> - <?php echo $end_date; ?>
                                    </p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst($project_status); ?> - <?php echo $progress; ?>%
                                    </span>
                                    <button class="default-btn ml-2" onclick="openUpdateStatusModal(<?php echo $project_id; ?>, '<?php echo addslashes($project_name); ?>', '<?php echo $project_status; ?>', <?php echo $progress; ?>)">
                                        Update 
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Services Section -->
                        <?php if (!empty($services)) { ?>
                        <div class="services-section mb-4">
                            <h5>Project Services</h5>
                            <div class="services-tags">
                                <?php foreach ($services as $service) { ?>
                                    <span class="service-tag"><?php echo $service; ?></span>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>

                        <!-- Land Information Section -->
                        <?php if (!empty($lands)) { ?>
                        <div class="land-info-section mb-4">
                            <h5>Lands Involved</h5>
                            <div class="row">
                                <?php foreach ($lands as $index => $land) { ?>
                                <div class="col-md-6">
                                    <div class="land-info-card">
                                        <h6>Land #<?php echo ($index + 1); ?> - <?php echo $land['land_number'] ?? 'N/A'; ?></h6>
                                        <p class="mb-1"><strong>Address:</strong> <?php echo $land['land_address'] ?? 'N/A'; ?></p>
                                        <p class="mb-1"><strong>Area:</strong> <?php echo $land['land_area'] ?? 'N/A'; ?> sqm</p>
                                        <p class="mb-1"><strong>Type:</strong> <?php echo $land['land_type'] ?? 'N/A'; ?></p>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>

                        <!-- Action Buttons -->
                        <div class="action-buttons mb-4">
                            <button class="default-btn mr-2" onclick="toggleSection('project<?php echo $project_id; ?>-details')">
                                <i class="fas fa-users mr-1"></i> Project Details & Team Management
                            </button>
                            <button class="default-btn" onclick="toggleSection('project<?php echo $project_id; ?>-deliverables')">
                                <i class="fas fa-file-alt mr-1"></i> Deliverables Management
                            </button>
                            <button type="button" 
                            class="default-btn detailsProjectBtn"
                            style="padding: 5px 10px; font-size: 12px; background: #ff9800; border: none; cursor: pointer;" 
                            data-id="<?php echo $project_id; ?>">
                            Details
                            </button>
                            <?php if (strtolower($row['status']) === 'completed'): ?>
                            <!-- When completed show Done -->
                            <?php
                            // Get client email and other info for this project
                            $client_query = "SELECT user.email as client_email 
                                            FROM client 
                                            JOIN user ON client.user_id = user.user_id
                                            WHERE client.client_id = '{$row['client_id']}'";
                            $client_result = mysqli_query($con, $client_query);
                            $client_data = mysqli_fetch_assoc($client_result);
                            $client_email = $client_data['client_email'] ?? 'N/A';
                            
                            // Get service names as comma-separated string
                            $service_names = !empty($services) ? implode(', ', $services) : 'N/A';
                            
                            // Get land numbers and addresses
                            $land_numbers = [];
                            $land_addresses = [];
                            foreach ($lands as $land) {
                                $land_numbers[] = $land['land_number'] ?? 'N/A';
                                $land_addresses[] = $land['land_address'] ?? 'N/A';
                            }
                            $land_numbers_str = implode(', ', $land_numbers);
                            $land_addresses_str = implode('; ', $land_addresses);
                            ?>

                           

                            <button type="button"
                                class="btn btn-success sendEmailModalBtn"
                                data-project_id="<?php echo $project_id; ?>"
                                data-project="<?php echo htmlspecialchars($project_name); ?>"
                                data-price="<?php echo $row['total_cost']; ?>"
                                data-email="<?php echo htmlspecialchars($client_email); ?>"
                                data-services="<?php echo htmlspecialchars($service_names); ?>"
                                data-land_nb="<?php echo htmlspecialchars($land_numbers_str); ?>"
                                data-land_address="<?php echo htmlspecialchars($land_addresses_str); ?>">
                                Done
                            </button>
                        <?php endif; ?>
                        </div>

                        <!-- Project Details & Team Management (Collapsible) -->
                        <div id="project<?php echo $project_id; ?>-details" class="collapsible-section" style="display: none;">
                            <div class="row">
                                <div class="col-12">
                                    <div class="service-item box-shadow" style="padding: 15px; background: #f8f9fa;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 style="margin: 0;">Project Details & Team Management</h5>
                                            <button class="close-btn" onclick="toggleSection('project<?php echo $project_id; ?>-details')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="project-info mb-4">
                                            <h6>Project Description</h6>
                                            <p style="font-size: 13px;"><?php echo $description; ?></p>
                                        </div>

                                        <div class="project-info mb-4">
                                            <h6>Project Details</h6>
                                            <p style="font-size: 13px; margin: 5px 0;"><strong>Total Cost:</strong> $<?php echo number_format($row['total_cost'], 2); ?></p>
                                            <p style="font-size: 13px; margin: 5px 0;"><strong>Payment Status:</strong> <?php echo ucfirst($row['payment_status']); ?></p>
                                            <p style="font-size: 13px; margin: 5px 0;"><strong>Required Team Size:</strong> <?php echo $row['team_size'] ?? 'N/A'; ?> members</p>
                                        </div>

                                        <button class="default-btn mt-3" style="width: 100%;" onclick="openTeamModal(<?php echo $project_id; ?>)">
                                            Manage Team Members
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deliverables Management (Collapsible) -->
                        <div id="project<?php echo $project_id; ?>-deliverables" class="collapsible-section" style="display: none;">
                            <div class="row">
                                <div class="col-12">
                                    <div class="service-item box-shadow" style="padding: 15px; background: #f8f9fa;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 style="margin: 0;">Deliverables Management</h5>
                                            <button class="close-btn" onclick="toggleSection('project<?php echo $project_id; ?>-deliverables')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        
                                        <?php
                                        // Get deliverables for this project (using correct column names)
                                        $del_query = "SELECT * FROM deliverable WHERE project_id = $project_id ORDER BY submission_date DESC";
                                        $del_result = mysqli_query($con, $del_query);
                                        ?>
                                        
                                        <div class="existing-deliverables">
                                            <h6>Existing Deliverables</h6>
                                            <?php if ($del_result && mysqli_num_rows($del_result) > 0) { ?>
                                                <?php while ($deliverable = mysqli_fetch_assoc($del_result)) { ?>
                                                <div class="deliverable-item p-2 mb-2" style="background: #fff; border-radius: 3px;">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong style="font-size: 13px;"><?php echo $deliverable['deliverable_name']; ?></strong>
                                                            <p style="font-size: 11px; margin: 2px 0; color: #666;">
                                                                Type: <?php echo $deliverable['deliverable_type']; ?> | 
                                                                Submitted: <?php echo date('M d, Y', strtotime($deliverable['submission_date'])); ?>
                                                            </p>
                                                        </div>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button type="button"
                                                                class="btn btn-warning showPDFBtn"
                                                                data-id="<?php echo $project_id; ?>">
                                                                Show
                                                            </button>

                                                            <a href="<?php echo $deliverable['file_path']; ?>"
                                                                class="btn btn-success"
                                                                download>
                                                                Download
                                                            </a>
                                                            
                                                            <button type="button"
                                                                class="btn btn-danger removeDeliverableBtn"
                                                                data-deliverable-id="<?php echo $deliverable['deliverable_id']; ?>"
                                                                data-project-id="<?php echo $project_id; ?>">
                                                                Remove
                                                            </button>
                                                        </div>

                                                    </div>
                                                </div>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <p style="font-size: 12px; color: #666; text-align: center;">No deliverables uploaded yet</p>
                                            <?php } ?>
                                        </div>

                                        <input type="file" id="fileInput_<?php echo $project_id; ?>" style="display: none;">
                                        <button class="default-btn mt-3" style="width: 100%;" onclick="document.getElementById('fileInput_<?php echo $project_id; ?>').click()">
                                            Upload New Deliverable
                                        </button>
                                        <div id="uploadStatus_<?php echo $project_id; ?>" style="margin-top: 10px; font-size: 12px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php
            }
        } else {
            ?>
            <div class="col-12">
                <div class="service-item box-shadow text-center" style="padding: 40px;">
                    <i class="fas fa-folder-open" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <h4 style="color: #666;">No Projects Found</h4>
                    <?php if (!empty($projectName) || !empty($clientName)) { ?>
                        <p style="color: #999;">No <?php echo $status; ?> projects match your search criteria</p>
                        <?php if (!empty($projectName)) { ?>
                            <p style="color: #999; font-size: 14px;">Project Name: "<?php echo htmlspecialchars($projectName); ?>"</p>
                        <?php } ?>
                        <?php if (!empty($clientName)) { ?>
                            <p style="color: #999; font-size: 14px;">Client Name: "<?php echo htmlspecialchars($clientName); ?>"</p>
                        <?php } ?>
                        
                    <?php } else { ?>
                        <p style="color: #999;">You don't have any <?php echo $status; ?> projects assigned yet</p>
                    <?php } ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>