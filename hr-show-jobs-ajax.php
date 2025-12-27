<?php
// ========================================
// FILE: hr-show-jobs-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['show'])){
    $show_oportunity = "SELECT * FROM `job_opportunity`";
    $show_oportunity_result = mysqli_query($con, $show_oportunity);
    
    while ($row = mysqli_fetch_assoc($show_oportunity_result)) {
        $job_id = $row['job_id'];
        $countQuery = "SELECT COUNT(*) AS apps FROM job_application WHERE job_id = '$job_id'";
        $countResult = mysqli_query($con, $countQuery);
        $appCount = 0;
        if ($countResult) {
            $countRow = mysqli_fetch_assoc($countResult);
            $appCount = $countRow['apps'];
        }
        ?>
        <div class="col-lg-4 col-md-6 padding-10">
            <div class="service-item box-shadow" style="padding: 15px;">
                <div class="service-content" style="padding: 0;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 style="font-size: 16px; margin: 0;">
                            <?php echo htmlspecialchars($row['job_title']); ?>
                        </h4>
                        <span class="status-badge status-active">
                            <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                        </span>
                    </div>
                    <p style="font-size: 13px; margin: 5px 0;">
                        <strong>Hires Needed:</strong> <?php echo (int)$row['number_of_positions']; ?>
                    </p>
                    <p style="font-size: 12px; margin: 3px 0; color: #666;">
                        Applications: <?php echo $appCount; ?>
                    </p>
                    <div class="dl-btn-group mt-2" style="gap: 5px;">
                        <button class="dl-btn view" data-id="<?php echo (int)$row['job_id']; ?>"
                            style="padding: 5px 10px; font-size: 12px;">View</button>
                        <button class="dl-btn edit" data-id="<?php echo (int)$row['job_id']; ?>"
                            style="background:#4caf50; padding: 5px 10px; font-size: 12px;">Edit</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
?>