<?php
session_start();
include 'includes/connect.php';

// Check if user is logged in and is a Lead Engineer
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "LeadEngineer") {
    exit("Unauthorized");
}

// Get lead_engineer ID
$user_id = $_SESSION['user_id'];
$q = mysqli_query($con, "SELECT lead_engineer_id FROM lead_engineer WHERE user_id='$user_id'");
$lead_engineer = mysqli_fetch_assoc($q);
$lead_engineer_id = $lead_engineer['lead_engineer_id'];

// Get status from POST request
$status = isset($_POST['status']) ? trim($_POST['status']) : 'assigned';

if ($status === 'assigned') {
    // 🔹 Load ASSIGNED equipment list (for reporting issues)
    $query = "SELECT equipment.equipment_id, equipment.equipment_name, equipment.status, 
               equipment.date, project.project_name
              FROM equipment
              JOIN uses_project_equipment ON uses_project_equipment.equipment_id = equipment.equipment_id
              JOIN project ON project.project_id = uses_project_equipment.project_id
              JOIN lead_engineer ON lead_engineer.lead_engineer_id = project.lead_engineer_id
              WHERE lead_engineer.lead_engineer_id = '$lead_engineer_id' AND equipment.status = 'assigned'";

    $equipmentResult = mysqli_query($con, $query);

    if ($equipmentResult && mysqli_num_rows($equipmentResult) > 0) {
        echo '<div class="row">';
        while ($equipment = mysqli_fetch_assoc($equipmentResult)) {
            $equipment_id = $equipment['equipment_id'];
            $equipment_name = htmlspecialchars($equipment['equipment_name']);
            $project_name = htmlspecialchars($equipment['project_name']);
?>
<div class="col-lg-6 mb-3">
    <div class="card p-3 request-card">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h5><?php echo $equipment_name; ?></h5>
            <span class="status-badge status-approved">Assigned</span>
        </div>
        <p><strong>Project:</strong> <?php echo $project_name; ?></p>
        <button class="btn btn-danger btn-sm report-issue-btn" 
                type="button"
                data-id="<?php echo (int)$equipment_id; ?>"
                data-name="<?php echo htmlspecialchars($equipment_name, ENT_QUOTES); ?>">
            <i class="fas fa-exclamation-triangle"></i> Report Issue
        </button>
    </div>
</div>
<?php 
        }
        echo '</div>';
    } else {
?>
<div class="col-12">
    <div class="text-center p-5" style="color: #999;">
        <i class="fas fa-tools" style="font-size: 48px; margin-bottom: 15px;"></i>
        <p>No equipment currently assigned</p>
    </div>
</div>
<?php
    }
} elseif ($status === 'requested') {
    // ðŸ"¹ Load REQUESTED equipment
    $query = "SELECT DISTINCT 
               equipment.equipment_id, 
               equipment.equipment_name, 
               project.project_id, 
               project.project_name
              FROM equipment
              JOIN uses_project_equipment ON uses_project_equipment.equipment_id = equipment.equipment_id
              JOIN project ON project.project_id = uses_project_equipment.project_id
              WHERE project.lead_engineer_id = '$lead_engineer_id' 
              AND equipment.status='requested'
              ORDER BY project.project_name, equipment.equipment_name";

    $requestResult = mysqli_query($con, $query);

    if ($requestResult && mysqli_num_rows($requestResult) > 0) {
        echo '<div class="row">';
        while ($request = mysqli_fetch_assoc($requestResult)) {
            $equipment_id = $request['equipment_id'];
            $equipment_name = htmlspecialchars($request['equipment_name']);
            $project_id = $request['project_id'];
            $project_name = htmlspecialchars($request['project_name']);
            
            // Get land info for this project (optional)
            $landQuery = "SELECT land.land_number, land.land_address
                         FROM service_request
                         JOIN land ON land.land_id = service_request.land_id
                         WHERE service_request.project_id = '$project_id'
                         LIMIT 1";
            $landResult = mysqli_query($con, $landQuery);
            $land = mysqli_fetch_assoc($landResult);
            $land_number = htmlspecialchars($land['land_number'] ?? 'N/A');
            $land_address = htmlspecialchars($land['land_address'] ?? 'N/A');
?>
<div class="col-lg-6 mb-3">
    <div class="card p-3 request-card">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h5><?php echo $equipment_name; ?></h5>
            <button class="btn btn-danger btn-sm removeEquipmentBtn" 
                    type="button"
                    data-equipment-id="<?php echo (int)$equipment_id; ?>"
                    data-project-id="<?php echo (int)$project_id; ?>"
                    title="Remove Equipment Request">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p><strong>Project:</strong> <?php echo $project_name; ?></p>
        <p><strong>Land Number:</strong> <?php echo $land_number; ?></p>
        <p><strong>Land Address:</strong> <?php echo $land_address; ?></p>
    </div>
</div>
<?php 
        }
        echo '</div>';
    } else {
?>
<div class="col-12">
    <div class="text-center p-5" style="color: #999;">
        <i class="fas fa-box" style="font-size: 48px; margin-bottom: 15px;"></i>
        <p>No equipment requests found</p>
    </div>
</div>
<?php
    }
}
// Add CSS styling
?>
<style>
.request-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s ease;
}

.request-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-approved {
    background: #d4edda;
    color: #155724;
}

.removeEquipmentBtn {
    padding: 5px 10px;
    border-radius: 4px;
}

.removeEquipmentBtn:hover {
    background: #c82333;
    transform: scale(1.1);
}
</style>