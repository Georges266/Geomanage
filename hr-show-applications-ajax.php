<?php
// ========================================
// FILE: hr-show-applications-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['show'])){
    $user_id = $_SESSION['user_id'];
    $get_hr_id = "SELECT `hr_id` FROM `hr` WHERE user_id = '$user_id'";
    $result = mysqli_query($con, $get_hr_id);
    $row = mysqli_fetch_assoc($result);
    $hr_id = $row['hr_id'];
    
    $search = mysqli_real_escape_string($con, $_POST['search'] ?? '');
    $position = mysqli_real_escape_string($con, $_POST['position'] ?? '');
    $status = mysqli_real_escape_string($con, $_POST['status'] ?? '');

    $sql = "SELECT ja.*, jo.job_title, u.full_name
            FROM job_application AS ja
            JOIN job_opportunity AS jo ON ja.job_id = jo.job_id
            JOIN user AS u ON ja.user_id = u.user_id
            WHERE jo.hr_id = '$hr_id'";

    if (!empty($search)) {
        $sql .= " AND u.full_name LIKE '%$search%'";
    }

    if (!empty($position)) {
        $sql .= " AND jo.job_title = '$position'";
    }

    if (!empty($status)) {
        $sql .= " AND ja.status = '$status'";
    }

    $list_applications_result = mysqli_query($con, $sql);
    
    if(mysqli_num_rows($list_applications_result) > 0){
        while ($row = mysqli_fetch_assoc($list_applications_result)) {
            ?>
            <div class="col-lg-4 col-md-6 padding-10">
                <div class="service-item box-shadow" style="padding: 15px;">
                    <div class="service-content" style="padding: 0;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 style="font-size: 16px; margin: 0;"><?php echo htmlspecialchars($row['applicant_name']); ?></h4>
                            <span class="status-badge status-new"><?php echo htmlspecialchars($row['status']); ?></span>
                        </div>
                        <p style="font-size: 13px; margin: 5px 0;"><strong>For:</strong> <?php echo htmlspecialchars($row['job_title']); ?></p>
                        <p style="font-size: 12px; margin: 3px 0; color: #666;"><?php echo htmlspecialchars($row['application_date']); ?></p>
                        <div class="dl-btn-group mt-2" style="gap: 5px;">
                            <button class="dl-btn view" data-id="<?php echo (int)$row['application_id']; ?>"
                                style="padding: 5px 10px; font-size: 12px;">View</button>
                            <?php if ($row['status'] !== 'Rejected' && $row['status'] !== 'Hired'): ?>
                            <button class="dl-btn manage" data-id="<?php echo (int)$row['application_id']; ?>"
                                style="padding: 5px 10px; font-size: 12px; background: #4caf50;">Actions</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="col-12"><p style="text-align:center; padding:40px;">No applications found.</p></div>';
    }
}
?>
