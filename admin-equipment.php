<?php
include 'includes/header.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: ../no_access.php");
    exit();
}
?>
<?php include 'includes/connect.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!doctype html>
<html class="no-js" lang="en">
<head>
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
                <h1>Equipment Management</h1>
                <p>Track and manage surveying equipment inventory</p>
            </div>
        </div>
    </div>
</section>

<!-- Equipment Section -->
<section class="padding">
    <div class="container">
        <!-- Status Filter Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="tab-navigation mb-40">
                    <button class="tab-btn active" data-status="all">
                        <i class="fas fa-list"></i> All Equipment
                    </button>
                    <button class="tab-btn" data-status="Available">
                        <i class="fas fa-check-circle"></i> Available
                    </button>
                    <button class="tab-btn" data-status="Assigned">
                        <i class="fas fa-tools"></i> In Use
                    </button>
                    <button class="tab-btn" data-status="Maintenance">
                        <i class="fas fa-wrench"></i> Maintenance
                    </button>
                    <button class="tab-btn" data-status="Requested">
                        <i class="fas fa-wrench"></i> Requests
                    </button>
                </div>
            </div>
        </div>

        <!-- Equipment List Container (loaded via AJAX) -->
        <div class="row">
            <div class="col-12">
                <div id="equipmentTable"></div>
            </div>
        </div>
    </div>
</section>

<!-- Equipment Details Modal -->
<div id="equipmentDetailsModal" class="land-modal" style="display: none;">
    <div class="land-modal-content" style="max-width: 650px;">
        <div class="land-modal-header">
            <h3 style="margin: 0;">Equipment Details</h3>
            <span class="land-modal-close" onclick="closeModal('equipmentDetailsModal')">&times;</span>
        </div>
        <div class="land-modal-body" id="equipmentDetailsModalBody">
            <p class="text-center">Loading...</p>
        </div>
    </div>
</div>

<!-- Assign Equipment Modal -->
<div id="assignEquipmentModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 500px;">
        <div class="land-modal-header">
            <h3>Assign Equipment to Project</h3>
            <span class="land-modal-close" onclick="closeModal('assignEquipmentModal')">&times;</span>
        </div>
        <div class="land-modal-body">
            <input type="hidden" id="assign_equipment_id">
            <p><strong>Equipment:</strong> <span id="assign_equipment_name"></span></p>
            
            <div class="form-group">
                <label>Select Project:</label>
                <select id="assign_project_id" class="form-control">
                    <option value="">-- Select Project --</option>
                    <?php
                    $projectQuery = mysqli_query($con, "SELECT project_id, project_name FROM project WHERE status = 'active'");
                    while ($proj = mysqli_fetch_assoc($projectQuery)) {
                        echo "<option value='{$proj['project_id']}'>{$proj['project_name']}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="land-modal-footer" style="padding: 20px; border-top: 1px solid #ddd; text-align: right;">
            <button class="btn btn-secondary" onclick="closeModal('assignEquipmentModal')">Cancel</button>
            <button class="btn btn-success" id="confirmAssignBtn">Assign</button>
        </div>
    </div>
</div>

<!-- Success Message Modal -->
<div id="successModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 350px;">
        <div class="land-modal-body text-center">
            <i class="fas fa-check-circle" style="font-size: 48px; color: #4caf50; margin-bottom: 15px;"></i>
            <h4 id="successMessage">Success!</h4>
            <p id="successDetails" style="font-size: 14px;"></p>
            <button type="button" class="dl-btn" style="background: #4caf50; padding: 8px 20px; margin-top: 15px;" onclick="closeModal('successModal')">OK</button>
        </div>
    </div>
</div>

<style>
.tab-navigation {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.tab-btn {
    padding: 12px 20px;
    background: #f5f5f5;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tab-btn:hover {
    background: #e0e0e0;
}
.tab-btn.active {
    background: #ff7607;
    color: white;
}
.tab-btn i {
    font-size: 16px;
}
.land-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    overflow-y: auto;
}
.land-modal-content {
    background: #fff;
    margin: 50px auto;
    border-radius: 8px;
    position: relative;
    padding: 20px;
    max-width: 800px;
    width: 90%;
}
.land-modal-header {
    padding-bottom: 15px;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.land-modal-close {
    cursor: pointer;
    font-size: 24px;
}
</style>

<script>
// ============================================
// LOAD EQUIPMENT WITH STATUS FILTER
// ============================================
function loadEquipment(status = null) {
    if (status === null) {
        status = $('.tab-btn.active').data('status');
    }
    
    $('#equipmentTable').html('<div class="text-center" style="padding: 40px;"><i class="fas fa-spinner fa-spin" style="font-size: 36px; color: #ff7607;"></i><p style="margin-top: 15px;">Loading equipment...</p></div>');
    
    $.ajax({
        url: 'admin-equipment-show-ajax.php',
        type: 'POST',
        data: { status: status },
        success: function(response) {
            $('#equipmentTable').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading equipment:', error);
            $('#equipmentTable').html(
                '<div class="text-center" style="padding: 40px;"><i class="fas fa-exclamation-triangle" style="font-size: 36px; color: #f44336;"></i><p style="margin-top: 15px; color: #f44336;">Failed to load equipment. Please try again.</p><button class="dl-btn" style="margin-top: 15px;" onclick="loadEquipment()">Retry</button></div>'
            );
        }
    });
}


// TAB NAVIGATION (STATUS FILTERS)

$(document).on('click', '.tab-btn', function() {
    $('.tab-btn').removeClass('active');
    $(this).addClass('active');
    const status = $(this).data('status');
    loadEquipment(status);
});


// MODAL FUNCTIONS

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


// OPEN EQUIPMENT DETAILS MODAL

$(document).on('click', '.viewEquipmentDetailsBtn', function() {
    let equipmentId = $(this).data('id');

    // Move modal to body if not already there
    if ($('#equipmentDetailsModal').parent()[0].tagName !== 'BODY') {
        $('#equipmentDetailsModal').appendTo('body');
    }

    // Show modal with loading state
    $('#equipmentDetailsModal').fadeIn(300);
    $('body').css('overflow', 'hidden');
    $('#equipmentDetailsModalBody').html('<p class="text-center">Loading...</p>');

    // Load equipment details via AJAX
    $.post('admin-equipment-fetch-details.php', { 
        equipment_id: equipmentId 
    }, function(data) {
        $('#equipmentDetailsModalBody').html(data);
    }).fail(function() {
        $('#equipmentDetailsModalBody').html('<p class="text-danger">Error loading equipment details.</p>');
    });
});


// SCHEDULE MAINTENANCE

function scheduleMaintenanceCurrent(equipmentId) {
    const maintenanceNote = $('#maintenanceNote').val().trim();
    
    if (!maintenanceNote) {
        alert('Please enter maintenance notes before scheduling.');
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
            maintenance_note: maintenanceNote
        },
        success: function(response) {
            // Because PHP now returns plain text, no JSON parse needed
            if (response.includes("successfully")) {
                showSuccess('Maintenance Scheduled', response);
                closeModal('equipmentDetailsModal');

                setTimeout(() => {
                    loadEquipment();
                }, 1500);
            } else {
                alert("Error: " + response);
            }
        },
        error: function() {
            alert('Error scheduling maintenance. Please try again.');
        }
    });
}

