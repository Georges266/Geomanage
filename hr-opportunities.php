<?php
include 'includes/header.php';
include 'includes/connect.php';

// Check authorization
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "HR") {
    exit();
}

// Get HR ID
$user_id = $_SESSION['user_id'];
$get_hr_id = "SELECT `hr_id` FROM `hr` WHERE user_id = '$user_id'";
$result = mysqli_query($con, $get_hr_id);
$row = mysqli_fetch_assoc($result);
$hr_id = $row['hr_id'];
?>

<!doctype html>
<html class="no-js" lang="en">

<body>
    <div class="site-preloader-wrap">
        <div class="spinner"></div>
    </div>

    <!-- Page Header -->
    <section class="page-header padding bg-grey">
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-lg-8">
                    <h1>HR Recruitment - Opportunities</h1>
                    <p>Manage job opportunities and postings</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tab Navigation -->
    <section class="padding-30">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="tab-navigation mb-40">
                    </div>
                </div>
            </div>

            <!-- Create Job Button -->
            <div class="row mb-30">
                <div class="col-md-12 text-right">
                    <a href="#" class="default-btn" onclick="event.preventDefault(); openModal('createJobModal')">
                        <i class="fas fa-plus"></i> Create New Job
                    </a>
                </div>
            </div>

            <!-- Opportunities List -->
            <div class="row" id="jobsTable">
                <!-- Jobs will be loaded here via AJAX -->
            </div>
        </div>
    </section>

    <!-- ==========================================
     MODALS SECTION
     ========================================== -->

    <!-- Create Job Modal -->
    <div id="createJobModal" class="land-modal">
        <div class="land-modal-content" style="max-width: 500px;">
            <div class="land-modal-header">
                <h3>Create New Job Opportunity</h3>
                <span class="land-modal-close">&times;</span>
            </div>
            <div class="land-modal-body">
                <form id="createJobForm">
                    <div class="form-group">
                        <label>Job Title</label>
                        <input name="job_title" id="job_title" type="text" class="form-control" placeholder="e.g., Senior Land Surveyor" required>
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <select name="department" id="department" class="form-control" required>
                            <option value="">Select Department</option>
                            <option value="field">Field Operations</option>
                            <option value="technical">Technical</option>
                            <option value="management">Management</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Number of Hires Needed</label>
                        <input name="number_of_positions" id="number_of_positions" type="number" class="form-control" min="1" value="1" required>
                    </div>

                    <div class="form-group">
                        <label>Job Type</label>
                        <select name="job_type" id="job_type" class="form-control" required>
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Job Description</label>
                        <textarea name="job_description" id="job_description" class="form-control" rows="3" placeholder="Enter job description..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Responsibilities</label>
                        <textarea name="responsibilities" id="responsibilities" class="form-control" rows="3" placeholder="Enter responsibilities..." required></textarea>
                    </div>

                    <div class="form-group text-right" style="gap: 10px; display: flex; justify-content: flex-end;">
                        <button type="button" class="dl-btn" style="background: #f44336; padding: 8px 15px;" onclick="closeModal('createJobModal')">Cancel</button>
                        <button type="button" class="dl-btn" style="background: #4caf50; padding: 8px 15px;" id="addnew">Create Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Job Modal -->
    <div id="editJobModal" class="land-modal">
        <div class="land-modal-content" style="max-width: 500px;">
            <div class="land-modal-header">
                <h3>Edit Job Opportunity</h3>
                <span class="land-modal-close">&times;</span>
            </div>
            <div class="land-modal-body">
                <form id="editJobForm">
                    <input type="hidden" id="edit_job_id">

                    <div class="form-group">
                        <label>Job Title</label>
                        <input type="text" class="form-control" id="edit_job_title" required>
                    </div>

                    <div class="form-group">
                        <label>Number of Hires Needed</label>
                        <input type="number" class="form-control" id="edit_number_of_positions" min="1" required>
                    </div>

                    <div class="form-group">
                        <label>Job Type</label>
                        <select class="form-control" id="edit_job_type">
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" id="edit_status">
                            <option value="open">Open</option>
                            <option value="closed">Closed (keep applications)</option>
                            <option value="closed_reject">Closed (reject all non-hired)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Job Description</label>
                        <textarea class="form-control" id="edit_job_description" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Responsibilities</label>
                        <textarea class="form-control" id="edit_responsibilities" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Requirements</label>
                        <textarea class="form-control" id="edit_requirements" rows="3"></textarea>
                    </div>

                    <div class="form-group text-right" style="gap: 10px; display: flex; justify-content: flex-end;">
                        <button type="button" class="dl-btn" style="background:#666; padding:8px 15px;" onclick="closeModal('editJobModal')">Cancel</button>
                        <button type="button" class="dl-btn" style="background:#4caf50; padding:8px 15px;" id="updateJob">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Job Modal -->
    <div id="viewJobModal" class="land-modal">
        <div class="land-modal-content" style="max-width: 600px;">
            <div class="land-modal-header">
                <h3>Job Opportunity Details</h3>
                <span class="land-modal-close">&times;</span>
            </div>
            <div class="land-modal-body" id="viewJobContent">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>

    <!-- Success Message Modal -->
    <div id="successModal" class="land-modal">
        <div class="land-modal-content" style="max-width: 350px;">
            <div class="land-modal-body text-center">
                <i class="fas fa-check-circle" style="font-size: 48px; color: #4caf50; margin-bottom: 15px;"></i>
                <h4 id="successMessage">Success!</h4>
                <p style="font-size: 14px;" id="successDetails">Action completed successfully</p>
                <button type="button" class="dl-btn" style="background: #4caf50; padding: 8px 20px; margin-top: 15px;" onclick="closeModal('successModal')">OK</button>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        let selectedJobId = null;

        $(document).ready(function(){
            showJobs();

            // Add New Job
            $(document).on('click', '#addnew', function(){
                if ($('#job_title').val()=="" || $('#number_of_positions').val()=="" || $('#job_description').val()=="" || $('#responsibilities').val()==""){
                    alert('Please fill all required fields');
                }
                else{
                    $.ajax({
                        type: "POST",
                        url: "hr-add-job-ajax.php",
                        data: {
                            job_title: $('#job_title').val(),
                            number_of_positions: $('#number_of_positions').val(),
                            job_type: $('#job_type').val(),
                            job_description: $('#job_description').val(),
                            responsibilities: $('#responsibilities').val(),
                            department: $('#department').val(),
                            add: 1,
                        },
                        success: function(response){
                            $('#createJobForm')[0].reset();
                            closeModal('createJobModal');
                            showSuccessMessage('Job Created!', 'Job opportunity created successfully');
                            showJobs();
                        }
                    });
                }
            });

            // Edit Job - Load data into modal
            $(document).on('click', '.edit', function(){
                selectedJobId = $(this).data('id');
                
                $.ajax({
                    type: "POST",
                    url: "hr-get-job-ajax.php",
                    data: {
                        job_id: selectedJobId,
                        get: 1
                    },
                    dataType: 'json',
                    success: function(data){
                        $('#edit_job_id').val(data.job_id);
                        $('#edit_job_title').val(data.job_title);
                        $('#edit_number_of_positions').val(data.number_of_positions);
                        $('#edit_job_type').val(data.job_type);
                        $('#edit_status').val(data.status);
                        $('#edit_job_description').val(data.job_description);
                        $('#edit_responsibilities').val(data.responsibilities);
                        $('#edit_requirements').val(data.requirements);
                        openModal('editJobModal');
                    }
                });
            });

            // Update Job
            $(document).on('click', '#updateJob', function(){
                if ($('#edit_job_title').val()=="" || $('#edit_number_of_positions').val()==""){
                    alert('Please fill all required fields');
                }
                else{
                    $.ajax({
                        type: "POST",
                        url: "hr-update-job-ajax.php",
                        data: {
                            job_id: $('#edit_job_id').val(),
                            job_title: $('#edit_job_title').val(),
                            number_of_positions: $('#edit_number_of_positions').val(),
                            job_type: $('#edit_job_type').val(),
                            status: $('#edit_status').val(),
                            job_description: $('#edit_job_description').val(),
                            responsibilities: $('#edit_responsibilities').val(),
                            requirements: $('#edit_requirements').val(),
                            edit: 1,
                        },
                        success: function(){
                            closeModal('editJobModal');
                            showSuccessMessage('Job Updated!', 'Job opportunity updated successfully');
                            showJobs();
                        }
                    });
                }
            });

            // View Job
            $(document).on('click', '.view', function(){
                const jobId = $(this).data('id');
                
                $.ajax({
                    type: "POST",
                    url: "hr-view-job-ajax.php",
                    data: {
                        job_id: jobId,
                        view: 1
                    },
                    success: function(response){
                        $('#viewJobContent').html(response);
                        openModal('viewJobModal');
                    }
                });
            });
        });

        // Show all jobs
        function showJobs(){
            $.ajax({
                url: 'hr-show-jobs-ajax.php',
                type: 'POST',
                async: false,
                data:{
                    show: 1
                },
                success: function(response){
                    $('#jobsTable').html(response);
                }
            });
        }

        // Show success message
        function showSuccessMessage(title, message){
            $('#successMessage').text(title);
            $('#successDetails').text(message);
            openModal('successModal');
        }

        // Modal functions 
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'block';
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Close modal when clicking the X or outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('land-modal-close')) {
                e.target.closest('.land-modal').style.display = 'none';
            }
            if (e.target.classList.contains('land-modal')) {
                e.target.style.display = 'none';
            }
        });
    </script>

<?php include 'includes/footer.html'; ?>
</body>
</html>