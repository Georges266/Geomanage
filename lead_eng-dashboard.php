<?php
include 'includes/header.php'; 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "LeadEngineer") {
    exit();
}
include 'includes/connect.php';

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
$result=mysqli_query($con,$query);
while ($row = mysqli_fetch_assoc($result)) {
    $id=$row['lead_engineer_id'];
    $name=$row['full_name'];
}

// Get total projects
$query = "SELECT COUNT(*) AS total_projects 
        FROM project
        WHERE lead_engineer_id = $lead_engineer_id";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_projects = $row['total_projects'];
?>
<?php
// Get active projects
$query = "SELECT COUNT(*) AS total_projects_active 
          FROM project
          WHERE project.status = 'active' AND lead_engineer_id = $lead_engineer_id";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_projects_active = $row['total_projects_active'];

?>



<!doctype html>
<html class="no-js" lang="en">
<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                <h1>Project Management</h1>
                <p>Manage your assigned projects, teams, and deliverables</p>
            </div>
            <div class="col-lg-4 text-right">
                <div class="engineer-info">
                    <strong>Lead Engineer: <?php echo $name ?></strong><br>
                    <small>Assigned Projects: <?php echo $total_projects_active ?></small>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Get completed projects
$query = "SELECT COUNT(*) AS total_projects_active 
          FROM project
          WHERE project.status = 'completed' AND lead_engineer_id = $lead_engineer_id";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$total_projects_completed = $row['total_projects_active'];

?>

<!-- Tab Navigation -->
<section class="padding-top" style="padding-top: 40px;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="tab-navigation mb-40">
                    <button class="tab-btn active" data-status="active">Active Projects </button>
                    <button class="tab-btn" data-status="completed">Completed Projects </button>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="row mb-30">
            <div class="col-md-12">
                <div class="service-item box-shadow" style="padding: 15px;">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" id="projectNameSearch" class="form-control" placeholder="Search by project name...">
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="clientNameSearch" class="form-control" placeholder="Search by client name...">
                        </div>
                        <div class="col-md-2">
                            <button class="default-btn" id="searchBtn" style="width: 100%; padding: 10px;">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button class="default-btn" id="clearBtn" style="width: 100%; padding: 10px; background: #666;">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Project Sections -->
<section class="padding-bottom">
    <div class="container">
        <!-- Projects will be loaded here via AJAX -->
        <div id="projectsContainer"></div>
    </div>
</section>



<!-- Update Status Modal -->
<div id="updateStatusModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 650px;">

        <div class="land-modal-header enhanced-header">
            <h3><i class="fas fa-edit"></i> Update Project</h3>
            <span class="land-modal-close" onclick="closeModal('updateStatusModal')">&times;</span>
        </div>

        <div class="land-modal-body">
            <form id="updateStatusForm">
                <input type="hidden" id="update_project_id" name="project_id">

                <!-- Project Info Card -->
                <div class="modal-info-card">
                    <label><i class="fas fa-project-diagram"></i> Project</label>
                    <input type="text" id="update_project_name" class="form-control" readonly>
                </div>

                <!-- Status & Progress Section -->
                <div class="modal-section">
                    <h6 class="modal-section-title">
                        <i class="fas fa-chart-line"></i> Status & Progress
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" id="update_status" name="status">
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Progress</label>
                                <input type="range"
                                       class="form-control-range"
                                       id="update_progress"
                                       name="progress"
                                       min="0" max="100"
                                       oninput="document.getElementById('progressValue').textContent=this.value+'%'">
                                <div class="progress-value">
                                    <strong id="progressValue">0%</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <!-- Equipment Section -->
                <div class="modal-section">
                    <div class="modal-section-header">
                        <h6 class="modal-section-title">
                            <i class="fas fa-tools"></i> Assigned Equipment
                        </h6>
                        <button type="button"
                                class="btn btn-sm btn-primary"
                                onclick="toggleAddEquipment()"
                                style="padding: 4px 12px; font-size: 12px;">
                            <i class="fas fa-plus"></i> Request Equipment
                        </button>
                    </div>

                    <!-- Add Equipment Section -->
                    <div id="addEquipmentSection" class="add-section">
                        <h6 class="add-section-title">
                            <i class="fas fa-toolbox"></i> Available Equipment
                        </h6>
                        <div id="availableEquipmentList" class="available-list">
                            <!-- AJAX -->
                        </div>
                        <button type="button"
                                class="btn btn-sm btn-secondary mt-2"
                                onclick="toggleAddEquipment()"
                                style="padding: 4px 12px; font-size: 12px;">
                            Close
                        </button>
                    </div>

                    <!-- Assigned Equipment -->
                    <ul id="assignedEquipment" class="assigned-list">
                        <!-- AJAX -->
                    </ul>
                </div>

                <!-- Buttons -->
                <div class="modal-footer-buttons">
                    <button type="button" class="btn-modal-secondary"
                            onclick="closeModal('updateStatusModal')">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-modal-primary">
                        <i class="fas fa-check"></i> Update Project
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>



