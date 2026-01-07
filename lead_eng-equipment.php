<?php
include 'includes/header.php'; 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "LeadEngineer") {
    exit();
}
?>
<?php
include 'includes/connect.php';
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php
// GET lead_engineer ID
$user_id = $_SESSION['user_id'];
$q = mysqli_query($con, "SELECT lead_engineer_id FROM lead_engineer WHERE user_id='$user_id'");
$lead_engineer = mysqli_fetch_assoc($q);
$lead_engineer_id = $lead_engineer['lead_engineer_id'];

// Get engineer info
$query = "SELECT lead_engineer.lead_engineer_id, user.full_name
FROM lead_engineer
JOIN user ON lead_engineer.user_id = user.user_id
WHERE lead_engineer.user_id = $user_id"; 
$result = mysqli_query($con, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['lead_engineer_id'];
    $name = $row['full_name'];
}
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
                <p>Report issues and manage equipment requests</p>
            </div>
            <div class="col-lg-4 text-right">
                <div class="engineer-info">
                    <strong>Lead Engineer: <?php echo $name ?></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="padding">
    <div class="container">
        <!-- Tab Navigation -->
        <div class="row">
            <div class="col-12">
                <div class="tab-navigation mb-40">
                    <button class="tab-btn active" data-status="assigned">
                        <i class="fas fa-tools"></i> Assigned Equipment
                    </button>
                    <button class="tab-btn" data-status="requested">
                        <i class="fas fa-box"></i> Requested Equipment
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div id="equipmentTable"></div>
            </div>
        </div>
    </div>
</section>

<script>
// 🔹 Load equipment via AJAX
function loadEquipment(status = 'assigned') {
    $.ajax({
        url: 'lead_eng-equipment-show-ajax.php',
        type: 'POST',
        data: { 
            status: status,
            lead_engineer_id: '<?php echo $lead_engineer_id; ?>'
        },
        success: function(response) {
            $('#equipmentTable').html(response);
            console.log("Equipment loaded successfully");
        },
        error: function(xhr, status, error) {
            console.error('Error loading equipment:', error);
            $('#equipmentTable').html(
                '<div class="col-12"><p class="text-center text-danger p-3">Error loading equipment.</p></div>'
            );
        }
    });
}

// 🔹 Tab Navigation
$(document).on('click', '.tab-btn', function() {
    $('.tab-btn').removeClass('active');
    $(this).addClass('active');
    const status = $(this).data('status');
    loadEquipment(status);
});

// 🔹 Request maintenance modal
$(document).on('click', '.report-issue-btn', function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    
    console.log("Clicked equipment ID:", id, "Name:", name);

    $('#maintenanceEquipment').val(name);
    $('#maintenanceEquipmentId').val(id);
    openModal('maintenanceRequestModal');
});

// 🔹 Remove equipment request
$(document).on('click', '.removeEquipmentBtn', function() {
    var equipmentId = $(this).data('equipment-id');
    var projectId = $(this).data('project-id');
    
    if (!confirm('Are you sure you want to remove this equipment request?')) {
        return;
    }
    
    $.ajax({
        url: 'admin-project-remove-equipment.php',
        type: 'POST',
        data: {
            equipment_id: equipmentId,
            project_id: projectId
        },
        success: function(response) {
            alert(response);
            loadEquipment('requested'); // Reload the requested tab
        },
        error: function(xhr, status, error) {
            alert('Error removing equipment: ' + error);
        }
    });
});

