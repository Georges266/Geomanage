<?php
include 'includes/connect.php';

$project_id = (int)$_POST['project_id'];

// Get project details
$query = "SELECT * FROM project WHERE project_id = $project_id";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo "<p class='text-danger'>Project not found.</p>";
    exit;
}

// Get assigned service requests for this project
$serviceRequestsQuery = "
    SELECT sr.request_id, sr.request_date, s.service_name, u.full_name as client_name
    FROM service_request sr
    JOIN service s ON sr.service_id = s.service_id
    JOIN client c ON sr.client_id = c.client_id
    JOIN user u ON c.user_id = u.user_id
    WHERE sr.project_id = $project_id
";
$serviceRequestsResult = mysqli_query($con, $serviceRequestsQuery);

// Get available (unassigned) service requests
$availableRequestsQuery = "
    SELECT sr.request_id, sr.request_date, s.service_name, u.full_name as client_name
    FROM service_request sr
    JOIN service s ON sr.service_id = s.service_id
    JOIN client c ON sr.client_id = c.client_id
    JOIN user u ON c.user_id = u.user_id
    WHERE sr.status = 'approved' AND sr.project_id IS NULL
    ORDER BY sr.request_date DESC
";
$availableRequestsResult = mysqli_query($con, $availableRequestsQuery);

// Get lead engineer details
$leadEngineerQuery = "
    SELECT le.lead_engineer_id, u.full_name, u.email
    FROM lead_engineer le
    JOIN user u ON le.user_id = u.user_id
    WHERE le.lead_engineer_id = {$row['lead_engineer_id']}
";
$leadEngineerResult = mysqli_query($con, $leadEngineerQuery);
$leadEngineer = mysqli_fetch_assoc($leadEngineerResult);

// Get assigned equipment
$equipmentQuery = "
    SELECT e.equipment_id, e.equipment_name, e.model
    FROM uses_project_equipment upe
    JOIN equipment e ON upe.equipment_id = e.equipment_id
    WHERE upe.project_id = $project_id
";
$equipmentResult = mysqli_query($con, $equipmentQuery);

// Get available equipment
$availableEquipmentQuery = "
    SELECT equipment_id, equipment_name, model
    FROM equipment
    WHERE status = 'Available'
    ORDER BY equipment_name
";
$availableEquipmentResult = mysqli_query($con, $availableEquipmentQuery);
?>

