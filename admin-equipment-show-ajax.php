<?php
include 'includes/connect.php';

// Get status filter
$status = isset($_POST['status']) ? mysqli_real_escape_string($con, $_POST['status']) : 'all';

// Build query - JOIN with project and lead engineer to show who requested the equipment
$query = "SELECT 
    equipment.*,
    upe.project_id,
    project.project_name,
    lead_user.full_name AS lead_engineer_name
FROM equipment
LEFT JOIN uses_project_equipment upe ON equipment.equipment_id = upe.equipment_id
LEFT JOIN project ON upe.project_id = project.project_id
LEFT JOIN lead_engineer ON project.lead_engineer_id = lead_engineer.lead_engineer_id
LEFT JOIN user AS lead_user ON lead_engineer.user_id = lead_user.user_id";

if ($status !== 'all') {
    $query .= " WHERE equipment.status = '$status'";
}

$query .= " GROUP BY equipment.equipment_id
            ORDER BY equipment.equipment_id DESC";

$result = mysqli_query($con, $query);

if (!$result) {
    echo "<div class='col-12 text-danger'>Query failed: " . mysqli_error($con) . "</div>";
    mysqli_close($con);
    exit;
}

if (mysqli_num_rows($result) == 0) {
    echo "<div class='col-12'><p class='text-center text-muted my-3'>No equipment found.</p></div>";
} else {
    echo '<div class="row">';

    while ($row = mysqli_fetch_assoc($result)) {
        // Extract data
        $id            = (int)$row['equipment_id'];
        $name          = htmlspecialchars($row['equipment_name']);
        $type          = htmlspecialchars($row['equipment_type']);
        $serial        = htmlspecialchars($row['serial_number']);
        $model         = htmlspecialchars($row['model']);
        $cost          = htmlspecialchars($row['cost']);
        $status        = htmlspecialchars($row['status']);
        $project_id    = $row['project_id'];
        $project_name  = $row['project_name'] ? htmlspecialchars($row['project_name']) : null;
        $lead_name     = $row['lead_engineer_name'] ? htmlspecialchars($row['lead_engineer_name']) : null;

        $maintenance   = $row['maintenance_date']
            ? date("M d, Y", strtotime($row['maintenance_date']))
            : 'N/A';

        $statusClass = 'status-' . strtolower(str_replace(' ', '-', $status));
?>
        <div class="col-lg-4 col-md-6 padding-10">
            <div class="service-item box-shadow" style="padding: 15px;">
                <div class="service-content" style="padding: 0;">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 style="font-size: 16px; margin: 0;"><?php echo $name; ?></h4>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo $status; ?>
                        </span>
                    </div>

                    <p style="font-size: 13px; margin: 5px 0;">
                        <strong>Type:</strong> <?php echo $type; ?>
                    </p>

                    <p style="font-size: 12px; margin: 3px 0; color: #666;">
                        <strong>Model:</strong> <?php echo $model; ?>
                    </p>

                    <p style="font-size: 12px; margin: 3px 0; color: #666;">
                        <strong>Serial:</strong> <?php echo $serial; ?>
                    </p>

                    <p style="font-size: 12px; margin: 3px 0; color: #666;">
                        <strong>Cost:</strong> $<?php echo number_format($cost, 2); ?>
                    </p>

                    <p style="font-size: 12px; margin: 3px 0; color: #666;">
                        <strong>Last Maintenance:</strong> <?php echo $maintenance; ?>
                    </p>

                    <?php if ($project_id && $lead_name): ?>
                        <div style="background: #fff3cd; padding: 8px; border-radius: 5px; margin-top: 10px;">
                            <p style="font-size: 12px; margin: 2px 0; color: #856404;">
                                <strong>Assigned to Project:</strong> <?php echo $project_name; ?>
                            </p>
                            <p style="font-size: 12px; margin: 2px 0; color: #856404;">
                                <strong>Lead Engineer:</strong> <?php echo $lead_name; ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <div class="dl-btn-group mt-2" style="display: flex; gap: 5px; flex-wrap: wrap;">
                        <button 
                            class="dl-btn viewEquipmentDetailsBtn"
                            data-id="<?php echo $id; ?>"
                            style="padding: 5px 10px; font-size: 12px; background: #ff9800; border: none; cursor: pointer;">
                            Details
                        </button>

                        <?php if ($status === 'requested' || $status === 'Requested'): ?>
                            <button 
                                class="dl-btn unassignEquipmentBtn"
                                data-equipment-id="<?php echo $id; ?>"
                                data-project-id="<?php echo $project_id; ?>"
                                style="padding: 5px 10px; font-size: 12px; background: #dc3545; border: none; cursor: pointer;">
                                Reject
                            </button>
                            <button 
                                class="dl-btn approveRequestBtn"
                                data-equipment-id="<?php echo $id; ?>"
                                data-project-id="<?php echo $project_id; ?>"
                                data-equipment-name="<?php echo $name; ?>"
                                data-project-name="<?php echo $project_name; ?>"
                                data-lead-name="<?php echo $lead_name; ?>"
                                style="padding: 5px 10px; font-size: 12px; background: #28a745; border: none; cursor: pointer;">
                                <i class="fas fa-check"></i> Approve
                            </button>
                           
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
<?php
    }

    echo '</div>';
}

mysqli_close($con);
?>