// Approve equipment request
$(document).on('click', '.approveRequestBtn', function() {
    var equipmentId = $(this).data('equipment-id');
    var projectId = $(this).data('project-id');
    var equipmentName = $(this).data('equipment-name');
    var projectName = $(this).data('project-name');
    var leadName = $(this).data('lead-name');
    
    if (!confirm('Approve equipment request?\n\nEquipment: ' + equipmentName + '\nProject: ' + projectName + '\nLead Engineer: ' + leadName)) {
        return;
    }
    
    $.ajax({
        url: 'admin-equipment-approve-request-query.php',
        type: 'POST',
        data: {
            equipment_id: equipmentId,
            project_id: projectId
        },
        success: function(response) {
            if (response.trim() === 'success') {
                showSuccess('Request Approved', 'Equipment has been assigned to the project successfully!');
                setTimeout(() => {
                    loadEquipment();
                }, 1500);
            } else {
                alert('Error: ' + response);
            }
        },
        error: function() {
            alert('Error approving request. Please try again.');
        }
    });
});


// ============================================
// RELOAD EQUIPMENT MODAL
// ============================================
function reloadEquipmentModal(equipmentId) {
    $.ajax({
        url: 'admin-equipment-fetch-details.php',
        type: 'POST',
        data: { equipment_id: equipmentId },
        success: function(response) {
            $('#equipmentDetailsModalBody').html(response);
        },
        error: function() {
            $('#equipmentDetailsModalBody').html('<p class="text-danger">Error reloading equipment details.</p>');
        }
    });
}




// Unassign equipment
$(document).on('click', '.unassignEquipmentBtn', function() {
    if (!confirm('Are you sure you want to unassign this equipment?')) {
        return;
    }
    
    var equipmentId = $(this).data('equipment-id');
    var projectId = $(this).data('project-id');
    
    $.ajax({
        url: 'admin-equipment-reject-request-query.php',
        type: 'POST',
        data: {
            equipment_id: equipmentId,
            project_id: projectId
        },
        success: function(response) {
            if (response.trim() === 'success') {
                alert('Equipment unassigned successfully!');
                loadEquipment(); // Reload the equipment list
            } else {
                alert('Error: ' + response);
            }
        }
    });
});


// ============================================
// SHOW SUCCESS MESSAGE
// ============================================
function showSuccess(message, details) {
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successDetails').textContent = details;
    openModal('successModal');
}

// ============================================
// INITIALIZE ON PAGE LOAD
// ============================================
$(document).ready(function() {
    initializeModalFunctions();
    loadEquipment('all');
});
</script>

<?php include 'includes/footer.html'; ?>
</body>
</html>