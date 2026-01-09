<?php
include 'includes/connect.php';
include 'includes/header.php';

// Check if user is logged in and get their info
$is_logged_in = isset($_SESSION['user_id']);
$client_id = null;
$user = null;

if ($is_logged_in) {
    // Check if user is a client
    if (isset($_SESSION['role']) && $_SESSION['role'] !== "Client") {
        exit();
    }
    
    // Get client_id from user_id
    $user = $_SESSION['user_id'];
    $q1 = "SELECT client_id FROM client WHERE user_id = $user";
    $r1 = mysqli_query($con, $q1);
    
    if (mysqli_num_rows($r1) > 0) {
        $row_client = mysqli_fetch_assoc($r1);
        $client_id = $row_client['client_id'];
    }
}

// Fetch open job opportunities
$q = "SELECT * FROM job_opportunity WHERE status = 'Open'";
$r = mysqli_query($con, $q);

// Initialize messages
$success_message = '';
$error_message = '';

// Handle form submission - ONLY if logged in
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['first_name'])) {
    
    $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
    $applicant_name = $first_name . ' ' . $last_name;
    $applicant_email = mysqli_real_escape_string($con, $_POST['email']);
    $applicant_phone = mysqli_real_escape_string($con, $_POST['phone']);
    $cover_letter = mysqli_real_escape_string($con, $_POST['cover_message']);
    $job_id = mysqli_real_escape_string($con, $_POST['job_id']);
    
    // Check if file was uploaded
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === 0) {
        $upload_dir = "uploads/resumes/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $_FILES['resume']['name'];
        $file_tmp = $_FILES['resume']['tmp_name'];
        $file_size = $_FILES['resume']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'doc', 'docx'];
        
        if (!in_array($file_ext, $allowed_ext)) {
            $error_message = "Invalid file type. Please upload PDF, DOC, or DOCX files only.";
        } elseif ($file_size > 5 * 1024 * 1024) {
            $error_message = "File is too large. Maximum file size is 5MB.";
        } else {
            $unique_name = time() . "_" . uniqid() . "." . $file_ext;
            $cv_file_path = $upload_dir . $unique_name;
            
            if (move_uploaded_file($file_tmp, $cv_file_path)) {
                // File uploaded successfully, insert into database
                $sql = "INSERT INTO `job_application`
                        (`applicant_name`, `applicant_email`, `applicant_phone`, `cover_letter`, `cv_file_path`, `status`, `user_id`, `job_id`)
                        VALUES 
                        ('$applicant_name', '$applicant_email', '$applicant_phone', '$cover_letter', '$cv_file_path', 'Pending', '$user', '$job_id')";
                
                if (mysqli_query($con, $sql)) {
                    $success_message = "Your application has been submitted successfully! You can apply for other positions below.";
                } else {
                    $error_message = "Error submitting application: " . mysqli_error($con);
                    if (file_exists($cv_file_path)) {
                        unlink($cv_file_path);
                    }
                }
            } else {
                $error_message = "Failed to save file. Please check folder permissions.";
            }
        }
    } else {
        $error_message = "Please select a resume/CV file.";
    }
}
?>
<!doctype html>
<html class="no-js" lang="en"> 

<body>
<div class="site-preloader-wrap">
<div class="spinner"></div>
</div>

<!-- Page Header -->
<section class="page-header padding">
<div class="container">
<div class="page-content text-center">
<h2>Career Opportunities</h2>
<p>Join Our Team at Indico Construction</p>
</div>
</div>
</section>

<?php if ($success_message): ?>
<div class="container" style="margin-top: 20px;">
<div class="alert alert-success" style="padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; text-align: center;">
<i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
</div>
</div>
<?php endif; ?>

<?php if ($error_message): ?>
<div class="container" style="margin-top: 20px;">
<div class="alert alert-danger" style="padding: 15px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; text-align: center;">
<i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
</div>
</div>
<?php endif; ?>

<?php if (!$is_logged_in): ?>
<div class="container" style="margin-top: 20px;">
<div class="alert alert-info" style="padding: 15px; background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; border-radius: 5px; text-align: center;">
<i class="fas fa-info-circle"></i> To apply for positions, please <a href="login.php" style="color: #0c5460; font-weight: bold; text-decoration: underline;">log in</a> or <a href="signup.php" style="color: #0c5460; font-weight: bold; text-decoration: underline;">create an account</a>.
</div>
</div>
<?php endif; ?>