<!-- Manage Team Members Modal -->
<div id="teamModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 700px;">
        <div class="land-modal-header">
            <h3>Manage Project Surveyors</h3>
            <span class="land-modal-close" onclick="closeModal('teamModal')">&times;</span>
        </div>
        <div class="land-modal-body">
            <input type="hidden" id="team_project_id">

            <!-- Assigned Surveyors Section -->
            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <label style="font-weight: 600; margin: 0;">
                        <i class="fas fa-users"></i> Assigned Surveyors
                    </label>
                    <button type="button" class="btn btn-sm btn-primary" onclick="toggleAddSurveyors()" 
                        style="padding: 4px 12px; font-size: 12px;">
                        <i class="fas fa-plus"></i> Add Surveyors
                    </button>
                </div>

                <!-- Add Surveyors Section (Hidden by default) -->
                <div id="addSurveyorsSection" style="display: none; margin-bottom: 15px; border: 2px solid #007bff; 
                    border-radius: 5px; padding: 10px; background: #f0f8ff;">
                    <h6 style="margin-bottom: 10px; color: #007bff;">
                        <i class="fas fa-user-plus"></i> Available Surveyors
                    </h6>
                    
                    <!-- Available Surveyors List -->
                    <div id="availableSurveyorsList" style="max-height: 250px; overflow-y: auto;">
                        <!-- Populated via AJAX -->
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="toggleAddSurveyors()" 
                        style="padding: 4px 12px; font-size: 12px;">
                        Close
                    </button>
                </div>

                <!-- Assigned Surveyors List -->
                <div style="border: 1px solid #ddd; border-radius: 5px; padding: 10px; max-height: 300px; 
                    overflow-y: auto; background: #f9f9f9;">
                    <ul id="assignedSurveyors" style="list-style: none; padding: 0; margin: 0;">
                        <!-- Populated via AJAX -->
                    </ul>
                </div>
            </div>
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
<div id="sendEmailModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 500px;">
        <div class="land-modal-header">
            <h3><i class="fas fa-envelope"></i> Send Completion Email</h3>
            <span class="land-modal-close" onclick="closeModal('sendEmailModal')">&times;</span>
        </div>
        <div class="land-modal-body">
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <p style="margin: 5px 0; font-size: 14px;">
                    <strong>Client Email:</strong> <span id="modalClientEmail"></span>
                </p>
                <p style="margin: 5px 0; font-size: 14px;">
                    <strong>Total Price:</strong> <span id="modalTotalPrice"></span>
                </p>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                <p style="margin: 0; font-size: 13px; color: #856404;">
                    <i class="fas fa-info-circle"></i> This will send a professional completion email to the client and mark the project as "Done".
                </p>
            </div>
        </div>
        <div class="land-modal-footer" style="padding: 20px; border-top: 1px solid #ddd; text-align: right;">
            <button class="btn btn-secondary" onclick="closeModal('sendEmailModal')">Cancel</button>
            <button class="btn btn-success" id="confirmSendEmailBtn">
                <i class="fas fa-paper-plane"></i> Send Email
            </button>
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
/* ========================================
   EXISTING PROJECT STYLES (Preserved)
======================================== */
.service-tag {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 15px;
    font-size: 11px;
    margin: 2px;
}

