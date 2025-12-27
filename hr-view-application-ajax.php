<?php
// ========================================
// FILE: hr-view-application-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['view'])){
    $appId = intval($_POST['application_id']);
    
    $query = "SELECT ja.*, jo.job_title, u.full_name
              FROM job_application AS ja
              JOIN job_opportunity AS jo ON ja.job_id = jo.job_id
              JOIN user AS u ON ja.user_id = u.user_id
              WHERE ja.application_id = '$appId'";
    
    $result = mysqli_query($con, $query);
    $viewApplication = mysqli_fetch_assoc($result);
    
    if($viewApplication){
        ?>
        <div class="application-info mb-3" style="padding: 10px; background: #f9f9f9; border-radius: 5px;">
            <strong><?php echo htmlspecialchars($viewApplication['applicant_name']); ?></strong><br>
            <small>Applied for: <?php echo htmlspecialchars($viewApplication['job_title']); ?></small><br>
            <small>Status: <?php echo htmlspecialchars($viewApplication['status']); ?></small><br>
            <small>Date: <?php echo htmlspecialchars($viewApplication['application_date']); ?></small><br>
            <small>Email: <?php echo htmlspecialchars($viewApplication['applicant_email']); ?></small>
        </div>

        <div class="form-group">
            <label>Resume</label><br>
            <?php if (!empty($viewApplication['cv_file_path'])): ?>
                <a href="<?php echo 'uploads/resumes/' . basename($viewApplication['cv_file_path']); ?>"
                    class="dl-btn" style="background:#4caf50; padding:8px 15px;" download>
                    Download Resume
                </a>
            <?php else: ?>
                <p>No resume uploaded.</p>
            <?php endif; ?>
        </div>

        <div class="form-group text-right">
            <button type="button" class="dl-btn" style="background:#666; padding:8px 15px;" onclick="closeModal('viewApplicationModal')">Close</button>
        </div>
        <?php
    }
}
?>