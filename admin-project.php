<?php
include 'includes/header.php'; 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: ../no_access.php");
    exit();
}
?>
<?php
include 'includes/connect.php';
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php
// Add this at the very beginning of admin-project.php, right after include statements


// Check for success/error messages from session FIRST
$successMessage = false;
$errorMessage = false;

if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear it immediately after reading
}

if (isset($_SESSION['error_message'])) {
    $errorMessage = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear it immediately after reading
}

// NOW handle the POST request (for form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectName       = mysqli_real_escape_string($con, $_POST['Project_Name']);
    $startDate         = $_POST['Start_Date'];
    $endDate           = $_POST['End_Date'];
    $leadEngineerId    = $_POST['Lead_Engineer'];
    $projectDesc       = mysqli_real_escape_string($con, $_POST['Project_Description']);
    $Team_Size         = $_POST['Team_Size'];
    
    // Insert project
    $sqlProject = "INSERT INTO project 
        (project_name, start_date, end_date, lead_engineer_id, team_size, description, status)
        VALUES ('$projectName', '$startDate', '$endDate', '$leadEngineerId', '$Team_Size', '$projectDesc', 'active')";

    if (mysqli_query($con, $sqlProject)) {
        $projectId = mysqli_insert_id($con); // ID of the last project inserted
        
        // Check if approved requests are selected
        if (empty($_POST['approved_requests'])) {
            $_SESSION['error_message'] = "No request selected";
            header("Location: admin-project.php");
            exit();
        }

        // Arrays to track lands for this project (avoid duplicates)
        $processedLands = array();

        // Update service requests with project ID AND get their land IDs
        foreach ($_POST['approved_requests'] as $request_id) {
            $request_id = (int)$request_id; // Cast to int for safety
            
            // Get the land_id for this service request
            $landQuery = "SELECT land_id FROM service_request WHERE request_id = $request_id";
            $landResult = mysqli_query($con, $landQuery);
            
            if ($landResult && $landRow = mysqli_fetch_assoc($landResult)) {
                $land_id = (int)$landRow['land_id'];
                
                // Add to includes_project_land table (only if not already added)
                if (!in_array($land_id, $processedLands)) {
                    $insertLandProject = "INSERT INTO includes_project_land (project_id, land_id) 
                                         VALUES ($projectId, $land_id)";
                    mysqli_query($con, $insertLandProject);
                    
                    // Mark this land as processed
                    $processedLands[] = $land_id;
                }
            }
            
            // Update service request with project ID
            $updateQuery = "UPDATE service_request 
                    SET project_id = $projectId 
                    WHERE request_id = $request_id";
            mysqli_query($con, $updateQuery);
        }

        // Handle equipment assignment
        if (!empty($_POST['equipment'])) {
            foreach ($_POST['equipment'] as $equipmentId) {
                $equipmentId = (int)$equipmentId;
                mysqli_query($con, "INSERT INTO uses_project_equipment (project_id, equipment_id) 
                                    VALUES ($projectId, $equipmentId)");
                mysqli_query($con, "UPDATE equipment SET status='Assigned' WHERE equipment_id=$equipmentId");
            }
        }

        // Store success message in session
        $_SESSION['success_message'] = "Project created successfully!";
        
        // REDIRECT to the same page (GET request)
        header("Location: admin-project.php");
        exit();
        
    } else {
        // Error inserting project
        $_SESSION['error_message'] = "Error: " . mysqli_error($con);
        header("Location: admin-project.php");
        exit();
    }
}
?>

<!-- Page Header -->
<section class="page-header padding bg-grey">
    <div class="container">
        <div class="row d-flex align-items-center">
            <div class="col-lg-8">
                <h1>Project Management</h1>
                <p>Create projects from approved service requests and assign equipment</p>
            </div>
            <div class="col-lg-4 text-right">
                <button type="button" class="default-btn" onclick="openModal('createProjectModal')">
                    <i class="fas fa-plus"></i> Create New Project
                </button>
            </div>
        </div>
    </div>
</section>

<?php
// Get active projects
$query = "SELECT COUNT(*) AS total_projects_active 
          FROM project
          WHERE project.status = 'active' ";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_projects_active = $row['total_projects_active'];

?>

<?php
// Get completed projects
$query = "SELECT COUNT(*) AS total_projects_active 
          FROM project
          WHERE project.status = 'completed'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_projects_completed = $row['total_projects_active'];

