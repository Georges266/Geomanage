<?php
include 'includes/header.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Maintenance_Technician") {
    exit();
}
?>

<!doctype html>
<html lang="en">
<body>

<section class="padding">
<div class="container">

<!-- ================= MAINTENANCE REQUESTS ================= -->
<div class="service-item box-shadow mb-5 p-4">
    <h3>Maintenance Requests</h3>

    <input type="text" id="maintenanceSearch" class="form-control mb-3"
           placeholder="Search maintenance..." onkeyup="loadMaintenanceRequests()">

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Equipment</th>
                    <th>Requested By</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="maintenanceBody">
                <tr>
                    <td colspan="5" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================= EQUIPMENT HISTORY ================= -->
<div class="service-item box-shadow p-4">
    <h3>Equipment Maintenance History</h3>

    <input type="text" id="historySearch" class="form-control mb-3"
           placeholder="Search equipment..." onkeyup="loadEquipmentHistory()">

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Equipment ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Last Maintenance</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody id="historyBody">
                <tr>
                    <td colspan="5" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</div>
</section>

<!-- ================= MAINTENANCE DETAILS MODAL ================= -->
<div id="maintenanceModal" class="land-modal" style="display: none;">
    <div class="land-modal-content" style="max-width: 650px;">
        <div class="land-modal-header">
            <h3><i class="fas fa-wrench"></i> Maintenance Request Details</h3>
            <span class="land-modal-close" onclick="closeModal('maintenanceModal')">&times;</span>
        </div>
        <div class="land-modal-body" id="maintenanceModalBody">
            <p class="text-center">Loading...</p>
        </div>
        <div class="land-modal-footer" style="padding: 20px; border-top: 1px solid #ddd; text-align: right;">
            <button class="btn btn-secondary" onclick="closeModal('maintenanceModal')">
                <i class="fas fa-times"></i> Close
            </button>
            <button class="btn btn-success" id="completeMaintenanceBtn">
                <i class="fas fa-check"></i> Mark as Fixed
            </button>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
/* ================= MODAL FUNCTIONS ================= */
function openModal(modalId) {
    $('#' + modalId).fadeIn(300);
    $('body').css('overflow', 'hidden');
}

function closeModal(modalId) {
    $('#' + modalId).fadeOut(300);
    $('body').css('overflow', 'auto');
}

// Close modal on ESC key
$(document).on('keydown', function(e) {
    if (e.key === 'Escape') {
        $('.land-modal').fadeOut(300);
        $('body').css('overflow', 'auto');
    }
});

// Close modal on backdrop click
$(document).on('click', '.land-modal', function(e) {
    if (e.target === this) {
        closeModal(this.id);
    }
});

/* ================= LOAD MAINTENANCE REQUESTS ================= */
function loadMaintenanceRequests() {
    $.ajax({
        url: 'maintenance-show-ajax.php',
        type: 'POST',
        data: {
            action: 'maintenance_requests',
            search: $('#maintenanceSearch').val()
        },
        success: function (response) {
            $('#maintenanceBody').html(response);
        }
    });
}

/* ================= LOAD EQUIPMENT HISTORY ================= */
function loadEquipmentHistory() {
    $.ajax({
        url: 'maintenance-show-ajax.php',
        type: 'POST',
        data: {
            action: 'equipment_history',
            search: $('#historySearch').val()
        },
        success: function (response) {
            $('#historyBody').html(response);
        }
    });
}

/* ================= VIEW MAINTENANCE DETAILS ================= */
$(document).on('click', '.viewMaintenanceBtn', function() {
    var maintenanceId = $(this).data('id');
    
    // Show modal with loading state
    openModal('maintenanceModal');
    $('#maintenanceModalBody').html('<p class="text-center">Loading...</p>');
    
    // Load details via AJAX
    $.ajax({
        url: 'maintenance-fetch-details.php',
        type: 'POST',
        data: { maintenance_id: maintenanceId },
        success: function(response) {
            $('#maintenanceModalBody').html(response);
            $('#completeMaintenanceBtn').data('maintenance-id', maintenanceId);
        },
        error: function() {
            $('#maintenanceModalBody').html('<p class="text-danger">Error loading details.</p>');
        }
    });
});

/* ================= FIX EQUIPMENT (COMPLETE MAINTENANCE) ================= */
$(document).on('click', '#completeMaintenanceBtn', function() {
    var maintenanceId = $(this).data('maintenance-id');
    
    if (!confirm('Mark this equipment as fixed and set it to Available?')) {
        return;
    }
    
    // Get form data
    var formData = new FormData();
    formData.append('maintenance_id', maintenanceId);
    
    // Add total cost if provided (optional)
    var cost = $('#totalCost').val();
    if (cost && parseFloat(cost) > 0) {
        formData.append('total_cost', cost);
    }
    
    // Add bill file if uploaded (optional)
    var billFile = $('#billUpload')[0].files[0];
    if (billFile) {
        formData.append('bill_file', billFile);
    }
    
    // Disable button to prevent double submission
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    
    $.ajax({
        url: 'maintenance-complete-query.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.trim() === 'success') {
                alert('Equipment fixed and marked as available!');
                closeModal('maintenanceModal');
                loadMaintenanceRequests();
            } else {
                alert('Error: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('Error fixing equipment. Please try again.');
        },
        complete: function() {
            $('#completeMaintenanceBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Mark as Fixed');
        }
    });
});

/* ================= LOAD ON PAGE OPEN ================= */
$(document).ready(function () {
    loadMaintenanceRequests();
    loadEquipmentHistory();
});
</script>


<?php include 'includes/footer.html'; ?>
</body>
</html>