// SCHEDULE MAINTENANCE
function scheduleMaintenanceCurrent() {
    const equipmentId = $('#maintenanceEquipmentId').val();
   
    const maintenanceDescription = $('#maintenanceDescription').val().trim();

    console.log('Equipment ID:', equipmentId);

    if (!equipmentId) {
        alert('Equipment ID is missing. Please try again.');
        return;
    }


    if (!maintenanceDescription) {
        alert('Please enter the maintenance description.');
        return;
    }

    if (!confirm('Schedule maintenance for this equipment?')) {
        return;
    }

    $.ajax({
        url: 'admin-equipment-schedule-maintenance.php',
        type: 'POST',
        data: {
            equipment_id: equipmentId,
            
            maintenance_description: maintenanceDescription
        },
        success: function (response) {
            console.log('Response:', response);
            if (response.toLowerCase().includes("successfully")) {
                alert('Success: ' + response);
                closeModal('maintenanceRequestModal');

                
                $('#maintenanceDescription').val('');

                setTimeout(() => {
                    loadEquipment('assigned');
                }, 500);
            } else {
                alert("Error: " + response);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('Error scheduling maintenance. Please try again.');
        }
    });
}

// 🔹 Initialize on page load
$(document).ready(function() {
    loadEquipment('assigned');
    initializeModalFunctions();
});

// 🔹 MODAL FUNCTIONS
function initializeModalFunctions() {
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }

    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('land-modal')) {
            closeModal(event.target.id);
        }
    });

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
}
</script>

<!-- Maintenance Request Modal -->
<div id="maintenanceRequestModal" class="land-modal">
    <div class="land-modal-content">
        <div class="land-modal-header">
            <h3>Report Equipment Issue</h3>
            <span class="land-modal-close" onclick="closeModal('maintenanceRequestModal')">&times;</span>
        </div>
        <div class="land-modal-body">
            <form id="maintenanceRequestForm">
                <div class="form-group">
                    <label>Equipment</label>
                    <input type="text" class="form-control" id="maintenanceEquipment" readonly>
                    <input type="hidden" name="equipment_id" id="maintenanceEquipmentId">
                </div>

                

                <div class="form-group">
                    <label for="maintenanceDescription" style="font-weight: 600; display: block; margin-bottom: 8px; color: #263a4f;">
                        <i class="fas fa-edit"></i> Description:
                    </label>
                    <textarea id="maintenanceDescription" class="form-control" rows="4" 
                              placeholder="Add maintenance description..." 
                              style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px;"></textarea>
                </div>
  
                <div class="form-group text-right" style="gap:10px; display:flex; justify-content:flex-end;">
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                        <button type="button" class="btn btn-secondary" 
                                style="padding: 10px 20px; border-radius: 5px;" 
                                onclick="closeModal('maintenanceRequestModal')">
                            <i class="fas fa-times"></i> Close
                        </button>

                        <button type="button" class="btn btn-warning" 
                                style="background: #ff7607; padding: 10px 20px; border-radius: 5px; color: #fff; border: none;" 
                                onclick="scheduleMaintenanceCurrent()">
                            <i class="fas fa-wrench"></i> Schedule Maintenance
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Message Modal -->
<div id="successModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 350px;">
        <div class="land-modal-body text-center">
            <i class="fas fa-check-circle" style="font-size: 48px; color: #4caf50; margin-bottom: 15px;"></i>
            <h4>Success!</h4>
            <p id="successDetails" style="font-size: 14px;"></p>
            <button type="button" class="dl-btn" style="background: #4caf50; padding: 8px 20px; margin-top: 15px;" onclick="closeModal('successModal')">OK</button>
        </div>
    </div>
</div>

<style>
/* Tab Navigation Styling */
.tab-navigation {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.tab-btn {
    background: #fff;
    border: 2px solid #e0e0e0;
    padding: 12px 25px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    color: #666;
    transition: all 0.3s ease;
}

.tab-btn:hover {
    border-color: #ff7607;
    color: #ff7607;
}

.tab-btn.active {
    background: #ff7607;
    border-color: #ff7607;
    color: #fff;
}

.tab-btn i {
    margin-right: 8px;
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

.default-btn {
    background: #ff7607;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.default-btn:hover {
    background: #e66806;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 118, 7, 0.3);
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
</style>

<?php include 'includes/footer.html'; ?>