?>


<!-- Rest of your HTML content stays the same... -->
<!-- Tab Navigation -->
<section class="padding">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="tab-navigation mb-40">
                    <button class="tab-btn active" data-status="active">Active Projects</button>
                    <button class="tab-btn" data-status="completed">Completed Projects</button>
                </div>
            </div>
        </div>

        <!-- Active Projects Tab -->
        <div id="active-projects" class="tab-content active">
            <!-- Search and Filter -->
            <div class="row mb-30">
                <div class="col-md-12">
                    <div class="service-item box-shadow padding-15">
                        <div class="row">
                            <div class="col-md-4 padding-10">
                                <input type="text" id="projectNameFilter" class="form-control" placeholder="Search projects...">
                            </div>
                            <div class="col-md-4">
                            <input type="text" id="clientNameSearch" class="form-control" placeholder="Search by client name...">
                            </div>
                            <?php 
                             $query='SELECT user.full_name,lead_engineer.lead_engineer_id
                                FROM user,lead_engineer
                                WHERE lead_engineer.user_id=user.user_id;';
                             $result=mysqli_query($con,$query);
                             
                              
                             ?>
                            <div class="col-md-3 padding-10">
                            <label style="font-weight: 600; margin-bottom: 5px; display: block;"></label>
                            <select class="form-control" id="leadengineerFilter">
                                <option value="">-- Select Lead Engineer --</option>

                                  <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                        <option value="<?php echo $row['lead_engineer_id']; ?>">
                                            <?php echo $row['full_name']; ?>
                                        </option>
                                  <?php } ?>  
    
                            </select>
                            </div>
                            

                            <div class="col-md-3 padding-10">
                            <label style="font-weight: 600; margin-bottom: 5px; display: block;"></label>
                            <select class="form-control" id="dateRangeFilter">
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="3months">Last 3 Months</option>
                            </select>
                            </div>

                            
                            <div class="col-md-4 padding-10">
                                <button class="default-btn" id="applyFilters" style="width: 100%; padding: 10px;">Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>


        
        <div class="row">
            <div class="col-12">
                <div id="projectTable"></div>
            </div>
        </div>
    </div>
</section>

<script>
// 🔹 Load projects via AJAX
function loadProjects(status = null) {
    if (status === null) {
        status = $('.tab-btn.active').data('status');
    }
    
    $.ajax({
        url: 'admin-project-show-ajax.php',
        type: 'POST',
        data: { 
            status: status,
            projectName: $('#projectNameFilter').val(),
            clientName: $('#clientNameSearch').val(),
            dateRange: $('#dateRangeFilter').val(),
            leadengineer: $('#leadengineerFilter').val()
        },
        success: function(response) {
            $('#projectTable').html(response);
            console.log("Projects loaded successfully");
        },
        error: function(xhr, status, error) {
            console.error('Error loading projects:', error);
            $('#projectTable').html(
                '<div class="col-12"><p class="text-center text-danger p-3">Error loading projects.</p></div>'
            );
        }
    });
}

// 🔹 Tab Navigation
$(document).on('click', '.tab-btn', function() {
    $('.tab-btn').removeClass('active');
    $(this).addClass('active');
    const status = $(this).data('status');
    loadProjects(status);
});

// 🔹 Apply Filters
$(document).on('click', '#applyFilters', function() {
    const activeTab = $('.tab-btn.active').data('status');
    loadProjects(activeTab);
});

$('#dateRangeFilter,#leadengineerFilter').on('change', function() {
    loadProjects();
});

// 🔹 Initialize on page load
$(document).ready(function() {
    loadProjects('active');
    initializeModalFunctions();
});

// 🔹 MODAL FUNCTIONS
function initializeModalFunctions() {
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';//When a modal opens, you don’t want the user to scroll the page behind it.
            
            if (modalId === 'createProjectModal') {
                showStep(1);
            }
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
        }// close when clicking outside
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

// 🔹 FORM STEP FUNCTIONS
function showStep(stepNumber) {
    const steps = document.querySelectorAll('.form-step');
    steps.forEach(step => {
        step.classList.remove('active');//Hide them (remove the active class)
    });
    
    const targetStep = document.getElementById('step' + stepNumber);//Find the step the user wants
    if (targetStep) {
        targetStep.classList.add('active');
    }
}

