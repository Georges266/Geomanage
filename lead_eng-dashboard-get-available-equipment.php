<?php
// ========================================================================
// FILE: lead_eng-dashboard-get-available-equipment.php
// Display available equipment as individual cards with Add button
// ========================================================================
include 'includes/connect.php';

// Get all available equipment (case-insensitive check for both 'available' and 'Available')
$sql = "
    SELECT equipment_id, equipment_name, equipment_type, serial_number
    FROM equipment
    WHERE LOWER(status) = 'available' 
    ORDER BY equipment_name ASC
";

$result = $con->query($sql);

if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        ?>
        <div style="padding: 8px; margin-bottom: 8px; background: white; border-radius: 4px; border: 1px solid #ddd;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="flex: 1;">
                    <strong style="font-size: 13px;"><?php echo htmlspecialchars($row['equipment_name']); ?></strong>
                    <span style="font-size: 12px; color: #666;"> - <?php echo htmlspecialchars($row['equipment_type']); ?></span>
                    <?php if(!empty($row['serial_number'])): ?>
                        <span style="font-size: 11px; color: #999;"> (SN: <?php echo htmlspecialchars($row['serial_number']); ?>)</span>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-sm  btn-primary" 
                    style="padding: 2px 8px; font-size: 11px;" 
                    onclick="addSingleEquipment(<?php echo $row['equipment_id']; ?>)">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
        </div>
        <?php
    }
} else {
    echo '<p style="text-align: center; color: #999; padding: 10px; margin: 0;">No available equipment.</p>';
}
?>