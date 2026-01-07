<?php
session_start();
include 'includes/connect.php';

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    echo "Unauthorized access";
    exit();
}

// Validate equipment ID
if (!isset($_POST['equipment_id'])) {
    echo '<p class="text-danger">Invalid equipment ID.</p>';
    exit();
}

$equipment_id = (int)$_POST['equipment_id'];

// ============================================
// FETCH EQUIPMENT BASIC INFO WITH LATEST MAINTENANCE
// ============================================
$equipmentQuery = "SELECT 
    e.equipment_id,
    e.equipment_name,
    e.status AS equipment_status,
    m.maintenance_date,
    m.maintenance_type,
    m.description,
    m.total_cost,
    m.bill_file_path
FROM equipment e
LEFT JOIN maintenance m ON e.equipment_id = m.equipment_id
WHERE e.equipment_id = $equipment_id
ORDER BY m.maintenance_date DESC
LIMIT 1";

$equipmentResult = mysqli_query($con, $equipmentQuery);

if (!$equipmentResult || mysqli_num_rows($equipmentResult) === 0) {
    echo '<p class="text-danger">Equipment not found.</p>';
    exit();
}

$equipment = mysqli_fetch_assoc($equipmentResult);

// Extract equipment data
$id               = (int)$equipment['equipment_id'];
$name             = htmlspecialchars($equipment['equipment_name']);
$status           = htmlspecialchars($equipment['equipment_status']);
$maintenance_date = $equipment['maintenance_date'] ?? null;
$maintenance_type = htmlspecialchars($equipment['maintenance_type'] ?? '');
$maintenance_description = htmlspecialchars($equipment['description'] ?? '');
$total_cost = $equipment['total_cost'] ?? null;
$bill_file_path = $equipment['bill_file_path'] ?? null;

// Check if last maintenance is completed
$isCompleted = !empty($maintenance_date);

// ============================================
// FETCH PROJECT AND LAND ASSIGNMENT
// ============================================
$assignmentQuery = "
    SELECT 
                e.equipment_name,
                e.equipment_type,
                e.model,
                e.serial_number,
                p.project_name,
                p.project_id,
                land.land_id,
                land.land_address,
                land.land_number,
                u.email AS lead_email,
                u.full_name AS lead_name
            FROM equipment e
            JOIN uses_project_equipment upe ON e.equipment_id = upe.equipment_id
            JOIN project p ON upe.project_id = p.project_id
            JOIN includes_project_land on includes_project_land.project_id=p.project_id
            JOIN land on land.land_id=includes_project_land.land_id
            JOIN lead_engineer le ON p.lead_engineer_id = le.lead_engineer_id
            JOIN user u ON le.user_id = u.user_id
    WHERE upe.equipment_id = $equipment_id
";

$assignmentResult = mysqli_query($con, $assignmentQuery);
$hasAssignments = ($assignmentResult && mysqli_num_rows($assignmentResult) > 0);
?>