<!-- Current Openings Section -->
<section class="service-section padding bg-grey">
<div class="dots"></div>
<div class="container">
<div class="section-heading text-center mb-40">
<span>Open Positions</span>
<h2>Current Job Openings</h2>
<p>Click on any position to <?php echo $is_logged_in ? 'apply' : 'view details'; ?></p>
</div>
<div class="row">
<?php 
if (mysqli_num_rows($r) > 0) {
    while ($row = mysqli_fetch_assoc($r)) {
        $job_id = $row['job_id'];    
        $job_title = htmlspecialchars($row['job_title']);  
?>
<div class="col-lg-6 padding-15">
<div class="service-item box-shadow job-position" data-job="project-manager">
<div class="service-icon">
<i class="flaticon-worker"></i>
</div>
<h3><?php echo $job_title; ?></h3>
<p><?php echo htmlspecialchars($row['responsibilities']); ?>.</p>
<p><?php echo htmlspecialchars($row['requirements']); ?>.</p>
<button class="read-more apply-now-btn" data-job-id="<?php echo $job_id; ?>" data-job-title="<?php echo $job_title; ?>" data-logged-in="<?php echo $is_logged_in ? '1' : '0'; ?>">
    <?php echo $is_logged_in ? 'Apply Now' : 'Login to Apply'; ?>
</button>
</div>
</div>
<?php 
    }
} else {
    echo '<div class="col-12"><p style="text-align:center;font-size:18px;">No job openings available at the moment. Please check back later.</p></div>';
}
?>
</div>
</div>
</section>

<?php if ($is_logged_in): ?>
<div id="job-application-modal" class="land-modal">
<div class="land-modal-content" style="max-width: 700px;">
<span class="land-modal-close">&times;</span>
<div class="land-modal-body">
<div class="section-heading text-center mb-30">
<h2>Apply for: <span id="selected-job-title">Position</span></h2>
</div>
<form class="form-horizontal" method="POST" enctype="multipart/form-data" id="job-application-form">
<input type="hidden" id="applied-position" name="position">
<input type="hidden" id="applied-job-id" name="job_id">
<div class="form-group colum-row row">
<div class="col-sm-6">
<input type="text" name="first_name" class="form-control" placeholder="First Name" required>
</div>
<div class="col-sm-6">
<input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
</div>
</div>
<div class="form-group colum-row row">
<div class="col-md-6">
<input type="email" name="email" class="form-control" placeholder="Email Address" required>
</div>
<div class="col-md-6">
<input type="tel" name="phone" class="form-control" placeholder="Phone Number" required>
</div>
</div>
<div class="form-group colum-row row">
<div class="col-md-12">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 10px; display: block;">Upload Resume/CV (PDF, DOC, DOCX) *</label>
<input type="file" name="resume" id="resume-file" class="form-control" accept=".pdf,.doc,.docx" required style="padding: 10px;">
<small style="color: #6c757d; font-size: 12px;">Maximum file size: 5MB</small>
<small id="file-name" style="display: block; color: #28a745; margin-top: 5px;"></small>
</div>
</div>
<div class="form-group colum-row row">
<div class="col-md-12">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 10px; display: block;">Cover Letter / Message *</label>
<textarea name="cover_message" class="form-control" rows="4" placeholder="Tell us about your experience and why you want to join our team..." required></textarea>
</div>
</div>
<div class="form-group row">
<div class="col-md-12">
<div style="display: flex; align-items: center; margin-bottom: 25px;">
<input type="checkbox" id="privacy" style="margin-right: 8px;" required>
<label for="privacy" style="margin-bottom: 0; font-size: 14px;">I agree to the <a href="#" style="color: #ff7607;">Privacy Policy</a> and consent to my data being processed</label>
</div>
</div>
</div>
<div class="form-group row">
<div class="col-md-12">
<button type="submit" name="apply" class="default-btn" style="width: 100%;">
<i class="fas fa-paper-plane"></i> Submit Application
</button>
</div>
</div>
</form>
</div>
</div>
</div>
<?php endif; ?>

<script>
// Check if user is logged in
const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

// Modal functionality - only if logged in
<?php if ($is_logged_in): ?>
const modal = document.getElementById('job-application-modal');
const closeBtn = document.querySelector('.land-modal-close');

// Close modal
closeBtn.addEventListener('click', function() {
    modal.style.display = 'none';
});

window.addEventListener('click', function(event) {
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

// Show selected file name
document.getElementById('resume-file').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    const fileDisplay = document.getElementById('file-name');
    if (fileName) {
        fileDisplay.textContent = '✓ Selected: ' + fileName;
    } else {
        fileDisplay.textContent = '';
    }
});
<?php endif; ?>

// Apply button functionality
const applyButtons = document.querySelectorAll('.apply-now-btn');

applyButtons.forEach(button => {
    button.addEventListener('click', function() {
        const loggedIn = this.getAttribute('data-logged-in') === '1';
        
        if (!loggedIn) {
            // Redirect to login page
            window.location.href = 'login.php';
        } else {
            // Open application modal
            const jobId = this.getAttribute('data-job-id');
            const jobTitle = this.getAttribute('data-job-title');
            
            document.getElementById('selected-job-title').textContent = jobTitle;
            document.getElementById('applied-position').value = jobTitle;
            document.getElementById('applied-job-id').value = jobId;
            
            modal.style.display = 'block';
            document.getElementById('job-application-form').reset();
            document.getElementById('file-name').textContent = '';
        }
    });
});
</script>

<?php include 'includes/footer.html'; ?>
</body>
</html>