<form id="editProjectForm">
    <input type="hidden" id="edit_project_id" value="<?php echo $row['project_id']; ?>">
    
    <div class="form-group">
        <label>Project Name</label>
        <input type="text" id="Project_Name" class="form-control" value="<?php echo htmlspecialchars($row['project_name']); ?>" required>
    </div>

    <div class="row">
        <div class="col-md-6">
            <label>Start Date</label>
            <input type="date" id="Start_Date" class="form-control" value="<?php echo $row['start_date']; ?>" required>
        </div>
        <div class="col-md-6">
            <label>End Date</label>
            <input type="date" id="End_Date" class="form-control" value="<?php echo $row['end_date']; ?>" required>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6">
            <label>Lead Engineer</label>
            <select id="Lead_Engineer" class="form-control" required>
                <?php
                $engineers = mysqli_query($con, "
                    SELECT le.lead_engineer_id, u.full_name
                    FROM lead_engineer le
                    JOIN user u ON le.user_id = u.user_id
                ");
                while ($eng = mysqli_fetch_assoc($engineers)) {
                    $selected = ($eng['lead_engineer_id'] == $row['lead_engineer_id']) ? 'selected' : '';
                    echo "<option value='{$eng['lead_engineer_id']}' $selected>{$eng['full_name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-6">
            <label>Team Size</label>
            <input type="number" id="Team_Size" class="form-control" value="<?php echo $row['team_size']; ?>" min="1" max="30" required>
        </div>
    </div>

    <!-- Current Lead Engineer Info -->
    <?php if ($leadEngineer): ?> <!--checks if $leadEngineer exists (is not empty or null).-->
    <div class="alert alert-info mt-2" style="padding: 10px; font-size: 13px;">
        <strong>Current Lead Engineer:</strong> <?php echo htmlspecialchars($leadEngineer['full_name']); ?>
    </div>
    <?php endif; ?>

    <div class="form-group mt-2">
        <label>Project Description</label>
        <textarea id="Project_Description" class="form-control" rows="3"><?php echo htmlspecialchars($row['description']); ?></textarea>
    </div>

    <!-- Assigned Service Requests -->
    <div class="form-group mt-3">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <label style="font-weight: 600; margin: 0;">Assigned Service Requests</label>
            <button type="button" class="btn btn-sm btn-primary" onclick="toggleAddServiceRequests()" style="padding: 4px 12px; font-size: 12px;">
                <i class="fas fa-plus"></i> Add Requests
            </button>
        </div>
        
        <!-- Add Service Requests Section (Hidden by default) -->
        <div id="addServiceRequestsSection" style="display: none; margin-bottom: 15px; border: 2px solid #007bff; border-radius: 5px; padding: 10px; background: #f0f8ff;">
            <h6 style="margin-bottom: 10px; color: #007bff;">Available Service Requests</h6>
            <div style="max-height: 200px; overflow-y: auto;">
                <?php 
                if ($availableRequestsResult && mysqli_num_rows($availableRequestsResult) > 0) {//mysqli_num_rows counts how many rows the query returned.
                    while ($availSr = mysqli_fetch_assoc($availableRequestsResult)) {
                ?>
                    <div style="padding: 8px; margin-bottom: 8px; background: white; border-radius: 4px; border: 1px solid #ddd;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <strong style="font-size: 13px;">Request #<?php echo $availSr['request_id']; ?></strong>
                                <p style="margin: 3px 0; font-size: 12px; color: #666;">
                                    <strong>Service:</strong> <?php echo htmlspecialchars($availSr['service_name']); ?>
                                </p>
                                <p style="margin: 3px 0; font-size: 12px; color: #666;">
                                    <strong>Client:</strong> <?php echo htmlspecialchars($availSr['client_name']); ?>
                                </p>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" style="padding: 2px 8px; font-size: 11px;" onclick="addServiceRequest(<?php echo $availSr['request_id']; ?>)">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                ?>
                    <p style="text-align: center; color: #999; padding: 10px; margin: 0;">No available service requests.</p>
                <?php } ?>
            </div>
            <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="toggleAddServiceRequests()" style="padding: 4px 12px; font-size: 12px;">
                Close
            </button>
        </div>

        <div style="border: 1px solid #ddd; border-radius: 5px; padding: 10px; max-height: 200px; overflow-y: auto; background: #f9f9f9;">
            <?php 
            if ($serviceRequestsResult && mysqli_num_rows($serviceRequestsResult) > 0) {
                while ($sr = mysqli_fetch_assoc($serviceRequestsResult)) {
            ?>
                <div style="padding: 8px; margin-bottom: 8px; background: white; border-radius: 4px; border-left: 3px solid #ff7607;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <strong style="font-size: 13px;">Request #<?php echo $sr['request_id']; ?></strong>
                            <p style="margin: 3px 0; font-size: 12px; color: #666;">
                                <strong>Service:</strong> <?php echo htmlspecialchars($sr['service_name']); ?>
                            </p>
                            <p style="margin: 3px 0; font-size: 12px; color: #666;">
                                <strong>Client:</strong> <?php echo htmlspecialchars($sr['client_name']); ?>
                            </p>
                            <p style="margin: 3px 0; font-size: 11px; color: #999;">
                                Requested: <?php echo date('M d, Y', strtotime($sr['request_date'])); ?>
                            </p>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" style="padding: 2px 8px; font-size: 11px;" onclick="removeServiceRequest(<?php echo $sr['request_id']; ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            <?php 
                }
            } else {
            ?>
                <p style="text-align: center; color: #999; padding: 20px; margin: 0;">No service requests assigned to this project.</p>
            <?php } ?>
        </div>
    </div>

    <!-- Assigned Equipment -->
    <div class="form-group mt-3">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <label style="font-weight: 600; margin: 0;">Assigned Equipment</label>
            <button type="button" class="btn btn-sm btn-primary" onclick="toggleAddEquipment()" style="padding: 4px 12px; font-size: 12px;">
                <i class="fas fa-plus"></i> Add Equipment
            </button>
        </div>

        <!-- Add Equipment Section (Hidden by default) -->
        <div id="addEquipmentSection" style="display: none; margin-bottom: 15px; border: 2px solid #007bff; border-radius: 5px; padding: 10px; background: #f0f8ff;">
            <h6 style="margin-bottom: 10px; color: #007bff;">Available Equipment</h6>
            <div style="max-height: 200px; overflow-y: auto;">
                <?php 
                if ($availableEquipmentResult && mysqli_num_rows($availableEquipmentResult) > 0) {
                    while ($availEquip = mysqli_fetch_assoc($availableEquipmentResult)) {
                ?>
                    <div style="padding: 8px; margin-bottom: 8px; background: white; border-radius: 4px; border: 1px solid #ddd;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="flex: 1;">
                                <strong style="font-size: 13px;"><?php echo htmlspecialchars($availEquip['equipment_name']); ?></strong>
                                <?php if (!empty($availEquip['model'])): ?>
                                    <span style="font-size: 12px; color: #666;"> - <?php echo htmlspecialchars($availEquip['model']); ?></span>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-success" style="padding: 2px 8px; font-size: 11px;" onclick="addEquipment(<?php echo $availEquip['equipment_id']; ?>)">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                ?>
                    <p style="text-align: center; color: #999; padding: 10px; margin: 0;">No available equipment.</p>
                <?php } ?>
            </div>
            <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="toggleAddEquipment()" style="padding: 4px 12px; font-size: 12px;">
                Close
            </button>
        </div>

        <div style="border: 1px solid #ddd; border-radius: 5px; padding: 10px; max-height: 200px; overflow-y: auto; background: #f9f9f9;">
            <?php 
            if ($equipmentResult && mysqli_num_rows($equipmentResult) > 0) {
                while ($equip = mysqli_fetch_assoc($equipmentResult)) {
            ?>
                <div style="padding: 8px; margin-bottom: 8px; background: white; border-radius: 4px; border-left: 3px solid #4caf50;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="font-size: 13px;"><?php echo htmlspecialchars($equip['equipment_name']); ?></strong>
                            <?php if (!empty($equip['model'])): ?>
                                <span style="font-size: 12px; color: #666;"> - <?php echo htmlspecialchars($equip['model']); ?></span>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" style="padding: 2px 8px; font-size: 11px;" onclick="removeEquipment(<?php echo $equip['equipment_id']; ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            <?php 
                }
            } else {
            ?>
                <p style="text-align: center; color: #999; padding: 20px; margin: 0;">No equipment assigned to this project.</p>
            <?php } ?>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="form-group text-right mt-3" style="display:flex; justify-content:space-between; gap:10px;">
        <button type="button" class="btn btn-danger" onclick="deleteProject(<?php echo $project_id; ?>)">
            <i class="fas fa-trash"></i> Delete Project
        </button>
        <div style="display:flex; gap:10px;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('editProjectModal')">Cancel</button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>
</form>

<script>
// Toggle add service requests section
function toggleAddServiceRequests() {
    $('#addServiceRequestsSection').slideToggle(300);
}

// Toggle add equipment section
function toggleAddEquipment() {
    $('#addEquipmentSection').slideToggle(300);
}

// Handle form submission
$('#editProjectForm').on('submit', function(e) {
    e.preventDefault();//Do NOT reload the page and do NOT submit the form the normal way.
    
    const projectData = {
        project_id: $('#edit_project_id').val(),
        project_name: $('#Project_Name').val(),
        start_date: $('#Start_Date').val(),
        end_date: $('#End_Date').val(),
        lead_engineer_id: $('#Lead_Engineer').val(),
        team_size: $('#Team_Size').val(),
        description: $('#Project_Description').val()
    };
    
    $.ajax({
        url: 'admin-project-update-query.php',
        type: 'POST',
        data: projectData,
        success: function(response) {
            alert('Project updated successfully!');
            closeModal('editProjectModal');
            loadProjects(); // Reload the projects list
        },
        error: function() {
            alert('Error updating project.');
        }
    });
});

// Add service request to project
function addServiceRequest(requestId) {
    $.ajax({
        url: 'admin-project-add-service-query.php',
        type: 'POST',
        data: { 
            request_id: requestId,
            project_id: $('#edit_project_id').val()
        },
        success: function(response) {
          //  alert('Service request added successfully!');
            // Reload the modal content
             reloadProjectModal($('#edit_project_id').val()); // Cleaner!
        },
        error: function() {
            alert('Error adding service request.');
        }
    });
}

// Remove service request from project
function removeServiceRequest(requestId) {
    if (confirm('Remove this service request from the project?')) {
        $.ajax({
            url: 'admin-project-remove-service-query.php',
            type: 'POST',
            data: { 
                request_id: requestId,
                project_id: $('#edit_project_id').val()
            },
            success: function(response) {
               // alert('Service request removed!');
                // Reload the modal content
                 reloadProjectModal($('#edit_project_id').val()); // Cleaner!
            },
            error: function() {
                alert('Error removing service request.');
            }
        });
    }
}

// Add equipment to project
function addEquipment(equipmentId) {
    $.ajax({
        url: 'admin-project-add-equipment-query.php',
        type: 'POST',
        data: { 
            equipment_id: equipmentId,
            project_id: $('#edit_project_id').val()
        },
        success: function(response) {
         //   alert('Equipment added successfully!');
            // Reload the modal content
            reloadProjectModal($('#edit_project_id').val()); // Cleaner!
        },
        error: function() {
            alert('Error adding equipment.');
        }
    });
}

// Remove equipment from project
function removeEquipment(equipmentId) {
    if (confirm('Remove this equipment from the project?')) {
        $.ajax({
            url: 'admin-project-remove-equipment.php',
            type: 'POST',
            data: { 
                equipment_id: equipmentId,
                project_id: $('#edit_project_id').val()
            },
            success: function(response) {
              //  alert('Equipment removed!');
                // Reload the modal content
                reloadProjectModal($('#edit_project_id').val());

            },
            error: function() {
                alert('Error removing equipment.');
            }
        });
    }
}

// Delete entire project
function deleteProject(projectId) {
    if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
        $.ajax({
            url: 'admin-project-delete-query.php',
            type: 'POST',
            data: { project_id: projectId },
            success: function(response) {
                alert('Project deleted successfully!');
                closeModal('editProjectModal');
                loadProjects(); // Reload the projects list
            },
            error: function() {
                alert('Error deleting project.');
            }
        });
    }
}



function reloadProjectModal(projectId) {
    // Show loading message
    $("#editProjectModalBody").html('<p class="text-center">Loading...</p>');
    
    $.ajax({
        url: 'admin-project-fetch-project-info.php',
        type: 'POST',
        data: { project_id: projectId },
        success: function(response) {
            $("#editProjectModalBody").html(response);
        }
    });
}



</script>
