<?php
// ========================================
// FILE: hr-view-job-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['view'])){
    $jobId = intval($_POST['job_id']);
    $query = "SELECT * FROM job_opportunity WHERE job_id = '$jobId'";
    $result = mysqli_query($con, $query);
    $viewJob = mysqli_fetch_assoc($result);
    
    if($viewJob){
        ?>
        <div class="job-info mb-3" style="padding: 10px; background: #f9f9f9; border-radius: 5px;">
            <strong><?php echo htmlspecialchars($viewJob['job_title']); ?></strong><br>
            <small>Type: <?php echo htmlspecialchars($viewJob['job_type']); ?></small><br>
            <small>Status: <?php echo htmlspecialchars($viewJob['status']); ?></small><br>
            <small>Positions: <?php echo (int)$viewJob['number_of_positions']; ?></small>
        </div>

        <div class="form-group">
            <label>Description</label>
            <p style="white-space: pre-line;"><?php echo htmlspecialchars($viewJob['job_description']); ?></p>
        </div>

        <div class="form-group">
            <label>Responsibilities</label>
            <p style="white-space: pre-line;"><?php echo htmlspecialchars($viewJob['responsibilities']); ?></p>
        </div>

        <div class="form-group">
            <label>Requirements</label>
            <p style="white-space: pre-line;"><?php echo htmlspecialchars($viewJob['requirements']); ?></p>
        </div>

        <div class="form-group text-right">
            <button type="button" class="dl-btn" style="background:#666; padding:8px 15px;" onclick="closeModal('viewJobModal')">Close</button>
        </div>
        <?php
    }
}
?>
