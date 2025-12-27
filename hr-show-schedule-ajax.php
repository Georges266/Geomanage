<?php
// ========================================
// FILE: hr-show-schedule-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['show'])){
    $user_id = $_SESSION['user_id'];
    $q = mysqli_query($con, "SELECT hr_id FROM hr WHERE user_id='$user_id'");
    $hr = mysqli_fetch_assoc($q);
    $hr_id = $hr['hr_id'];
    
    $today = date('Y-m-d');

    $schedule_sql = "
        SELECT s.*, u.full_name, u.email, jo.job_title, ja.application_id, ja.applicant_name
        FROM interview_schedule s
        JOIN job_application ja ON s.application_id = ja.application_id
        JOIN job_opportunity jo ON ja.job_id = jo.job_id
        JOIN user u ON ja.user_id = u.user_id
        WHERE s.hr_id = '$hr_id'
        AND s.interview_date >= '$today'
        AND s.status IN ('Scheduled','Rescheduled')
        ORDER BY s.interview_date ASC, s.interview_time ASC
    ";

    $schedule_result = mysqli_query($con, $schedule_sql);
    
    if (mysqli_num_rows($schedule_result) > 0) {
        echo '<div class="row">';
        
        while ($row = mysqli_fetch_assoc($schedule_result)) {
            ?>
            <div class="col-md-6 padding-10">
                <div class="service-item box-shadow" style="padding:12px;border-left:4px solid #2196F3;">

                    <strong><?php echo htmlspecialchars($row['applicant_name']); ?></strong><br>
                    <small><?php echo htmlspecialchars($row['job_title']); ?></small><br>

                    <small style="color:#2196F3;">
                        <?php echo date("l, M j, Y", strtotime($row['interview_date'])); ?>
                    </small><br>

                    <small><?php echo date("g:i A", strtotime($row['interview_time'])); ?></small><br>

                    <?php if (!empty($row['interview_location'])): ?>
                        <small><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['interview_location']); ?></small><br>
                    <?php endif; ?>

                    <button class="dl-btn reschedule" data-id="<?php echo (int)$row['application_id']; ?>"
                            style="padding:5px 10px;font-size:11px;">Reschedule</button>

                </div>
            </div>
            <?php
        }
        
        echo '</div>';
    } else {
        ?>
        <div style="text-align:center;padding:40px;background:#f9f9f9;border-radius:8px;">
            <p>No upcoming interviews.</p>
            <a href="hr-applications.php" class="dl-btn">Go to Applications</a>
        </div>
        <?php
    }
}
?>