<!-- EQUIPMENT DETAILS DISPLAY -->
<div style="font-size: 14px; color: #333;">
    
    <!-- Equipment Assignment Information -->
    <div class="row mb-4">
        <div class="col-md-12 mb-3">
            <h5 style="margin-bottom: 15px; border-bottom: 2px solid #ff7607; padding-bottom: 8px; color: #263a4f;">
                <i class="fas fa-clipboard-list"></i> Equipment Assignment Details
            </h5>
            
            <!-- Equipment Basic Info -->
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <p style="margin-bottom: 8px;">
                    <strong style="color: #263a4f;">Equipment Name:</strong> 
                    <span style="color: #555;"><?php echo $name; ?></span>
                </p>
                <p style="margin-bottom: 0;">
                    <strong style="color: #263a4f;">Current Status:</strong> 
                    <span class="status-badge status-<?php echo strtolower($status); ?>"><?php echo $status; ?></span>
                </p>
            </div>

            <!-- Assignment Display -->
            <?php if ($hasAssignments): ?>
                <?php 
                // Group lands by project
                $projects = [];
                while($row = mysqli_fetch_assoc($assignmentResult)) {
                    $projectId = $row['project_id'];
                    
                    // Initialize project if not exists
                    if (!isset($projects[$projectId])) {
                        $projects[$projectId] = [
                            'name' => $row['project_name'],
                            'lands' => []
                        ];
                    }
                    
                    // Add land if exists
                    if ($row['land_id']) {
                        $projects[$projectId]['lands'][] = [
                            'id' => $row['land_id'],
                            'address' => $row['land_address'],
                            'number' => $row['land_number']
                        ];
                    }
                }
                ?>

                <div style="background: #fff; padding: 15px; border: 1px solid #e0e0e0; border-radius: 8px;">
                    <strong style="color: #263a4f; display: block; margin-bottom: 15px;">
                        <i class="fas fa-project-diagram"></i> Assigned to:
                    </strong>

                    <?php foreach($projects as $projectId => $projectData): ?>
                        <!-- Project Info -->
                        <div style="margin-bottom: 15px; padding: 15px; background: #f0f8ff; border-left: 4px solid #ff7607; border-radius: 4px;">
                            <div style="margin-bottom: 10px;">
                                <strong style="color: #ff7607; font-size: 15px;">
                                    <i class="fas fa-folder"></i> <?php echo htmlspecialchars($projectData['name']); ?>
                                </strong>
                                <p style="margin: 3px 0 0 0; font-size: 12px; color: #666;">
                                    Project ID: <?php echo $projectId; ?>
                                </p>
                            </div>

                            <!-- Lands in this project -->
                            <?php if (!empty($projectData['lands'])): ?>
                                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                                    <strong style="color: #263a4f; font-size: 13px; display: block; margin-bottom: 8px;">
                                        <i class="fas fa-map-marker-alt"></i> Associated Lands:
                                    </strong>
                                    <?php foreach($projectData['lands'] as $land): ?>
                                        <div style="padding: 8px; background: white; border-radius: 4px; margin-bottom: 5px; font-size: 12px; border: 1px solid #e0e0e0;">
                                            <strong style="color: #555;">
                                                <?php echo $land['number'] ? 'Land #' . htmlspecialchars($land['number']) : 'Land ID: ' . $land['id']; ?>
                                            </strong>
                                            <p style="margin: 2px 0 0 0; color: #666;">
                                                <i class="fas fa-location-arrow" style="color: #ff7607; margin-right: 5px;"></i>
                                                <?php echo htmlspecialchars($land['address']); ?>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 4px;">
                                    <p style="margin: 0; font-size: 12px; color: #856404;">
                                        <i class="fas fa-info-circle"></i> No lands found for this project
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px; border: 2px dashed #ddd;">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <p style="color: #999; font-style: italic; margin: 0;">This equipment is not currently assigned to any project.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Maintenance Information -->
        <div class="col-md-12 mb-3" style="margin-top: 20px;">
            <h5 style="margin-bottom: 15px; border-bottom: 2px solid #ff7607; padding-bottom: 8px; color: #263a4f;">
                <i class="fas fa-wrench"></i> Maintenance Information
            </h5>
            
            <?php if ($isCompleted): ?>
                <!-- Show completed maintenance details -->
                <div style="background: #d4edda; padding: 15px; border-left: 3px solid #28a745; border-radius: 8px; margin-bottom: 15px;">
                    
                    <p style="margin-bottom: 0;">
                        <strong style="color: #155724;">Last Maintenance Date:</strong>
                        <span style="color: #155724;"><?php echo date("M d, Y", strtotime($maintenance_date)); ?></span>
                    </p>
                </div>

                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <p style="margin-bottom: 8px;">
                        <strong style="color: #263a4f;">Maintenance Type:</strong><br>
                        <span style="font-style: italic; color: #666; display: block; margin-top: 5px; padding: 10px; background: white; border-left: 3px solid #ff7607; border-radius: 4px;">
                            <?php echo $maintenance_type ? $maintenance_type : 'No type specified'; ?>
                        </span>
                    </p>
                    
                    <p style="margin-bottom: 8px;">
                        <strong style="color: #263a4f;">Description:</strong><br>
                        <span style="font-style: italic; color: #666; display: block; margin-top: 5px; padding: 10px; background: white; border-left: 3px solid #ff7607; border-radius: 4px;">
                            <?php echo $maintenance_description ? $maintenance_description : 'No description provided'; ?>
                        </span>
                    </p>

                    <?php if ($total_cost): ?>
                    <p style="margin-bottom: 8px;">
                        <strong style="color: #263a4f;"><i class="fas fa-dollar-sign"></i> Total Cost:</strong>
                        <span style="font-size: 16px; color: #28a745; font-weight: 600;">
                            $<?php echo number_format($total_cost, 2); ?>
                        </span>
                    </p>
                    <?php endif; ?>

                    <?php if ($bill_file_path): ?>
                    <p style="margin-bottom: 0;">
                        <strong style="color: #263a4f;"><i class="fas fa-file-invoice"></i> Bill/Receipt:</strong><br>
                        <a href="uploads/bills/<?php echo htmlspecialchars($bill_file_path); ?>" 
                           target="_blank" 
                           class="btn btn-sm btn-info"
                           style="margin-top: 10px; display: inline-block;">
                            <i class="fas fa-download"></i> View Bill
                        </a>
                        <?php else: ?>
                            <span style="color: #999; font-style: italic; display: inline-block; margin-top: 10px;">
                                No bill available
                            </span>
                        <?php endif; ?>
                    </p>
                    
                </div>
            <?php else: ?>
                <!-- Show message when no maintenance records exist -->
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <p style="margin-bottom: 8px;">
                        <strong style="color: #263a4f;">Last Maintenance Date:</strong> 
                        <span style="color: #555;">N/A</span>
                    </p>
                    <p style="margin-bottom: 0;">
                        <strong style="color: #263a4f;">Last Maintenance:</strong><br>
                        <span style="font-style: italic; color: #666; display: block; margin-top: 5px; padding: 10px; background: white; border-left: 3px solid #ff7607; border-radius: 4px;">
                            No maintenance records available
                        </span>
                    </p>
                </div>
            <?php endif; ?>

            <?php 
            $statusLower = strtolower(trim($status));
            if ($statusLower === 'available' || $statusLower === 'assigned'): 
            ?>
            <!-- Form to schedule new maintenance -->
            <div style="margin-top: 20px; padding: 20px; background: #fff3cd; border-radius: 8px; border: 1px solid #ffc107;">
                <h6 style="margin-bottom: 15px; color: #856404;">
                    <i class="fas fa-tools"></i> Schedule New Maintenance
                </h6>
                
                
                
                <div class="form-group">
                    <label for="maintenanceDescription" style="font-weight: 600; display: block; margin-bottom: 8px; color: #263a4f;">
                        <i class="fas fa-comment"></i> Description:
                    </label>
                    <textarea id="maintenanceDescription" class="form-control" rows="4" 
                              placeholder="Describe the issue or maintenance needed..." 
                              style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px;"></textarea>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
        <button type="button" class="btn btn-secondary" 
                style="padding: 10px 20px; border-radius: 5px;" 
                onclick="closeModal('equipmentDetailsModal')">
            <i class="fas fa-times"></i> Close
        </button>

        <?php 
        $statusLower = strtolower(trim($status));
        if ($statusLower === 'available' || $statusLower === 'assigned'): 
        ?>
        <button type="button" class="btn btn-warning" 
                style="background: #ff7607; padding: 10px 20px; border-radius: 5px; color: #fff; border: none;" 
                onclick="scheduleMaintenanceCurrent(<?php echo $id; ?>)">
            <i class="fas fa-wrench"></i> Schedule Maintenance
        </button>
        <?php endif; ?>
    </div>
</div>

<style>
.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}
.status-available { background: #d4edda; color: #155724; }
.status-assigned { background: #cce7ff; color: #004085; }
.status-maintenance { background: #fff3cd; color: #856404; }
.status-requested { background: #e1bee7; color: #4a148c; }
</style>