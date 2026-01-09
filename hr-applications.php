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

// ==========================================
// AJAX HANDLER FOR GETTING BOOKED TIMES
// ==========================================
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_booked_times') {
    header('Content-Type: application/json');
    
    try {
        $date = mysqli_real_escape_string($con, $_GET['date'] ?? '');
        $exclude_app_id = isset($_GET['exclude_app_id']) ? (int)$_GET['exclude_app_id'] : 0;
        
        if (empty($date)) {
            echo json_encode(['booked_times' => [], 'error' => 'No date provided']);
            exit();
        }
        
        $query = "SELECT interview_time 
                  FROM interview_schedule 
                  WHERE hr_id = '$hr_id' 
                  AND interview_date = '$date'
                  AND status IN ('Scheduled', 'Rescheduled')";
        
        if ($exclude_app_id > 0) {
            $query .= " AND application_id != '$exclude_app_id'";
        }
        
        $result = mysqli_query($con, $query);
        
        if (!$result) {
            echo json_encode(['booked_times' => [], 'error' => mysqli_error($con)]);
            exit();
        }
        
        $booked = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $booked[] = $row['interview_time'];
        }
        
        echo json_encode(['booked_times' => $booked, 'success' => true]);
        exit();
        
    } catch (Exception $e) {
        echo json_encode(['booked_times' => [], 'error' => $e->getMessage()]);
        exit();
    }
}

