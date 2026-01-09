<?php
include 'includes/header.php'; 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Surveyor") {
    exit();
}
?>
<?php
include 'includes/connect.php';
?>

<?php
// GET surveyor ID
$user_id = $_SESSION['user_id'];
$q = mysqli_query($con, "SELECT surveyor_id FROM surveyor WHERE user_id='$user_id'");
$surveyor = mysqli_fetch_assoc($q);
$surveyor_id = $surveyor['surveyor_id'];

// Get surveyor info
$query = "SELECT surveyor.surveyor_id, user.full_name
FROM surveyor
JOIN user ON surveyor.user_id = user.user_id
WHERE surveyor.user_id = $user_id"; 
$result = mysqli_query($con, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['surveyor_id'];
    $name = $row['full_name'];
}

// Handle maintenance request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_maintenance'])) {
    $equipment_id = intval($_POST['equipment_id']);
    $maintenance_description = mysqli_real_escape_string($con, $_POST['maintenance_description']);
    $maintenance_date = date('Y-m-d H:i:s');

    // First, check if equipment exists
    $checkQuery = "SELECT * FROM equipment WHERE equipment_id = $equipment_id";
    $checkResult = mysqli_query($con, $checkQuery);

    if (!$checkResult || mysqli_num_rows($checkResult) == 0) {
        $_SESSION['error_message'] = "Error: Equipment ID $equipment_id not found in database";
    } else {
        // Start transaction for data integrity
        mysqli_begin_transaction($con);
        
        try {
            // Insert into maintenance table
            $insertMaintenance = "INSERT INTO maintenance (request_date, description, equipment_id, requested_by) 
                                  VALUES ('$maintenance_date', '$maintenance_description', $equipment_id, $surveyor_id)";
            
            if (!mysqli_query($con, $insertMaintenance)) {
                throw new Exception("Failed to insert maintenance record: " . mysqli_error($con));
            }
            
            // Delete from uses_project_equipment table to unassign equipment from project
            $deleteAssignment = "DELETE FROM uses_project_equipment WHERE equipment_id = $equipment_id";
            
            if (!mysqli_query($con, $deleteAssignment)) {
                throw new Exception("Failed to remove equipment assignment: " . mysqli_error($con));
            }
            
            // Update equipment to maintenance status with maintenance note and request date
            $sql = "UPDATE equipment
                    SET maintenance_note = '$maintenance_description',
                        date = '$maintenance_date',
                        status = 'maintenance'
                    WHERE equipment_id = $equipment_id";

            if (!mysqli_query($con, $sql)) {
                throw new Exception("Failed to update equipment: " . mysqli_error($con));
            }
            
            $affected = mysqli_affected_rows($con);
            
            if ($affected > 0) {
                // Commit transaction
                mysqli_commit($con);
                $_SESSION['success_message'] = "Maintenance request submitted successfully!";
            } else {
                throw new Exception("Warning: Equipment found but not updated. It may already be in maintenance status.");
            }
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($con);
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
    }
    
    header("Location: surveyor-equipment.php");
    exit();
}

// Get assigned equipment for this surveyor
$query = "SELECT equipment.equipment_id, equipment.equipment_name, equipment.status, 
           equipment.date, project.project_name
          FROM equipment
          JOIN uses_project_equipment ON uses_project_equipment.equipment_id = equipment.equipment_id
          JOIN project ON project.project_id = uses_project_equipment.project_id
          JOIN surveyor ON surveyor.project_id = project.project_id
          WHERE surveyor.surveyor_id = '$surveyor_id' AND equipment.status = 'assigned'";

$equipmentResult = mysqli_query($con, $query);
?>
 
<div class="site-preloader-wrap">
    <div class="spinner"></div>
</div>

<!-- Page Header -->
<section class="page-header padding bg-grey">
    <div class="container">
        <div class="row d-flex align-items-center">
            <div class="col-lg-8">
                <h1>Equipment Management</h1>
                <p>Report issues and manage assigned equipment</p>
            </div>
            <div class="col-lg-4 text-right">
                <div class="engineer-info">
                    <strong>Surveyor: <?php echo $name ?></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="padding">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h4 class="mb-4"><i class="fas fa-tools"></i> Assigned Equipment</h4>
            </div>
        </div>

        <div class="row">
            <?php 
            if ($equipmentResult && mysqli_num_rows($equipmentResult) > 0) {
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
                            data-name="<?php echo htmlspecialchars($equipment_name, ENT_QUOTES); ?>"
                            onclick="openReportModal(<?php echo (int)$equipment_id; ?>, '<?php echo htmlspecialchars($equipment_name, ENT_QUOTES); ?>')">
                        <i class="fas fa-exclamation-triangle"></i> Report Issue
                    </button>
                </div>
            </div>
            <?php 
                }
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
            ?>
        </div>
    </div>
</section>

