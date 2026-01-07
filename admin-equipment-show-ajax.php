<?php
include 'includes/connect.php';

// Get status filter and search term
$status = isset($_POST['status']) ? mysqli_real_escape_string($con, $_POST['status']) : 'all';
$search = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';

// ================= MAINTENANCE HISTORY TAB =================
if ($status === 'history') {
    $query = "SELECT 
        e.equipment_id,
        e.equipment_name,
        e.equipment_type,
        MAX(m.request_date) AS last_date,
        COUNT(m.maintenance_id) AS total_maintenance
    FROM equipment e
    LEFT JOIN maintenance m ON e.equipment_id = m.equipment_id";
    
    // Add search condition for history tab
    if (!empty($search)) {
        $query .= " WHERE e.equipment_name LIKE '%$search%' 
                    OR e.equipment_type LIKE '%$search%'";
    }
    
    $query .= " GROUP BY e.equipment_id
                ORDER BY total_maintenance DESC, e.equipment_id ASC";
    
    $result = mysqli_query($con, $query);
    
    if (!$result) {
        echo "<div class='col-12 text-danger'>Query failed: " . mysqli_error($con) . "</div>";
        mysqli_close($con);
        exit;
    }
    
    if (mysqli_num_rows($result) == 0) {
        $message = !empty($search) 
            ? "No equipment found matching '<strong>" . htmlspecialchars($search) . "</strong>'"
            : "No equipment found.";
        echo "<div class='col-12'><p class='text-center text-muted my-3'>{$message}</p></div>";
    } else {
        echo '<div class="table-responsive">
                <table class="table table-striped table-hover" style="background: white; border-radius: 8px; overflow: hidden;">
                    <thead style="background: #263a4f; color: white;">
                        <tr>
                            <th>Equipment ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Last Maintenance</th>
                            <th>Total Maintenance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        while ($row = mysqli_fetch_assoc($result)) {
            $id = (int)$row['equipment_id'];
            $name = htmlspecialchars($row['equipment_name']);
            $type = htmlspecialchars($row['equipment_type']);
            $lastDate = $row['last_date'] ? date("M d, Y", strtotime($row['last_date'])) : 'N/A';
            $total = (int)$row['total_maintenance'];
            
            // Highlight search terms
            if (!empty($search)) {
                $name = highlightSearchTerm($name, $search);
                $type = highlightSearchTerm($type, $search);
            }
            
            echo "<tr>
                    <td>#EQ-{$id}</td>
                    <td>{$name}</td>
                    <td>{$type}</td>
                    <td>{$lastDate}</td>
                    <td><span style='background: #ff7607; color: white; padding: 4px 12px; border-radius: 12px; font-weight: 600;'>{$total}</span></td>
                    <td>
                        <button class='btn btn-sm btn-primary viewCostHistoryBtn' 
                                data-id='{$id}' 
                                data-name='" . htmlspecialchars($row['equipment_name']) . "'
                                style='padding: 6px 12px; font-size: 13px;'>
                            <i class='fas fa-dollar-sign'></i> View Costs
                        </button>
                    </td>
                  </tr>";
        }
        
        echo '      </tbody>
                </table>
              </div>';
    }
    
    mysqli_close($con);
    exit;
}

// ================= REGULAR EQUIPMENT TABS =================
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

// Build WHERE clause
$whereConditions = array();

if ($status !== 'all') {
    $whereConditions[] = "equipment.status = '$status'";
}

if (!empty($search)) {
    $whereConditions[] = "(equipment.equipment_name LIKE '%$search%' 
                          OR equipment.equipment_type LIKE '%$search%' 
                          OR equipment.serial_number LIKE '%$search%' 
                          OR equipment.model LIKE '%$search%')";
}

if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

$query .= " GROUP BY equipment.equipment_id
            ORDER BY equipment.equipment_id DESC";

$result = mysqli_query($con, $query);

if (!$result) {
    echo "<div class='col-12 text-danger'>Query failed: " . mysqli_error($con) . "</div>";
    mysqli_close($con);
    exit;
}

// Add the "Add New Equipment" button at the top
echo '<div class="row mb-3">
        <div class="col-12 text-end">
            <button class="btn btn-success" id="addEquipmentBtn">
                <i class="fas fa-plus"></i> Add New Equipment
            </button>
        </div>
      </div>'; 

if (mysqli_num_rows($result) == 0) {
    $message = !empty($search) 
        ? "No equipment found matching '<strong>" . htmlspecialchars($search) . "</strong>'"
        : "No equipment found.";
    echo "<div class='col-12'><p class='text-center text-muted my-3'>{$message}</p></div>";
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

        $maintenance   = $row['date']
            ? date("M d, Y", strtotime($row['date']))
            : 'N/A';

        // Highlight search terms
        if (!empty($search)) {
            $name = highlightSearchTerm($name, $search);
            $type = highlightSearchTerm($type, $search);
            $serial = highlightSearchTerm($serial, $search);
            $model = highlightSearchTerm($model, $search);
        }

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
                        <strong>Purchase Date:</strong> <?php echo $maintenance; ?>
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
                                data-equipment-name="<?php echo htmlspecialchars($row['equipment_name']); ?>"
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

// Helper function to highlight search terms
function highlightSearchTerm($text, $searchTerm) {
    if (empty($searchTerm)) {
        return $text;
    }
    
    // Escape special regex characters in search term
    $searchTerm = preg_quote($searchTerm, '/');
    
    // Use regex to replace matching text with highlighted version (case-insensitive)
    return preg_replace(
        '/(' . $searchTerm . ')/i',
        '<span class="search-highlight">$1</span>',
        $text
    );
}
?>