function goToStep2() {
    const startDate = new Date(document.getElementById('startDate').value);
    const endDate = new Date(document.getElementById('endDate').value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (startDate < today) {
        alert('Start date cannot be in the past');
        return;
    }
    
    if (endDate <= startDate) {
        alert('End date must be after start date');
        return;
    }

    const fields = document.querySelectorAll('#step1 input, #step1 select, #step1 textarea');
    let valid = true;

    fields.forEach(function(field) {
        if (!field.checkValidity()) {
            valid = false;
            field.reportValidity();
        }
    });

    if (valid) showStep(2);
}

function goToStep3() {
    const checked = document.querySelectorAll('#step2 input[name="approved_requests[]"]:checked');
    if (checked.length > 0) {
        showStep(3);
    } else {
        alert('Please select at least one approved service request to continue.');
    }
}

// Open Edit Project Modal
$(document).on('click', '.editProjectBtn', function() {
    let projectId = $(this).data('id');

    if ($('#editProjectModal').parent()[0].tagName !== 'BODY') {
        $('#editProjectModal').appendTo('body');
    }

    $('#editProjectModal').fadeIn(300);
    $('body').css('overflow', 'hidden');
    
    // ✅ Clear only the body content
    $('#editProjectModalBody').html('<p class="text-center">Loading...</p>');

    $.post('admin-project-fetch-project-info.php', { project_id: projectId }, function(data) {
        $('#editProjectModalBody').html(data);
    }).fail(function() {
        $('#editProjectModalBody').html('<p class="text-danger">Error loading project info.</p>');
    });
});

// Open details Project Modal
$(document).on('click', '.detailsProjectBtn', function() {
    let projectId = $(this).data('id');

    if ($('#detailsProjectModal').parent()[0].tagName !== 'BODY') {
        $('#detailsProjectModal').appendTo('body');
    }

    $('#detailsProjectModal').fadeIn(300);
    $('body').css('overflow', 'hidden');
    $('#detailsProjectModalBody').html('<p class="text-center">Loading...</p>');

    $.post('admin-project-fetch-projectDetails-info.php', { project_id: projectId }, function(data) {
        $('#detailsProjectModalBody').html(data);
    }).fail(function() {
        $('#detailsProjectModalBody').html('<p class="text-danger">Error loading project info.</p>');
    });
});

// 🔹 SEND EMAIL FUNCTIONALITY
let selectedProject = null;
let selectedEmail = null;
let selectedPrice = null;

// Open Send Email Modal (delegated event for dynamic buttons)
$(document).on("click", ".sendEmailModalBtn", function (e) {
    e.preventDefault();
    
    selectedProjectId = $(this).data("project_id");
    selectedProject = $(this).data("project");
    selectedEmail   = $(this).data("email");
    selectedPrice   = $(this).data("price");
    selectedServices = $(this).data("services");
    selectedLandNb = $(this).data("land_nb");
    selectedLandAddress = $(this).data("land_address");

    console.log("Opening email modal for project:", selectedProject, selectedEmail, selectedPrice);

    $("#modalTotalPrice").text(selectedPrice ? selectedPrice + " USD" : "N/A");  
    $("#modalClientEmail").text(selectedEmail || "No email provided");

    openModal('sendEmailModal');
});

// Confirm Send Email  
$(document).on("click", "#confirmSendEmailBtn", function () {
    console.log("Sending email for project ID:", selectedProjectId);
    
    // Validate data
    if (!selectedProjectId || !selectedEmail) {
        alert("Missing project information. Please try again.");
        return;
    }
    
    // Disable button to prevent double-click
    $(this).prop('disabled', true).text('Sending...');
    
    // Close modal immediately
    closeModal('sendEmailModal');
    
    // Send email in background
    $.post("admin-project-send-email.php", {
        project_id: selectedProjectId,
        project_name: selectedProject,
        total_price: selectedPrice,
        client_email: selectedEmail,
        Services: selectedServices,
        Land_Nb: selectedLandNb,
        Land_Address: selectedLandAddress
    }, function (response) {
        alert(response);
        loadProjects();
    }).fail(function(xhr, status, error) {
        alert("Error sending email. Please try again.");
    }).always(function() {
        // Re-enable button
        $('#confirmSendEmailBtn').prop('disabled', false).text('Send Email');
    });
});



//Open PDF
$(document).on('click', '.showPDFBtn', function() {
    var projectId = $(this).data('id');
    
    $.ajax({
        url: 'admin-project-get_pdf_path.php',
        type: 'POST',
        data: { id: projectId },
        success: function(response) {
            // Check if response contains an error
            if(response.startsWith('ERROR:')) {
                alert(response); // Shows: "ERROR: No deliverable found for this project"
            } else if(response) {
                window.open(response, '_blank');
            } else {
                alert('Unable to open deliverable');
            }
        },
        error: function() {
            alert('Error connecting to server');
        }
    });
});

// View on Map button handler
$(document).on('click', '.viewMapBtn', function() {
    const requestId = $(this).data('request-id');
    
    if (!requestId) {
        alert('Request ID not available.');
        return;
    }
    
    // Open admin map viewer with request_id
    const mapUrl = `admin-map-viewer.php?request_id=${requestId}`;
    window.open(mapUrl, 'MapView', 'width=1200,height=800,scrollbars=yes,resizable=yes');
});


</script>

<!-- Create Project Modal -->
<form id="createProjectForm" method="POST" action="">
<div id="createProjectModal" class="land-modal">
    <div class="land-modal-content">
        <div class="land-modal-header">
            <h3>Create New Project from Approved Requests</h3>
            <span class="land-modal-close" onclick="closeModal('createProjectModal')">&times;</span>
        </div>
        <div class="land-modal-body">

            <!-- Step 1 -->
            <div class="form-step active" id="step1">
                <h4 class="mb-3 step-title">Step 1: Project Information</h4>

                <div class="form-group">
                    <label>Project Name</label>
                    <input type="text" name="Project_Name" class="form-control" required>
                </div>

                <div class="row form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" name="Start_Date" class="form-control" required id="startDate">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" name="End_Date" class="form-control" required id="endDate">
                        </div>
                    </div>
                </div>

                <div class="row form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Lead Engineer</label>
                            <select class="form-control" name="Lead_Engineer" required>
                                <option value="">Select Lead Engineer</option>
                                <?php 
                                $leadEngineerQuery = mysqli_query($con,"
                                    SELECT lead_engineer.lead_engineer_id, user.full_name
                                    FROM lead_engineer
                                    JOIN user ON lead_engineer.user_id = user.user_id
                                   
                                ");
                                
                                if ($leadEngineerQuery && mysqli_num_rows($leadEngineerQuery) > 0) {
                                    while($row = mysqli_fetch_assoc($leadEngineerQuery)) {
                                        echo "<option value='".$row['lead_engineer_id']."'>".$row['full_name']."</option>";
                                    }
                                } else {
                                    echo "<option value=''>No lead engineers available</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Team Size</label>
                            <input type="number" name="Team_Size" class="form-control" min="1" max="30" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Project Description</label>
                    <textarea name="Project_Description" class="form-control" rows="3" required placeholder="Describe the project scope, objectives, and any special requirements..."></textarea>
                </div>

                <div class="form-navigation">
                    <button type="button" class="default-btn" onclick="goToStep2()">Next</button>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="form-step" id="step2">
                <h4 class="mb-3 step-title">Step 2: Select Approved Services</h4>

                <div class="approved-requests-list">
                    <?php 
                    $sql = "SELECT 
                                sr.request_id,
                                sr.status,
                                sr.approval_status,
                                u.user_id,
                                u.full_name,
                                s.service_name,
                                p.project_id,
                                land.land_address
                            FROM service_request sr
                            JOIN service s ON sr.service_id = s.service_id
                            JOIN land ON land.land_id = sr.land_id
                            JOIN client c ON c.client_id = sr.client_id
                            JOIN user u ON u.user_id = c.user_id
                            LEFT JOIN project p ON p.project_id = sr.project_id
                            WHERE sr.status = 'approved'
                            AND sr.project_id IS NULL
                            ORDER BY land.land_id, sr.approval_status DESC;
                            ";
                    
                    $result = mysqli_query($con, $sql);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)){ 
                    ?>
                    <div class="request-item">
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   name="approved_requests[]" 
                                   value="<?php echo $row['request_id']; ?>">
                            <label class="form-check-label">
                                Request #<?php echo $row['request_id']; ?> - <?php echo $row['full_name']; ?>
                            </label>
                        </div>
                        <div class="request-details">
                            <p class="request-service"><strong>Service:</strong> <?php echo $row['service_name']; ?></p>
                            <p class="request-service"><strong>Land:</strong> <?php echo $row['land_address']; ?></p>
                            <p class="request-date">
                                Approved on: <?php echo $row['approval_status'] ? date('M d, Y', strtotime($row['approval_status'])) : 'N/A'; ?>
                            </p>
                        </div>
                    </div>
                    <?php 
                        }
                    } else { 
                    ?>
                    <p class="no-requests">No approved service requests available.</p>
                    <?php } ?>
                </div>

                <div class="form-navigation">
                    <button type="button" class="default-btn btn-back" onclick="showStep(1)">Back</button>
                    <button type="button" class="default-btn" onclick="goToStep3()">Next</button>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="form-step" id="step3">
                <h4 class="mb-3 step-title">Step 3: Assign Equipment</h4>

                <div class="equipment-list">
                    <?php 
                    $res = mysqli_query($con, "SELECT * FROM equipment WHERE status='Available'");
                    if ($res && mysqli_num_rows($res) > 0) {
                        while($row = mysqli_fetch_assoc($res)){ 
                    ?>
                    <div class="equipment-item">
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input"
                                   name="equipment[]" 
                                   value="<?php echo $row['equipment_id']; ?>">
                            <label class="form-check-label">
                                <strong><?php echo $row['equipment_name']." ".$row['model']; ?></strong>
                            </label>
                        </div>
                    </div>
                    <?php 
                        }
                    } else {
                    ?>
                    <p class="no-equipment">No available equipment.</p>
                    <?php } ?>
                </div>

                <div class="form-navigation">
                    <button type="button" class="default-btn btn-back" onclick="showStep(2)">Back</button>
                    <button type="submit" class="default-btn">
                        <i class="fas fa-plus"></i> Create Project
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
</form>