.services-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 15px;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-planning { background: #fff3cd; color: #856404; }
.status-in-progress { background: #cce7ff; color: #004085; }
.status-on-hold { background: #ffeaa7; color: #856404; }
.status-completed { background: #d4edda; color: #155724; }
.status-active { background: #cce7ff; color: #004085; }

.project-section {
    border-radius: 8px;
    transition: transform 0.2s ease;
}

.project-section:hover {
    transform: translateY(-2px);
}

.project-section h3,
.project-section h5,
.project-section h6,
.project-section p,
.project-section strong {
    color: #263a4f !important;
}

.project-section .service-item {
    background: #fff !important;
}

.land-info-card {
    background: #f8f9fa !important;
    border-radius: 5px;
    padding: 12px;
    margin-bottom: 10px;
    border-left: 3px solid #4caf50;
}

.land-info-card h6 {
    margin-bottom: 8px;
    color: #263a4f !important;
    font-size: 14px;
    font-weight: 600;
}

.land-info-card p {
    font-size: 12px;
    margin-bottom: 3px;
    color: #555 !important;
}

.land-info-card strong {
    color: #263a4f !important;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.collapsible-section {
    margin-top: 15px;
    border-top: 1px solid #e0e0e0;
    padding-top: 15px;
}

.collapsible-section .service-item {
    background: #f8f9fa !important;
}

.collapsible-section h5,
.collapsible-section h6 {
    color: #263a4f !important;
    font-weight: 600;
}

.collapsible-section p,
.collapsible-section strong {
    color: #555 !important;
}

.project-info h6 {
    color: #263a4f !important;
    font-weight: 600;
    margin-bottom: 10px;
}

.project-info p {
    color: #555 !important;
}

.close-btn {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    font-size: 16px;
}

.close-btn:hover {
    color: #333;
}

.services-section h5, 
.land-info-section h5 {
    color: #263a4f !important;
    margin-bottom: 10px;
    font-size: 16px;
    font-weight: 600;
}

.deliverable-item {
    background: #fff !important;
}

.deliverable-item strong,
.deliverable-item p {
    color: #263a4f !important;
}

.existing-deliverables h6 {
    color: #263a4f !important;
    font-weight: 600;
    margin-bottom: 15px;
}

/* Tab Navigation Styles */
.tab-navigation {
    display: flex;
    gap: 10px;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 0;
    margin-bottom: 30px;
}

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

/* ========================================
   BASE MODAL STYLES (Updated)
======================================== */
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
    padding: 20px;
    max-width: 800px;
    width: 90%;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

/* Base Modal Header - Simple version for all modals except update modal */
.land-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 15px;
}

.land-modal-header h3 {
    color: #263a4f !important;
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}

.land-modal-close {
    cursor: pointer;
    font-size: 24px;
    color: #666;
}

.land-modal-close:hover {
    color: #333;
}

/* ========================================
   ENHANCED UPDATE MODAL STYLES
======================================== */

/* Enhanced Header - Only for update modal */
.land-modal-header.enhanced-header {
    background: linear-gradient(135deg, #ff7607 0%, #ff9d42 100%);
    color: white;
    padding: 20px 25px;
    border-radius: 8px 8px 0 0;
    margin: -20px -20px 20px -20px;
    border-bottom: none;
}

.land-modal-header.enhanced-header h3 {
    color: white !important;
}

.land-modal-header.enhanced-header .land-modal-close {
    color: white;
    opacity: 0.9;
    transition: opacity 0.2s;
}

.land-modal-header.enhanced-header .land-modal-close:hover {
    opacity: 1;
    color: white;
}

/* Modal Info Card */
.modal-info-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 20px;
}

.modal-info-card label {
    display: block;
    font-weight: 600;
    color: #263a4f;
    margin-bottom: 8px;
    font-size: 13px;
}

.modal-info-card .form-control {
    background: white;
    border: 1px solid #d0d0d0;
}

/* Modal Sections */
.modal-section {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
}

.modal-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.modal-section-title {
    color: #263a4f !important;
    font-size: 14px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-section-title i {
    color: #ff7607;
}

/* Progress Value */
.progress-value {
    text-align: center;
    margin-top: 8px;
    padding: 5px;
    background: #f0f0f0;
    border-radius: 4px;
}

.progress-value strong {
    color: #ff7607 !important;
    font-size: 16px;
}

/* Modal Lists */
.modal-list {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 200px;
    overflow-y: auto;
    background: #f9f9f9;
    border-radius: 4px;
    padding: 8px;
}

/* Add Section */
.add-section {
    display: none;
    margin-bottom: 15px;
    border: 2px solid #28a745;
    border-radius: 6px;
    padding: 12px;
    background: #f4fff6;
}

.add-section-title {
    color: #28a745 !important;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.available-list {
    max-height: 250px;
    overflow-y: auto;
    background: white;
    border-radius: 4px;
    padding: 5px;
}

/* Assigned List */
.assigned-list {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 300px;
    overflow-y: auto;
    background: #f9f9f9;
    border-radius: 4px;
    padding: 8px;
}

/* Footer Buttons */
.modal-footer-buttons {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
}

.btn-modal-secondary {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-modal-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.btn-modal-primary {
    background: linear-gradient(135deg, #ff7607 0%, #ff9d42 100%);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 5px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-modal-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 118, 7, 0.4);
}

/* Form Controls Enhancement */
.modal-section .form-control,
.modal-section select.form-control {
    border: 1px solid #d0d0d0;
    border-radius: 4px;
    padding: 8px 12px;
    font-size: 13px;
    transition: border-color 0.2s;
}

.modal-section .form-control:focus,
.modal-section select.form-control:focus {
    border-color: #ff7607;
    box-shadow: 0 0 0 0.2rem rgba(255, 118, 7, 0.25);
    outline: none;
}

.modal-section label {
    font-size: 13px;
    font-weight: 500;
    color: #263a4f;
    margin-bottom: 5px;
}

/* Scrollbar Styling */
.modal-list::-webkit-scrollbar,
.available-list::-webkit-scrollbar,
.assigned-list::-webkit-scrollbar {
    width: 6px;
}

.modal-list::-webkit-scrollbar-track,
.available-list::-webkit-scrollbar-track,
.assigned-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.modal-list::-webkit-scrollbar-thumb,
.available-list::-webkit-scrollbar-thumb,
.assigned-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.modal-list::-webkit-scrollbar-thumb:hover,
.available-list::-webkit-scrollbar-thumb:hover,
.assigned-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Add Equipment Section - Blue Theme */
#addEquipmentSection {
    display: none;
    margin-bottom: 15px;
    border: 2px solid #007bff;
    border-radius: 6px;
    padding: 12px;
    background: #e3f2fd;
}

#addEquipmentSection .add-section-title {
    color: #007bff !important;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Add Surveyors Section - Keep existing green/different color if needed */
#addSurveyorsSection {
    display: none;
    margin-bottom: 15px;
    border: 2px solid #007bff;
    border-radius: 5px;
    padding: 10px;
    background: #f0f8ff;
}

/* Responsive */
@media (max-width: 768px) {
    .land-modal-content {
        margin: 20px;
        width: calc(100% - 40px);
    }
    
    .modal-footer-buttons {
        flex-direction: column;
    }
    
    .btn-modal-secondary,
    .btn-modal-primary {
        width: 100%;
        justify-content: center;
    }
}
</style>


<script>
/* ========================================
   PROJECT LOADING & SEARCH
======================================== */

// Load projects via AJAX
function loadProjects(projectName = '', clientName = '', status = null) {
    if (status === null) {
        status = $('.tab-btn.active').data('status') || 'active';
    }
    
    $.ajax({
        url: 'lead_eng-dashboard-show-ajax.php',
        type: 'POST',
        data: { 
            projectName: projectName,
            clientName: clientName,
            status: status
        },
        success: function(response) {
            $('#projectsContainer').html(response);
        },
        error: function(xhr, status, error) {
            $('#projectsContainer').html(
                '<div class="col-12"><p class="text-center text-danger p-3">Error loading projects.</p></div>'
            );
        }
    });
}

// Search functionality
$('#searchBtn').on('click', function() {
    const projectName = $('#projectNameSearch').val();
    const clientName = $('#clientNameSearch').val();
    const status = $('.tab-btn.active').data('status');
    loadProjects(projectName, clientName, status);
});

// Clear search
$('#clearBtn').on('click', function() {
    $('#projectNameSearch').val('');
    $('#clientNameSearch').val('');
    const status = $('.tab-btn.active').data('status');
    loadProjects('', '', status);
});

// Allow search on Enter key
$('#projectNameSearch, #clientNameSearch').on('keypress', function(e) {
    if (e.which === 13) {
        $('#searchBtn').click();
    }
});

/* ========================================
   PAGE INITIALIZATION
======================================== */

$(document).ready(function() {
    console.log('Lead Engineer Dashboard initialized...');
    // Load active projects by default
    loadProjects('', '', 'active');
});

/* ========================================
   TAB NAVIGATION
======================================== */

$(document).on('click', '.tab-btn', function() {
    $('.tab-btn').removeClass('active');
    $(this).addClass('active');
    const status = $(this).data('status');
    const projectName = $('#projectNameSearch').val();
    const clientName = $('#clientNameSearch').val();
    loadProjects(projectName, clientName, status);
});

/* ========================================
   MODAL FUNCTIONS
======================================== */

// Generic modal opener
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

// Generic modal closer
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
$(document).on('click', '.land-modal', function(e) {
    if ($(e.target).hasClass('land-modal')) {
        closeModal($(this).attr('id'));
    }
});

/* ========================================
   UPDATE STATUS MODAL
======================================== */

// Open Update Status Modal
function openUpdateStatusModal(projectId, projectName, currentStatus, progress) {
    // Set basic fields
    $('#update_project_id').val(projectId);
    $('#update_project_name').val(projectName);
    $('#update_status').val(currentStatus);
    $('#update_progress').val(progress);
    $('#progressValue').text(progress + '%');

   

    // Load assigned equipment
    $.post('lead_eng-dashboard-get-equipment.php', { project_id: projectId }, function(data) {
        $('#assignedEquipment').html(data);
    }).fail(function() {
        $('#assignedEquipment').html('<li style="text-align: center; color: #dc3545; padding: 10px;">Error loading equipment</li>');
    });

    // Load available equipment
    $.get('lead_eng-dashboard-get-available-equipment.php', function(data) {
        $('#availableEquipmentList').html(data);
    }).fail(function() {
        $('#availableEquipmentList').html('<p style="text-align: center; color: #dc3545; padding: 10px;">Error loading available equipment</p>');
    });

    // Reset toggle state
    $('#addEquipmentSection').hide();

    openModal('updateStatusModal');
}

// Handle status update form submission
$(document).on('submit', '#updateStatusForm', function(e) {
    e.preventDefault();
    
    // Disable submit button to prevent double submission
    const submitBtn = $(this).find('button[type="submit"]');
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    
    $.ajax({
        url: 'lead_eng-dashboard-update-query.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            closeModal('updateStatusModal');
            
            // Show success message
            $('#successMessage').text('Success!');
            $('#successDetails').text('Project status updated successfully');
            openModal('successModal');
            
            // Reload projects
            const projectName = $('#projectNameSearch').val();
            const clientName = $('#clientNameSearch').val();
            const status = $('.tab-btn.active').data('status');
            loadProjects(projectName, clientName, status);
        },
        error: function(xhr, status, error) {
            alert('Error updating project status. Please try again.');
        },
        complete: function() {
            // Re-enable submit button
            submitBtn.prop('disabled', false).html('<i class="fas fa-check"></i> Update Project');
        }
    });
});

/* ========================================
   EQUIPMENT MANAGEMENT
======================================== */

// Toggle add equipment section
function toggleAddEquipment() {
    $('#addEquipmentSection').slideToggle(300);
}

// Reload equipment lists
function reloadEquipmentModal(projectId) {
    $.post('lead_eng-dashboard-get-equipment.php', { project_id: projectId }, function(data) {
        $('#assignedEquipment').html(data);
    });
    
    $.get('lead_eng-dashboard-get-available-equipment.php', function(data) {
        $('#availableEquipmentList').html(data);
    });
}

// Add single equipment to project
function addSingleEquipment(equipmentId) {
    const projectId = $('#update_project_id').val();
    
    $.post('lead_eng-dashboard-request-equipment-query.php', { 
        project_id: projectId, 
        equipment_id: equipmentId
    }, function(response) {
        const trimmedResponse = response.trim();
        
        console.log('Equipment request response:', trimmedResponse); // Debug log
        
        if (trimmedResponse === 'success') {
            $('#successMessage').text('Equipment Requested!');
            $('#successDetails').text('Equipment has been requested successfully');
            openModal('successModal');
            reloadEquipmentModal(projectId);
        } else if (trimmedResponse === 'already assigned') {
            alert('This equipment is already assigned to this project');
        } else if (trimmedResponse === 'equipment not available') {
            alert('This equipment is currently not available');
        } else if (trimmedResponse === 'unauthorized') {
            alert('You do not have permission to modify this project');
        } else {
            alert('Error: ' + trimmedResponse);
        }
    }).fail(function(xhr, status, error) {
        console.error('AJAX Error:', error);
        alert('Error connecting to server. Please try again.');
    });
}

// Remove equipment from project
function removeEquipment(projectId, equipmentId) {
    if (!confirm('Remove this equipment from the project?')) return;
    
    $.post('admin-project-remove-equipment.php', { 
        project_id: projectId, 
        equipment_id: equipmentId 
    }, function(response) {
        if (response.trim() === 'success') {
            reloadEquipmentModal(projectId);
        } else {
            alert('Error removing equipment');
        }
    }).fail(function() {
        alert('Error removing equipment. Please try again.');
    });
}

/* ========================================
   TEAM MODAL (SURVEYORS)
======================================== */

// Toggle add surveyors section
function toggleAddSurveyors() {
    $('#addSurveyorsSection').slideToggle(300);
}

// Open Team Modal
function openTeamModal(projectId) {
    $('#team_project_id').val(projectId);
    
    // Load assigned surveyors
    $.post('lead_eng-dashboard-get-surveyors.php', { project_id: projectId }, function(data) {
        $('#assignedSurveyors').html(data);
    }).fail(function() {
        $('#assignedSurveyors').html('<li style="text-align: center; color: #dc3545; padding: 20px;">Error loading surveyors</li>');
    });

    // Load available surveyors
    $.get('lead_eng-dashboard-get-available-surveyors.php', function(data) {
        $('#availableSurveyorsList').html(data);
    }).fail(function() {
        $('#availableSurveyorsList').html('<p style="text-align: center; color: #dc3545; padding: 10px;">Error loading available surveyors</p>');
    });

    // Reset toggle state
    $('#addSurveyorsSection').hide();

    openModal('teamModal');
}

// Reload team modal after changes
function reloadTeamModal(projectId) {
    $.post('lead_eng-dashboard-get-surveyors.php', { project_id: projectId }, function(data) {
        $('#assignedSurveyors').html(data);
    });
    
    $.get('lead_eng-dashboard-get-available-surveyors.php', function(data) {
        $('#availableSurveyorsList').html(data);
    });
}

// Add single surveyor to project
function addSingleSurveyor(surveyorId) {
    const projectId = $('#team_project_id').val();
    
    $.post('lead_eng-dashboard-add-surveyor-query.php', { 
        project_id: projectId, 
        surveyor_id: surveyorId
    }, function(response) {
        if (response.trim() === 'success') {
            reloadTeamModal(projectId);
        } else {
            alert('Error adding surveyor to project');
        }
    }).fail(function() {
        alert('Error adding surveyor. Please try again.');
    });
}

// Remove surveyor from project
function removeSurveyor(projectId, surveyorId) {
    if (!confirm('Remove this surveyor from the project?')) return;
    
    $.post('lead_eng-dashboard-remove-surveyor-query.php', { 
        project_id: projectId, 
        surveyor_id: surveyorId 
    }, function(response) {
        if (response.trim() === 'success') {
            reloadTeamModal(projectId);
        } else {
            alert('Error removing surveyor');
        }
    }).fail(function() {
        alert('Error removing surveyor. Please try again.');
    });
}

/* ========================================
   PROJECT DETAILS MODAL
======================================== */

// Open details project modal
$(document).on('click', '.detailsProjectBtn', function() {
    const projectId = $(this).data('id');

    // Ensure modal is appended to body
    if ($('#detailsProjectModal').parent()[0].tagName !== 'BODY') {
        $('#detailsProjectModal').appendTo('body');
    }

    // Show modal and loading state
    $('#detailsProjectModal').fadeIn(300);
    $('body').css('overflow', 'hidden');
    $('#detailsProjectModalBody').html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</p>');

    // Fetch project details
    $.post('admin-project-fetch-projectDetails-info.php', { project_id: projectId }, function(data) {
        $('#detailsProjectModalBody').html(data);
    }).fail(function() {
        $('#detailsProjectModalBody').html('<p class="text-danger text-center">Error loading project information.</p>');
    });
});

/* ========================================
   DELIVERABLES MANAGEMENT
======================================== */

// Open PDF deliverable
$(document).on('click', '.showPDFBtn', function() {
    const projectId = $(this).data('id');
    
    $.ajax({
        url: 'admin-project-get_pdf_path.php',
        type: 'POST',
        data: { id: projectId },
        success: function(response) {
            if (response.startsWith('ERROR:')) {
                alert(response);
            } else if (response) {
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

// Handle file upload for deliverables
$(document).on('change', '[id^="fileInput_"]', function() {
    const projectId = this.id.replace('fileInput_', '');
    const file = this.files[0];
    
    if (!file) return;
    
    // Validate file type (optional)
    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!allowedTypes.includes(file.type)) {
        alert('Please upload a PDF or Word document');
        $(this).val('');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('project_id', projectId);
    
    const statusDiv = $('#uploadStatus_' + projectId);
    statusDiv.html('<span style="color: #ff7607;"><i class="fas fa-spinner fa-spin"></i> Uploading...</span>');
    
    $.ajax({
        url: 'lead_eng-dashboard-upload-deliverable.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.trim() === 'success') {
                statusDiv.html('<span style="color: #4caf50;"><i class="fas fa-check-circle"></i> File uploaded successfully!</span>');
                
                // Reload projects to show new deliverable
                setTimeout(function() {
                    const projectName = $('#projectNameSearch').val();
                    const clientName = $('#clientNameSearch').val();
                    const status = $('.tab-btn.active').data('status');
                    loadProjects(projectName, clientName, status);
                }, 1000);
            } else {
                statusDiv.html('<span style="color: #dc3545;"><i class="fas fa-exclamation-circle"></i> Error: ' + response + '</span>');
            }
        },
        error: function() {
            statusDiv.html('<span style="color: #dc3545;"><i class="fas fa-times-circle"></i> Upload failed</span>');
        }
    });
});

// Handle remove deliverable
$(document).on('click', '.removeDeliverableBtn', function() {
    if (!confirm('Are you sure you want to remove this deliverable? This action cannot be undone.')) {
        return;
    }
    
    const deliverableId = $(this).data('deliverable-id');
    const projectId = $(this).data('project-id');
    
    $.ajax({
        url: 'lead_eng-dashboard-remove-deliverable.php',
        type: 'POST',
        data: { deliverable_id: deliverableId },
        success: function(response) {
            if (response.trim() === 'success') {
                // Show success message
                $('#successMessage').text('Deliverable Removed');
                $('#successDetails').text('The deliverable has been removed successfully');
                openModal('successModal');
                
                // Reload projects
                const projectName = $('#projectNameSearch').val();
                const clientName = $('#clientNameSearch').val();
                const status = $('.tab-btn.active').data('status');
                loadProjects(projectName, clientName, status);
            } else {
                alert('Error: ' + response);
            }
        },
        error: function() {
            alert('Failed to remove deliverable. Please try again.');
        }
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



/* ========================================
   UTILITY FUNCTIONS
======================================== */

// Toggle collapsible sections
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        $(section).slideToggle(300);
    }
}

// Show success notification (alternative to modal)
function showSuccessNotification(message) {
    const notification = $('<div>')
        .addClass('success-notification')
        .html('<i class="fas fa-check-circle"></i> ' + message)
        .css({
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: '#4caf50',
            color: 'white',
            padding: '15px 25px',
            borderRadius: '5px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            zIndex: 10000,
            display: 'none'
        });
    
    $('body').append(notification);
    notification.fadeIn(300).delay(3000).fadeOut(300, function() {
        $(this).remove();
    });
}

// Show error notification
function showErrorNotification(message) {
    const notification = $('<div>')
        .addClass('error-notification')
        .html('<i class="fas fa-exclamation-circle"></i> ' + message)
        .css({
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: '#dc3545',
            color: 'white',
            padding: '15px 25px',
            borderRadius: '5px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            zIndex: 10000,
            display: 'none'
        });
    
    $('body').append(notification);
    notification.fadeIn(300).delay(3000).fadeOut(300, function() {
        $(this).remove();
    });
}

/* ========================================
   KEYBOARD SHORTCUTS
======================================== */

$(document).on('keydown', function(e) {
    // ESC key closes modals
    if (e.key === 'Escape') {
        $('.land-modal:visible').each(function() {
            closeModal($(this).attr('id'));
        });
    }
});

/* ========================================
   CONSOLE LOG FOR DEBUGGING
======================================== */

console.log('%c Lead Engineer Dashboard Loaded Successfully! ', 
    'background: #ff7607; color: white; font-size: 14px; padding: 5px 10px; border-radius: 3px;');

</script>


<?php include 'includes/footer.html'; ?>
