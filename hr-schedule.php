<?php
include 'includes/header.php';
include 'includes/connect.php';

// CHECK LOGIN
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "HR") {
    exit();
}

// GET HR ID
$user_id = $_SESSION['user_id'];
$q = mysqli_query($con, "SELECT hr_id FROM hr WHERE user_id='$user_id'");
$hr = mysqli_fetch_assoc($q);
$hr_id = $hr['hr_id'];
?>

<!doctype html>
<html class="no-js" lang="en">

<body>

<!-- HEADER -->
<section class="page-header padding bg-grey">
    <div class="container">
        <h1>HR Recruitment - Schedule</h1>
        <p>Upcoming Interviews</p>
    </div>
</section>

<section class="padding-30">
<div class="container">

    <!-- Upcoming Interviews -->
    <div class="service-item box-shadow padding-15">
        <h4>Upcoming Interviews</h4>

        <div id="scheduleTable">
            <!-- Interviews will be loaded here via AJAX -->
        </div>

    </div>

</div>
</section>

<!-- =============================
     RESCHEDULE MODAL
============================= -->
<div id="rescheduleModal" class="land-modal">
<div class="land-modal-content" style="max-width:450px;">

    <div class="land-modal-header">
        <h3>Reschedule Interview</h3>
        <span class="land-modal-close">&times;</span>
    </div>

    <div class="land-modal-body">

        <div id="applicantInfo" style="padding:10px;background:#f9f9f9;border-radius:5px;margin-bottom:15px;">
            <!-- Applicant info will be loaded here -->
        </div>

        <form id="rescheduleForm">
            <input type="hidden" id="reschedule_application_id">

            <div class="form-group">
                <label>New Date *</label>
                <input type="date" id="reschedule_date" class="form-control"
                       style="height: 40px; padding: 8px 12px;"
                       min="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label>New Time *</label>
                <select id="reschedule_time" class="form-control" 
                        style="height: 40px; padding: 8px 12px;" required>
                    <option value="">Select time</option>
                    <option value="09:00:00">9:00 AM</option>
                    <option value="09:30:00">9:30 AM</option>
                    <option value="10:00:00">10:00 AM</option>
                    <option value="10:30:00">10:30 AM</option>
                    <option value="11:00:00">11:00 AM</option>
                    <option value="11:30:00">11:30 AM</option>
                    <option value="12:00:00">12:00 PM</option>
                    <option value="12:30:00">12:30 PM</option>
                    <option value="13:00:00">1:00 PM</option>
                    <option value="13:30:00">1:30 PM</option>
                    <option value="14:00:00">2:00 PM</option>
                    <option value="14:30:00">2:30 PM</option>
                    <option value="15:00:00">3:00 PM</option>
                    <option value="15:30:00">3:30 PM</option>
                    <option value="16:00:00">4:00 PM</option>
                    <option value="16:30:00">4:30 PM</option>
                    <option value="17:00:00">5:00 PM</option>
                </select>
            </div>

            <div class="form-group">
                <label>Interview Type *</label>
                <select id="reschedule_type" class="form-control" 
                        style="height: 40px; padding: 8px 12px;" required>
                    <option value="In-person">In-person</option>
                    <option value="Video Call">Video Call</option>
                    <option value="Phone Call">Phone Call</option>
                </select>
            </div>

            <div class="form-group">
                <label>Location / Meeting Link</label>
                <input type="text" id="reschedule_location" class="form-control"
                       style="height: 40px; padding: 8px 12px;"
                       placeholder="e.g., Office Room 5 or Zoom link">
            </div>

            <div class="form-group text-right" style="gap: 10px; display: flex; justify-content: flex-end; margin-top:20px;">
                <button type="button" class="dl-btn" style="background:#f44336; padding:8px 15px;" onclick="closeModal('rescheduleModal')">Cancel</button>
                <button type="button" class="dl-btn" style="background:#4caf50; padding:8px 15px;" id="updateSchedule">Save Changes</button>
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
            <h4 id="successMessage">Success!</h4>
            <p style="font-size: 14px;" id="successDetails">Action completed successfully</p>
            <button type="button" class="dl-btn" style="background: #4caf50; padding: 8px 20px; margin-top: 15px;" onclick="closeModal('successModal')">OK</button>
        </div>
    </div>
</div>

<script type="text/javascript">
    let selectedApplicationId = null;

    $(document).ready(function(){
        showSchedule();

        // Load interview data for rescheduling
        $(document).on('click', '.reschedule', function(){
            selectedApplicationId = $(this).data('id');
            
            $.ajax({
                type: "POST",
                url: "hr-get-schedule-ajax.php",
                data: {
                    application_id: selectedApplicationId,
                    get: 1
                },
                dataType: 'json',
                success: function(data){
                    // Populate applicant info
                    let infoHtml = `
                        <strong>${data.applicant_name}</strong><br>
                        <small>Position: ${data.job_title}</small><br>
                        <small>Current: ${data.formatted_date} at ${data.formatted_time}</small>
                    `;
                    $('#applicantInfo').html(infoHtml);

                    // Populate form fields
                    $('#reschedule_application_id').val(data.application_id);
                    $('#reschedule_date').val(data.interview_date);
                    $('#reschedule_time').val(data.interview_time);
                    $('#reschedule_type').val(data.interview_type);
                    $('#reschedule_location').val(data.interview_location);

                    openModal('rescheduleModal');
                }
            });
        });

        // Update schedule
        $(document).on('click', '#updateSchedule', function(){
            if ($('#reschedule_date').val()=="" || $('#reschedule_time').val()=="" || $('#reschedule_type').val()==""){
                alert('Please fill all required fields');
            }
            else{
                $.ajax({
                    type: "POST",
                    url: "hr-update-schedule-ajax.php",
                    data: {
                        application_id: $('#reschedule_application_id').val(),
                        interview_date: $('#reschedule_date').val(),
                        interview_time: $('#reschedule_time').val(),
                        interview_type: $('#reschedule_type').val(),
                        interview_location: $('#reschedule_location').val(),
                        update: 1,
                    },
                    success: function(response){
                        const result = JSON.parse(response);
                        
                        if(result.status === 'error'){
                            alert(result.message);
                        } else {
                            closeModal('rescheduleModal');
                            showSuccessMessage('Interview Rescheduled!', result.message);
                            showSchedule();
                        }
                    }
                });
            }
        });
    });

    // Show all upcoming interviews
    function showSchedule(){
        $.ajax({
            url: 'hr-show-schedule-ajax.php',
            type: 'POST',
            async: false,
            data:{
                show: 1
            },
            success: function(response){
                $('#scheduleTable').html(response);
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