<!-- Edit Project Modal -->
<div id="editProjectModal" class="land-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow: auto;">
    <div class="land-modal-content" style="background: white; margin: 50px auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 600px;">
        <div class="land-modal-header" style="padding: 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">Manage Project</h3>
            <span class="land-modal-close" style="cursor:pointer; font-size: 24px;" onclick="closeModal('editProjectModal')">&times;</span>
        </div>
        <div class="land-modal-body" style="padding: 20px;" id="editProjectModalBody">
            <!-- Project form will be loaded here via JS -->
        </div>
    </div>
</div>


<!-- Details Project Modal -->
<div id="detailsProjectModal" class="land-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow: auto;">
    <div class="land-modal-content" style="background: white; margin: 50px auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 600px;">
        <div class="land-modal-header" style="padding: 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">Project Details</h3>
            <span class="land-modal-close" style="cursor:pointer; font-size: 24px;" onclick="closeModal('detailsProjectModal')">&times;</span>
        </div>
        <div class="land-modal-body" style="padding: 20px;" id="detailsProjectModalBody">
            <!-- Project details content will be loaded here via JS -->
        </div>
        <div class="land-modal-footer" style="padding: 20px; border-top: 1px solid #ddd; text-align: right;">
            <button class="btn btn-secondary" onclick="closeModal('detailsProjectModal')">Close</button>
        </div>
    </div>
