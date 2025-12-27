<?php
// ========================================================================
// FILE: lead_eng-dashboard-get-equipment.php
// Display assigned equipment with Remove button (NO prepared statements)
// ========================================================================

include 'includes/connect.php'; 

if (!isset($_POST['project_id'])) {
    echo '<li style="text-align:center;color:#dc3545;padding:10px;">Invalid request</li>';
    exit;
}

$project_id = (int) $_POST['project_id']; // cast for safety

$sql = "
    SELECT 
        equipment.equipment_id,
        equipment.equipment_name,
        equipment.equipment_type,
        equipment.serial_number
    FROM equipment
    INNER JOIN uses_project_equipment 
        ON uses_project_equipment.equipment_id = equipment.equipment_id
    WHERE uses_project_equipment.project_id = $project_id
    and equipment.status='assigned'
";

$result = mysqli_query($con, $sql);

if (!$result) {
    echo '<li style="color:red;padding:10px;">Database error</li>';
    exit;
}

if (mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <li style="padding:10px;margin-bottom:8px;background:white;border-radius:4px;border:1px solid #ddd;list-style:none;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                
                <div style="flex:1;">
                    <strong style="font-size:13px;color:#263a4f;">
                        <?php echo htmlspecialchars($row['equipment_name']); ?>
                    </strong>
                    <span style="font-size:12px;color:#666;">
                        - <?php echo htmlspecialchars($row['equipment_type']); ?>
                    </span>

                    <?php if (!empty($row['serial_number'])): ?>
                        <span style="font-size:11px;color:#999;">
                            (SN: <?php echo htmlspecialchars($row['serial_number']); ?>)
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Remove button -->
                <button type="button"
                        class="btn btn-sm btn-danger"
                        style="padding:2px 8px;font-size:11px;"
                        onclick="removeEquipment(<?php echo $project_id; ?>, <?php echo $row['equipment_id']; ?>)">
                    <i class="fas fa-times"></i>
                </button>

            </div>
        </li>
        <?php
    }

} else {

    echo '
    <li style="text-align:center;color:#999;padding:20px;list-style:none;">
        <i class="fas fa-tools" style="font-size:24px;margin-bottom:10px;display:block;"></i>
        No equipment assigned yet. Click "Add Equipment" to assign resources.
    </li>';
}

mysqli_close($con);
?>
