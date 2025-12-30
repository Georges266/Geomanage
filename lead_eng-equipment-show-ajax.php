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

// 🔹 Handle new equipment request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['project_id'], $_POST['equipment_type'], $_POST['from_date'], $_POST['until_date'], $_POST['justification'])) {
    $project_id = intval($_POST['project_id']);
    $equipment_type = mysqli_real_escape_string($con, $_POST['equipment_type']);
    $from_date = mysqli_real_escape_string($con, $_POST['from_date']);
    $until_date = mysqli_real_escape_string($con, $_POST['until_date']);
    $justification = mysqli_real_escape_string($con, $_POST['justification']);

    // Insert request into table (create 'equipment_requests' if needed)
    $insert = mysqli_query($con, "INSERT INTO equipment_requests 
        (project_id, equipment_type, from_date, until_date, justification, lead_engineer_id, request_date)
        VALUES ('$project_id', '$equipment_type', '$from_date', '$until_date', '$justification', '$lead_engineer_id', NOW())");

    if ($insert) {
        echo "Equipment request submitted successfully!";
    } else {
        echo "Error: " . mysqli_error($con);
    }
    exit; // stop further execution
}

// 🔹 Load equipment list
$query = "SELECT equipment.equipment_id, equipment.equipment_name, equipment.status, 
           equipment.date, project.project_name
          FROM equipment
          JOIN uses_project_equipment ON uses_project_equipment.equipment_id = equipment.equipment_id
          JOIN project ON project.project_id = uses_project_equipment.project_id
          JOIN lead_engineer ON lead_engineer.lead_engineer_id = project.lead_engineer_id
          WHERE lead_engineer.lead_engineer_id = '$lead_engineer_id' and equipment.status ='assigned'";

$equipmentResult = mysqli_query($con, $query);

if ($equipmentResult && mysqli_num_rows($equipmentResult) > 0) {
    while ($equipment = mysqli_fetch_assoc($equipmentResult)) {
        $equipment_id = $equipment['equipment_id'];
        $equipment_name = $equipment['equipment_name'];
        $project_name = $equipment['project_name'];
        $equipment_status = $equipment['status'] ?? 'In Use';
        
        // Status color
        $status_color = '#4caf50';
        if (strtolower($equipment_status) == 'maintenance') $status_color = '#ff9800';
        elseif (strtolower($equipment_status) == 'available') $status_color = '#2196f3';
?>
<div class="equipment-item mb-3 p-3" style="border: 1px solid #e0e0e0; border-radius: 5px;">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <strong><?php echo htmlspecialchars($equipment_name); ?></strong>
            <p style="font-size: 12px; margin: 2px 0; color: #666;">
                Assigned to: <?php echo htmlspecialchars($project_name); ?>
            </p>
           
            
        </div>
        <button class="dl-btn report-issue-btn" 
                type="button"
                data-id="<?php echo (int)$equipment_id; ?>"
                data-name="<?php echo htmlspecialchars($equipment_name, ENT_QUOTES); ?>"
                style="padding: 5px 10px; font-size: 12px; background: #f44336;">
            Report Issue
        </button>
    </div>
</div>
<?php 
    }
} else {
?>
<div class="text-center p-4" style="color: #999;">
    <i class="fas fa-tools" style="font-size: 36px; margin-bottom: 10px;"></i>
    <p>No equipment currently assigned</p>
</div>
<?php
}
?>