$roles = [];
$result = mysqli_query($con, "
    SELECT DISTINCT role 
    FROM user 
    WHERE role NOT IN ('Admin', 'Client')
    ORDER BY role ASC
");

while ($row = mysqli_fetch_assoc($result)) {
    $roles[] = $row['role'];
}
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
                    <h1>HR Recruitment - Applications</h1>
                    <p>Manage job applications and candidates</p>
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

            <!-- Search and Filter -->
            <div class="row mb-30">
                <div class="col-md-12">
                    <div class="service-item box-shadow padding-15">
                        <div class="row">
                            <div class="col-md-3 padding-10">
                                <input type="text" id="search" class="form-control" placeholder="Search applicants...">
                            </div>
                            <div class="col-md-3 padding-10">
                                <select id="position" class="form-control" style="height: 40px; padding: 8px 12px;">
                                    <option value="">All Positions</option>
                                    <option value="Land Surveyor">Land Surveyor</option>
                                    <option value="CAD Technician">CAD Technician</option>
                                    <option value="Junior Surveyor">Junior Surveyor</option>
                                </select>
                            </div>
                            <div class="col-md-3 padding-10">
                                <select id="status" class="form-control" style="height: 40px; padding: 8px 12px;">
                                    <option value="">All Status</option>
                                    <option value="New">New</option>
                                    <option value="Under Review">Under Review</option>
                                    <option value="Interview Scheduled">Interview Scheduled</option>
                                    <option value="Interview Rescheduled">Interview Rescheduled</option>
                                    <option value="Rejected">Rejected</option>
                                    <option value="Hired">Hired</option>
                                </select>
                            </div>
                            <div class="col-md-3 padding-10">
                                <button type="button" id="filterBtn" class="default-btn" style="width: 100%; padding: 10px;">Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applications List -->
            <div class="row" id="applicationsTable">
                <!-- Applications will be loaded here via AJAX -->
            </div>
        </div>
    </section>

    <!-- Application Action Modal -->
    <div id="actionModal" class="land-modal">
        <div class="land-modal-content" style="max-width: 450px;">
            <div class="land-modal-header">
                <h3>Manage application</h3>
                <span class="land-modal-close">&times;</span>
            </div>
            <div class="land-modal-body">
                <div id="applicationInfo" class="application-info mb-3" style="padding: 10px; background: #f9f9f9; border-radius: 5px;">
                    <!-- Info will be loaded here -->
                </div>

                <form id="actionForm">
                    <input type="hidden" id="action_application_id">
                    <input type="hidden" id="hiddenAppStatus">

                    <div class="form-group">
                        <label>Action</label>
                        <select class="form-control" id="actionSelect" required>
                            <option value="">Select action</option>
                        </select>
                        <small id="actionWarning" style="color: #f44336; display: none;"></small>
                    </div>

                    <!-- Hire Fields -->
                    <div id="hireFields" style="display:none;">
                        <h4 style="margin-top:15px;">New User Details</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name *</label>
                                    <input type="text" class="form-control" id="hire_full_name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" class="form-control" id="hire_email">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password *</label>
                                    <input type="password" class="form-control" id="hire_password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" class="form-control" id="hire_phone">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Role *</label>
                                    <select class="form-control" id="hire_role">
                                        <option value="">Select Role</option>
                                        <option value="HR">HR</option>
                                        <option value="LeadEngineer">Lead Engineer</option>
                                        <option value="Sales_Person">Sales Person</option>
                                        <option value="Maintenance_Technician">Maintenance Technician</option>
                                        <option value="Surveyor">Surveyor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Address</label>
                                    <input type="text" class="form-control" id="hire_address">
                                </div>
                            </div>
                        </div>

                        <!-- Role-Specific Fields -->
                        <div id="leadEngineerFields" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>License Number</label>
                                        <input type="text" class="form-control" id="hire_license_number" placeholder="Engineering license">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Years of Experience</label>
                                        <input type="number" class="form-control" id="hire_years_experience_eng" min="0" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="salesPersonFields" style="display:none;">
                            <div class="form-group">
                                <label>Commission Rate (%)</label>
                                <input type="number" class="form-control" id="hire_commission_rate" step="0.01" min="0" max="100" value="0">
                            </div>
                        </div>

                        <div id="surveyorFields" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Years of Experience</label>
                                        <input type="number" class="form-control" id="hire_years_experience_surv" min="0" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Role Description</label>
                                        <input type="text" class="form-control" id="hire_role_description" placeholder="e.g., General Surveyor">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h4 style="margin-top:20px; margin-bottom:15px; border-top: 1px solid #ddd; padding-top:15px;">Contract Details</h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Salary *</label>
                                    <input type="number" class="form-control" id="hire_salary" step="0.01" placeholder="Monthly salary">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Start Date *</label>
                                    <input type="date" class="form-control" id="hire_start_date" 
                                        style="height: 40px; padding: 8px 12px;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" class="form-control" id="hire_end_date" 
                                        style="height: 40px; padding: 8px 12px;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Signing Date *</label>
                                    <input type="date" class="form-control" id="hire_signing_date" 
                                        style="height: 40px; padding: 8px 12px;"
                                        value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Fields -->
                    <div id="scheduleFields" style="display: none;">
                        <div class="form-group">
                            <label>Interview Type *</label>
                            <select class="form-control" id="schedule_interview_type" style="height: 40px; padding: 8px 12px;">
                                <option value="In-person">In-person</option>
                                <option value="Video Call">Video Call</option>
                                <option value="Phone Call">Phone Call</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Interview Date *</label>
                            <input type="date" class="form-control" id="schedule_interview_date"
                                style="height: 40px; padding: 8px 12px;"
                                min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label>Interview Time *</label>
                            <select class="form-control" id="schedule_interview_time"
                                style="height: 40px; padding: 8px 12px;">
                                <option value="">Select a date first</option>
                            </select>
                            <small class="text-muted">Booked times are marked and disabled</small>
                        </div>

                        <div class="form-group">
                            <label>Location / Meeting Link</label>
                            <input type="text" class="form-control" id="schedule_interview_location"
                                placeholder="e.g., Office Room 5 or Zoom link"
                                style="height: 40px; padding: 8px 12px;">
                        </div>
                    </div>

                    <div class="form-group text-right" style="gap: 10px; display: flex; justify-content: flex-end;">
                        <button type="button" class="dl-btn" style="background: #f44336; padding: 8px 15px;" onclick="closeModal('actionModal')">Cancel</button>
                        <button type="button" class="dl-btn" id="submitAction" style="background: #4caf50; padding: 8px 15px;">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Application Modal -->
    <div id="viewApplicationModal" class="land-modal">
        <div class="land-modal-content" style="max-width: 600px;">
            <div class="land-modal-header">
                <h3>Application Details</h3>
                <span class="land-modal-close">&times;</span>
            </div>
            <div class="land-modal-body" id="viewApplicationContent">
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
        let selectedApplicationId = null;
        let currentApplicationData = null;

        // All available time slots
        const allTimeSlots = {
            "09:00:00": "9:00 AM", "09:30:00": "9:30 AM", "10:00:00": "10:00 AM",
            "10:30:00": "10:30 AM", "11:00:00": "11:00 AM", "11:30:00": "11:30 AM",
            "12:00:00": "12:00 PM", "12:30:00": "12:30 PM", "13:00:00": "1:00 PM",
            "13:30:00": "1:30 PM", "14:00:00": "2:00 PM", "14:30:00": "2:30 PM",
            "15:00:00": "3:00 PM", "15:30:00": "3:30 PM", "16:00:00": "4:00 PM",
            "16:30:00": "4:30 PM", "17:00:00": "5:00 PM"
        };

        $(document).ready(function(){
            showApplications();

            // Filter button
            $(document).on('click', '#filterBtn', function(){
                showApplications();
            });

            // View application
            $(document).on('click', '.view', function(){
                const appId = $(this).data('id');
                
                $.ajax({
                    type: "POST",
                    url: "hr-view-application-ajax.php",
                    data: { application_id: appId, view: 1 },
                    success: function(response){
                        $('#viewApplicationContent').html(response);
                        openModal('viewApplicationModal');
                    }
                });
            });

            // Manage application (open action modal)
            $(document).on('click', '.manage', function(){
                selectedApplicationId = $(this).data('id');
                
                $.ajax({
                    type: "POST",
                    url: "hr-get-application-ajax.php",
                    data: { application_id: selectedApplicationId, get: 1 },
                    dataType: 'json',
                    success: function(data){
                        currentApplicationData = data;
                        populateActionModal(data);
                        openModal('actionModal');
                    }
                });
            });

            // Submit action
            $(document).on('click', '#submitAction', function(){
                const action = $('#actionSelect').val();
                
                if (!action) {
                    alert('Please select an action');
                    return;
                }

                if (action === 'reject') {
                    if (!confirm('Are you sure you want to reject this application?')) {
                        return;
                    }
                }

                if (action === 'hire') {
                    handleHire();
                } else if (action === 'schedule' || action === 'reschedule') {
                    handleSchedule(action);
                } else if (action === 'reject') {
                    handleReject();
                }
            });

            // Role change for hire
            $(document).on('change', '#hire_role', function(){
                toggleRoleSpecificFields();
            });

            // Action change
            $(document).on('change', '#actionSelect', function(){
                const action = $(this).val();
                
                // Hide all fields first
                $('#scheduleFields').hide();
                $('#hireFields').hide();
                
                // Show relevant fields
                if (action === 'schedule' || action === 'reschedule') {
                    toggleScheduleFields();
                } else if (action === 'hire') {
                    toggleHireFields();
                }
            });

            // Date change for schedule
            $(document).on('change', '#schedule_interview_date', function(){
                updateAvailableTimes();
            });
        });

        function showApplications(){
            const search = $('#search').val();
            const position = $('#position').val();
            const status = $('#status').val();

            $.ajax({
                url: 'hr-show-applications-ajax.php',
                type: 'POST',
                data: { show: 1, search: search, position: position, status: status },
                success: function(response){
                    $('#applicationsTable').html(response);
                }
            });
        }

      function populateActionModal(data){
    $('#action_application_id').val(data.application_id);
    $('#hiddenAppStatus').val(data.status);
    
    let infoHtml = `
        <strong>${data.applicant_name}</strong><br>
        <small>Position: ${data.job_title}</small><br>
        <small>Status: <span id="currentStatus">${data.status}</span></small><br>
        <small>Applied on: ${data.application_date}</small>
    `;
    $('#applicationInfo').html(infoHtml);

    // Pre-fill hire fields with applicant data
    $('#hire_full_name').val(data.applicant_name || '');
    $('#hire_email').val(data.email || '');
    $('#hire_position').val(data.job_title || '');
    $('#hire_phone').val(data.phone || '');
    
    // Clear other hire fields
    $('#hire_password').val('');
    $('#hire_role').val('');
    $('#hire_address').val('');
    $('#hire_salary').val('');
    $('#hire_start_date').val('');
    $('#hire_end_date').val('');

    // Populate action options
    let actionOptions = '<option value="">Select action</option>';
    
    if (!data.is_scheduled && data.status !== 'Rejected' && data.status !== 'Hired' && 
        data.status !== 'Interview Scheduled' && data.status !== 'Interview Rescheduled') {
        actionOptions += '<option value="schedule">Schedule interview</option>';
    }
    
    if (data.is_scheduled) {
        actionOptions += '<option value="reschedule">Reschedule interview</option>';
    }
    
    if (data.status !== 'Rejected' && data.status !== 'Hired') {
        actionOptions += '<option value="hire">Hire candidate</option>';
        actionOptions += '<option value="reject">Reject application</option>';
    }
    
    $('#actionSelect').html(actionOptions);
    
    // Reset action select and hide all conditional fields
    $('#actionSelect').val('');
    $('#hireFields').hide();
    $('#scheduleFields').hide();
    $('#leadEngineerFields').hide();
    $('#salesPersonFields').hide();
    $('#surveyorFields').hide();
}

        function handleHire(){
            const requiredFields = {
                'hire_full_name': 'Full Name',
                'hire_email': 'Email',
                'hire_password': 'Password',
                'hire_role': 'Role',
                'hire_salary': 'Salary',
                'hire_start_date': 'Start Date',
                'hire_signing_date': 'Signing Date'
            };

            for (const [fieldId, label] of Object.entries(requiredFields)) {
                if (!$('#' + fieldId).val()) {
                    alert(`Please fill in: ${label}`);
                    return;
                }
            }

            const hireData = {
                application_id: $('#action_application_id').val(),
                action: 'hire',
                full_name: $('#hire_full_name').val(),
                email: $('#hire_email').val(),
                password: $('#hire_password').val(),
                phone: $('#hire_phone').val(),
                role: $('#hire_role').val(),
                address: $('#hire_address').val(),
                position: $('#hire_position').val(),
                salary: $('#hire_salary').val(),
                start_date: $('#hire_start_date').val(),
                end_date: $('#hire_end_date').val(),
                signing_date: $('#hire_signing_date').val(),
                hire: 1
            };

            // Add role-specific fields
            const role = $('#hire_role').val();
            if (role === 'LeadEngineer') {
                hireData.license_number = $('#hire_license_number').val();
                hireData.years_experience = $('#hire_years_experience_eng').val();
            } else if (role === 'Sales_Person') {
                hireData.commission_rate = $('#hire_commission_rate').val();
            } else if (role === 'Surveyor') {
                hireData.years_experience = $('#hire_years_experience_surv').val();
                hireData.role_description = $('#hire_role_description').val();
            }

            $.ajax({
                type: "POST",
                url: "hr-hire-ajax.php",
                data: hireData,
                success: function(response){
                    const result = JSON.parse(response);
                    if(result.status === 'error'){
                        alert(result.message);
                    } else {
                        closeModal('actionModal');
                        showSuccessMessage('Candidate Hired!', result.message);
                        showApplications();
                    }
                }
            });
        }

        function handleSchedule(action){
            if (!$('#schedule_interview_date').val() || !$('#schedule_interview_time').val()) {
                alert('Please fill in all required schedule fields');
                return;
            }

            $.ajax({
                type: "POST",
                url: "hr-schedule-action-ajax.php",
                data: {
                    application_id: $('#action_application_id').val(),
                    action: action,
                    interview_date: $('#schedule_interview_date').val(),
                    interview_time: $('#schedule_interview_time').val(),
                    interview_type: $('#schedule_interview_type').val(),
                    interview_location: $('#schedule_interview_location').val(),
                    schedule: 1
                },
                success: function(response){
                    const result = JSON.parse(response);
                    if(result.status === 'error'){
                        alert(result.message);
                    } else {
                        closeModal('actionModal');
                        showSuccessMessage('Interview ' + (action === 'schedule' ? 'Scheduled' : 'Rescheduled') + '!', result.message);
                        showApplications();
                    }
                }
            });
        }

        function handleReject(){
            $.ajax({
                type: "POST",
                url: "hr-reject-ajax.php",
                data: {
                    application_id: $('#action_application_id').val(),
                    reject: 1
                },
                success: function(response){
                    const result = JSON.parse(response);
                    if(result.status === 'error'){
                        alert(result.message);
                    } else {
                        closeModal('actionModal');
                        showSuccessMessage('Application Rejected', result.message);
                        showApplications();
                    }
                }
            });
        }

        // AJAX function to get booked times
        function updateAvailableTimes() {
            const dateInput = $('#schedule_interview_date');
            const timeSelect = $('#schedule_interview_time');
            const applicationId = $('#action_application_id').val();
            
            if (!dateInput.val()) {
                timeSelect.html('<option value="">Select a date first</option>');
                return;
            }
            
            timeSelect.html('<option value="">Loading available times...</option>');
            timeSelect.prop('disabled', true);
            
            const url = `hr-applications.php?ajax=get_booked_times&date=${encodeURIComponent(dateInput.val())}&exclude_app_id=${applicationId}`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Server error:', data.error);
                        timeSelect.html('<option value="">Error loading times</option>');
                        return;
                    }
                    
                    const bookedTimes = data.booked_times || [];
                    let optionsHTML = '<option value="">Select time</option>';
                    
                    for (const [value, label] of Object.entries(allTimeSlots)) {
                        const isBooked = bookedTimes.includes(value);
                        
                        if (isBooked) {
                            optionsHTML += `<option value="${value}" disabled style="color: #999;">${label} (Booked)</option>`;
                        } else {
                            optionsHTML += `<option value="${value}">${label}</option>`;
                        }
                    }
                    
                    timeSelect.html(optionsHTML);
                    timeSelect.prop('disabled', false);
                })
                .catch(error => {
                    console.error('Error fetching booked times:', error);
                    let optionsHTML = '<option value="">Select time</option>';
                    for (const [value, label] of Object.entries(allTimeSlots)) {
                        optionsHTML += `<option value="${value}">${label}</option>`;
                    }
                    timeSelect.html(optionsHTML);
                    timeSelect.prop('disabled', false);
                });
        }

        function toggleRoleSpecificFields() {
            const role = $('#hire_role').val();
            $('#leadEngineerFields, #salesPersonFields, #surveyorFields').hide();
            
            if (role === 'LeadEngineer') {
                $('#leadEngineerFields').show();
            } else if (role === 'Sales_Person') {
                $('#salesPersonFields').show();
            } else if (role === 'Surveyor') {
                $('#surveyorFields').show();
            }
        }

        function toggleScheduleFields() {
            const action = $('#actionSelect').val();
            if (action === 'schedule' || action === 'reschedule') {
                $('#scheduleFields').show();
                if ($('#schedule_interview_date').val()) {
                    updateAvailableTimes();
                }
            } else {
                $('#scheduleFields').hide();
            }
        }

       function toggleHireFields() {
    const action = $('#actionSelect').val();
    if (action === 'hire') {
        $('#hireFields').show();
        // Reset role-specific fields
        toggleRoleSpecificFields();
    } else {
        $('#hireFields').hide();
    }
}

        function showSuccessMessage(title, message){
            $('#successMessage').text(title);
            $('#successDetails').text(message);
            openModal('successModal');
        }

        function openModal(modalId) {
            $('#' + modalId).css('display', 'block');
        }

        function closeModal(modalId) {
            $('#' + modalId).css('display', 'none');
        }

        $(document).on('click', '.land-modal-close', function(){
            $(this).closest('.land-modal').css('display', 'none');
        });

        $(document).on('click', '.land-modal', function(e){
            if (e.target.classList.contains('land-modal')) {
                $(this).css('display', 'none');
            }
        });
    </script>

<?php include 'includes/footer.html'; ?>
</body>
</html>