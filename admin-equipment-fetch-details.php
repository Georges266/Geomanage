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
// FETCH EQUIPMENT BASIC INFO
// ============================================
$equipmentQuery = "SELECT * FROM equipment WHERE equipment_id = $equipment_id";
$equipmentResult = mysqli_query($con, $equipmentQuery);

if (!$equipmentResult || mysqli_num_rows($equipmentResult) === 0) {
    echo '<p class="text-danger">Equipment not found.</p>';
    exit();
}

$equipment = mysqli_fetch_assoc($equipmentResult);

// Extract equipment data
$id               = (int)$equipment['equipment_id'];
$name             = htmlspecialchars($equipment['equipment_name']);
$status           = htmlspecialchars($equipment['status']);
$maintenance_date = $equipment['maintenance_date'];
$maintenance_note = htmlspecialchars($equipment['maintenance_note'] ?? '');

// ============================================
// FETCH PROJECT AND LAND ASSIGNMENT
// ============================================
$assignmentQuery = "
    SELECT 
        project.project_id,
        project.project_name,
        land.land_id,
        land.land_address,
        land.land_number
    FROM uses_project_equipment
    JOIN project ON project.project_id = uses_project_equipment.project_id
    LEFT JOIN includes_project_land ON includes_project_land.project_id = project.project_id
    LEFT JOIN land ON land.land_id = includes_project_land.land_id
    WHERE uses_project_equipment.equipment_id = $equipment_id
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
                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                        <?php echo $status; ?>
                    </span>
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
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                <p style="margin-bottom: 8px;">
                    <strong style="color: #263a4f;">Last Maintenance Date:</strong> 
                    <span style="color: #555;">
                        <?php echo $maintenance_date ? date("M d, Y", strtotime($maintenance_date)) : 'N/A'; ?>
                    </span>
                </p>
                <p style="margin-bottom: 0;">
                    <strong style="color: #263a4f;">Last Maintenance Note:</strong><br>
                    <span style="font-style: italic; color: #666; display: block; margin-top: 5px; padding: 10px; background: white; border-left: 3px solid #ff7607; border-radius: 4px;">
                        <?php echo $maintenance_note ? $maintenance_note : 'No notes available'; ?>
                    </span>
                </p>
            </div>

            <?php if (in_array(strtolower($status), ['available', 'assigned'])): ?>
            <div class="form-group">
                <label for="maintenanceNote" style="font-weight: 600; display: block; margin-bottom: 8px; color: #263a4f;">
                    <i class="fas fa-edit"></i> New Maintenance Notes:
                </label>
                <textarea id="maintenanceNote" class="form-control" rows="4" 
                          placeholder="Add maintenance notes..." 
                          style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px;"></textarea>
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

        <?php if (in_array(strtolower($status), ['available', 'assigned'])): ?>
        <button type="button" class="btn btn-warning" 
                style="background: #ff7607; padding: 10px 20px; border-radius: 5px; color: #fff;" 
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
</style>