</div>



<!-- Send Email Modal -->
 
<div id="sendEmailModal" class="land-modal" style="display: none;">
    <div class="land-modal-content" style="max-width: 500px;">
        <div class="land-modal-header">
            <h3>Send Project Completion Email</h3>
            <span class="land-modal-close" onclick="closeModal('sendEmailModal')">&times;</span>
        </div>
        <div class="land-modal-body">
            <p>Are you sure you want to notify the client?</p>
            <p><strong>Total Price:</strong> <span id="modalTotalPrice"></span></p>
            <p><strong>Client Email:</strong> <span id="modalClientEmail"></span></p>
        </div>
        <div class="land-modal-footer" style="padding: 20px; border-top: 1px solid #ddd; text-align: right;">
            <button class="btn btn-secondary" onclick="closeModal('sendEmailModal')" style="padding: 8px 16px; margin-right: 10px;">Cancel</button>
            <button class="btn btn-success" id="confirmSendEmailBtn" style="padding: 8px 16px;">Send Email</button>
        </div>
    </div>
</div>


<!-- Success Message Modal -->
<div id="successModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 350px;">
        <div class="land-modal-body text-center">
            <i class="fas fa-check-circle" style="font-size: 48px; color: #4caf50; margin-bottom: 15px;"></i>
            <h4>Success!</h4>
            <p style="font-size: 14px;">Project created successfully</p>
            <button type="button" class="dl-btn" style="background: #4caf50; padding: 8px 20px; margin-top: 15px;" onclick="closeModal('successModal')">OK</button>
        </div>
    </div>