<!-- Maintenance Request Modal -->
<div id="maintenanceRequestModal" class="land-modal">
    <div class="land-modal-content">
        <div class="land-modal-header">
            <h3>Report Equipment Issue</h3>
            <span class="land-modal-close" onclick="closeModal('maintenanceRequestModal')">&times;</span>
        </div>
        <div class="land-modal-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Equipment</label>
                    <input type="text" class="form-control" id="maintenanceEquipment" readonly>
                    <input type="hidden" name="equipment_id" id="maintenanceEquipmentId">
                </div>

                <div class="form-group">
                    <label for="maintenanceDescription" style="font-weight: 600; display: block; margin-bottom: 8px; color: #263a4f;">
                        <i class="fas fa-edit"></i> Description:
                    </label>
                    <textarea id="maintenanceDescription" name="maintenance_description" class="form-control" rows="4" 
                              placeholder="Describe the equipment issue..." 
                              style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px;" required></textarea>
                </div>
  
                <div class="form-group text-right" style="gap:10px; display:flex; justify-content:flex-end;">
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                        <button type="button" class="btn btn-secondary" 
                                style="padding: 10px 20px; border-radius: 5px;" 
                                onclick="closeModal('maintenanceRequestModal')">
                            <i class="fas fa-times"></i> Close
                        </button>

                        <button type="submit" name="schedule_maintenance" class="btn btn-warning" 
                                style="background: #ff7607; padding: 10px 20px; border-radius: 5px; color: #fff; border: none;">
                            <i class="fas fa-wrench"></i> Schedule Maintenance
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Message Modal -->
<div id="successModal" class="land-modal" style="<?php echo isset($_SESSION['success_message']) ? 'display: block;' : 'display: none;'; ?>">
    <div class="land-modal-content" style="max-width: 350px;">
        <div class="land-modal-body text-center">
            <i class="fas fa-check-circle" style="font-size: 48px; color: #4caf50; margin-bottom: 15px;"></i>
            <h4>Success!</h4>
            <p id="successDetails" style="font-size: 14px;">
                <?php 
                if (isset($_SESSION['success_message'])) {
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                }
                ?>
            </p>
            <button type="button" class="dl-btn" style="background: #4caf50; padding: 8px 20px; margin-top: 15px;" onclick="closeModal('successModal')">OK</button>
        </div>
    </div>
</div>

<!-- Error Message Modal -->
<?php if (isset($_SESSION['error_message'])) { ?>
<div id="errorModal" class="land-modal" style="display: block;">
    <div class="land-modal-content" style="max-width: 350px;">
        <div class="land-modal-body text-center">
            <i class="fas fa-times-circle" style="font-size: 48px; color: #f44336; margin-bottom: 15px;"></i>
            <h4>Error!</h4>
            <p style="font-size: 14px;">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </p>
            <button type="button" class="dl-btn" style="background: #f44336; padding: 8px 20px; margin-top: 15px;" onclick="closeModal('errorModal')">OK</button>
        </div>
    </div>
</div>
<?php } ?>

<script>
// Open report modal
function openReportModal(equipmentId, equipmentName) {
    document.getElementById('maintenanceEquipment').value = equipmentName;
    document.getElementById('maintenanceEquipmentId').value = equipmentId;
    openModal('maintenanceRequestModal');
}

// MODAL FUNCTIONS
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('land-modal')) {
        closeModal(event.target.id);
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.land-modal');
        modals.forEach(modal => {
            if (modal.style.display === 'block') {
                closeModal(modal.id);
            }
        });
    }
});
</script>

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

.request-card h5 {
    color: #263a4f;
    margin: 0 0 10px 0;
}

.request-card p {
    color: #666;
    margin-bottom: 15px;
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

.btn {
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-danger {
    background: #f44336;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
}

.btn-danger:hover {
    background: #d32f2f;
    transform: translateY(-1px);
}

.btn-sm {
    font-size: 13px;
}

.land-modal {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    overflow-y: auto;
}

.land-modal-content {
    background: #fff;
    margin: 50px auto;
    border-radius: 8px;
    padding: 20px;
    max-width: 600px;
    width: 90%;
    position: relative;
}

.land-modal-header {
    padding-bottom: 15px;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}

.land-modal-header h3 {
    margin: 0;
    color: #263a4f;
}

.land-modal-close {
    cursor: pointer;
    font-size: 24px;
    position: absolute;
    top: 15px;
    right: 20px;
    color: #666;
}

.land-modal-close:hover {
    color: #000;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #263a4f;
}

.form-control {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s, box-shadow 0.3s;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #ff7607;
    box-shadow: 0 0 0 3px rgba(255, 118, 7, 0.1);
}

.btn-secondary {
    background: #6c757d;
    color: white;
    border: none;
    cursor: pointer;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-warning {
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-warning:hover {
    background: #e66806 !important;
    transform: translateY(-1px);
}

.dl-btn {
    background: #263a4f;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.dl-btn:hover {
    background: #1a2837;
}

.engineer-info {
    background: #fff;
    padding: 12px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>

<?php include 'includes/footer.html'; ?>