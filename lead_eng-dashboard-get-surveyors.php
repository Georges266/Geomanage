<?php
// ========================================================================
// FILE: lead_eng-dashboard-get-surveyors.php
// Display assigned surveyors with Remove button
// ========================================================================
include 'includes/connect.php'; 
$project_id = intval($_POST['project_id']);

$sql = "
    SELECT s.surveyor_id, u.user_id, u.full_name, u.email
    FROM surveyor s
    JOIN user u ON s.user_id = u.user_id
    WHERE s.project_id = ?
    ORDER BY u.full_name ASC
";

$stmt = $con->prepare($sql);
$stmt->bind_param('i', $project_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        ?>
        <li style="padding: 8px; margin-bottom: 8px; background: white; border-radius: 4px; border-left: 3px solid #4caf50;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="font-size: 13px;"><?php echo htmlspecialchars($row['full_name']); ?></strong>
                    <span style="font-size: 12px; color: #666;"> - <?php echo htmlspecialchars($row['email']); ?></span>
                </div>
                <button type="button" class="btn btn-sm btn-danger" 
                    style="padding: 2px 8px; font-size: 11px;" 
                    onclick="removeSurveyor(<?php echo $project_id; ?>, <?php echo $row['surveyor_id']; ?>)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </li>
        <?php
    }
} else {
    echo '<li style="text-align: center; color: #999; padding: 20px;">No surveyors assigned to this project yet.</li>';
}
?>