</div>

<style>
.form-step {
    display: none;
}
.form-step.active {
    display: block;
}
.service-tag {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 15px;
    font-size: 11px;
    margin: 2px;
}
.equipment-tag {
    display: inline-block;
    background: #e8f5e8;
    color: #2e7d32;
    padding: 4px 8px;
    border-radius: 15px;
    font-size: 11px;
    margin: 2px;
}
.services-tags, .equipment-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
}
.remove-service, .remove-equipment {
    cursor: pointer;
    margin-left: 5px;
    font-size: 10px;
}
.equipment-item {
    padding: 8px;
    border: 1px solid #f0f0f0;
    border-radius: 5px;
    margin-bottom: 8px;
}
.equipment-item:hover {
    background: #f9f9f9;
}

.land-modal {
    display: none; /* hidden by default */
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
    position: relative;
    padding: 20px;
    max-width: 800px;
    width: 90%;
}
.land-modal-close {
    cursor: pointer;
    font-size: 24px;
    position: absolute;
    top: 15px;
    right: 20px;
}

/* Improved button spacing */
.form-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
    gap: 15px; /* Space between buttons */
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
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex: 1; /* Make buttons equal width */
    justify-content: center;
}

.default-btn:hover {
    background: #e66806;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 118, 7, 0.3);
}

.btn-back {
    background: #666 !important;
}

.btn-back:hover {
    background: #555 !important;
}

/* Form improvements */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #263a4f;
    margin-bottom: 8px;
    font-size: 14px;
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

/* Lead Engineer dropdown specific */
select[name="Lead_Engineer"] {
    background-color: white;
}

select[name="Lead_Engineer"] option[value=""] {
    color: #999;
    font-style: italic;
}

/* Step styling */
.step-title {
    color: #263a4f;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 15px;
    margin-bottom: 20px;
    font-size: 18px;
    font-weight: 600;
}

/* Request and equipment lists */
.approved-requests-list,
.equipment-list {
    border: 2px solid #e8e8e8;
    padding: 15px;
    max-height: 250px;
    overflow-y: auto;
    border-radius: 8px;
    background: #fafafa;
    margin-bottom: 15px;
}

.request-item {
    border: 1px solid #eee;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 8px;
    transition: all 0.2s;
    background: white;
}

.request-item:hover {
    background: #f9f9f9;
    border-color: #ddd;
}

.request-details {
    margin-left: 25px;
    margin-top: 5px;
}

.request-service {
    margin: 3px 0;
    font-size: 13px;
}

.request-date {
    font-size: 12px;
    margin: 3px 0;
    color: #666;
}

.no-requests,
.no-equipment {
    text-align: center;
    color: #999;
    padding: 20px;
    font-style: italic;
}

/* Responsive design */
@media (max-width: 768px) {
    .land-modal-content {
        margin: 20px auto;
        padding: 15px;
    }
    
    .form-navigation {
        flex-direction: column;
        gap: 10px;
    }
    
    .default-btn {
        width: 100%;
    }
}

/* ========================================
   UNIFIED TAB NAVIGATION STYLES
   Replace the tab styles in admin-project.php with these
======================================== */

/* Tab Navigation Container */
.tab-navigation {
    display: flex;
    gap: 10px;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 0;
    margin-bottom: 30px;
}

/* Tab Buttons */
.tab-btn {
    background: none;
    border: none;
    padding: 12px 24px;
    font-size: 15px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
}

.tab-btn:hover {
    color: #ff7607;
    background: #f9f9f9;
}

.tab-btn.active {
    color: #ff7607;
    border-bottom-color: #ff7607;
    background: #fff5ed;
}

/* Animated underline effect */
.tab-btn::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 3px;
    background: #ff7607;
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.tab-btn.active::after {
    transform: scaleX(1);
}

</style>
<?php 
// Only show modals if messages actually exist and have content
if ($successMessage !== false && !empty(trim($successMessage))) {
    echo '<script>
        setTimeout(function() {
            openModal("successModal");
        }, 500);
    </script>';
}

if ($errorMessage !== false && !empty(trim($errorMessage))) {
    echo '<script>
        setTimeout(function() {
            alert("' . addslashes($errorMessage) . '");
        }, 500);
    </script>';
}
?>

 

<?php include 'includes/footer.html'; ?>

 