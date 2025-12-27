<?php
include 'includes/connect.php';

// Get filters from POST
$status = isset($_POST['status']) ? mysqli_real_escape_string($con, $_POST['status']) : 'active';
$projectName = isset($_POST['projectName']) ? mysqli_real_escape_string($con, $_POST['projectName']) : '';
$dateRange = isset($_POST['dateRange']) ? $_POST['dateRange'] : '';
$leadengineer = isset($_POST['leadengineer']) ? $_POST['leadengineer'] : '';
$clientName = isset($_POST['clientName']) ? mysqli_real_escape_string($con, $_POST['clientName']) : '';

// Base query with GROUP_CONCAT to combine multiple services and equipment
$query = "SELECT  
    project.project_id,
    project.status,
    project.project_name,
    project.team_size,
    project.start_date,
    project.end_date,
    project.total_cost,
    project.progress,
    lead_user.full_name AS lead_name,
    client_user.full_name AS client_name,
    client_user.email AS client_email,
	GROUP_CONCAT(DISTINCT land.land_address SEPARATOR ', ') AS land_address,
    GROUP_CONCAT(DISTINCT land.land_number SEPARATOR ', ') AS land_numbers,
    GROUP_CONCAT(DISTINCT service.service_name SEPARATOR ', ') AS service_names,
    GROUP_CONCAT(DISTINCT equipment.equipment_name SEPARATOR ', ') AS equipment_names

FROM project

JOIN lead_engineer 
    ON lead_engineer.lead_engineer_id = project.lead_engineer_id
JOIN user AS lead_user 
    ON lead_user.user_id = lead_engineer.user_id

LEFT JOIN service_request 
    ON service_request.project_id = project.project_id
LEFT JOIN client 
    ON service_request.client_id = client.client_id
LEFT JOIN user AS client_user 
    ON client_user.user_id = client.user_id

LEFT JOIN service 
    ON service.service_id = service_request.service_id

LEFT JOIN uses_project_equipment 
    ON uses_project_equipment.project_id = project.project_id
LEFT JOIN equipment 
    ON equipment.equipment_id = uses_project_equipment.equipment_id
LEFT JOIN land ON service_request.land_id=land.land_id

WHERE project.status = '$status'
";

// Apply filters
if (!empty($projectName)) {
    $query .= " AND project.project_name LIKE '%$projectName%'";
}
if (!empty($leadengineer)) {
    $query .= " AND project.lead_engineer_id LIKE '%$leadengineer%'";
}
// Add client name filter
if (!empty($clientName)) {
    $query .= " AND client_user.full_name LIKE '%$clientName%'";
}


// Date filter
if (!empty($dateRange)) {
    switch ($dateRange) {
        case 'today':
            $query .= " AND DATE(project.end_date) = CURDATE()";
            break;
        case 'week':
            $query .= " AND YEARWEEK(project.end_date) = YEARWEEK(CURDATE())";
            break;
        case 'month':
            $query .= " AND YEAR(project.end_date) = YEAR(CURDATE()) AND MONTH(project.start_date) = MONTH(CURDATE())";
            break;
        case '3months':
            $query .= " AND project.end_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
            break;
    }
}

$query .= " 
GROUP BY project.project_id
ORDER BY project.start_date DESC";

$result = mysqli_query($con, $query);

if (!$result) {
    echo "<div class='col-12 text-danger'>Query failed: " . mysqli_error($con) . "</div>";
    mysqli_close($con);
    exit;
}

if (mysqli_num_rows($result) == 0) {
    echo "<div class='col-12'><p class='text-center text-muted my-3'>No $status projects found.</p></div>";
} else {
    echo '<div class="row">';
    
    while ($row = mysqli_fetch_assoc($result)) {
        $id = (int)$row['project_id'];
        $name = htmlspecialchars($row['project_name']);
        $lead = htmlspecialchars($row['lead_name']);
        $client_names = $row['client_name'] ? htmlspecialchars($row['client_name']) : 'No clients assigned';
        $team_size = (int)$row['team_size'];
        $start = date("M d, Y", strtotime($row['start_date']));
        $end = date("M d, Y", strtotime($row['end_date']));
        $service_names = $row['service_names'] ?? 'No services';
        $land_numbers = $row['land_numbers'] ?? 'No land numbers';
        $land_address = $row['land_address'] ?? 'No land address';
        $equipment_names = $row['equipment_names'] ?? 'No equipment';
        $statusClass = 'status-' . strtolower($row['status']);
        $progress= $row['progress'];
        $total_price= $row['total_cost'];
        $client_email= $row['client_email'];
?>
        <div class="col-lg-4 col-md-6 padding-10">
            <div class="service-item box-shadow" style="padding: 15px;">
                <div class="service-content" style="padding: 0;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 style="font-size: 16px; margin: 0;"><?php echo $name; ?></h4>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </div>
                    
                    <p style="font-size: 13px; margin: 5px 0;">
                        <strong>Client(s):</strong> <?php echo $client_names; ?>
                    </p>
                    <p style="font-size: 12px; margin: 3px 0; color: #666;">
                        <strong>Lead Engineer:</strong> <?php echo $lead; ?>
                    </p>
                    <p style="font-size: 12px; margin: 3px 0; color: #666;">
                        <strong>Team Size:</strong> <?php echo $team_size; ?> members
                    </p>
                    <p style="font-size: 12px; margin: 3px 0; color: #666;">
                        <strong>Duration:</strong> <?php echo $start . ' - ' . $end; ?>
                    </p>
                    <p style="font-size: 12px; margin: 3px 0; color: #666;">
                        <strong>Progress:</strong> <?php echo $progress; ?> %
                    </p>
                    <div class="services-tags mt-2">
                        <?php 
                        if ($service_names && $service_names !== 'No services') {
                            $services = explode(', ', $service_names);
                            foreach ($services as $service) {
                                if (trim($service)) {
                                    echo '<span class="service-tag">' . htmlspecialchars(trim($service)) . '</span>';
                                }
                            }
                        } else {
                            echo '<span class="service-tag" style="background: #f5f5f5; color: #999;">No services</span>';
                        }
                        ?>
                    </div>
                    
                    <div class="equipment-tags mt-2">
                        <?php 
                        if ($equipment_names && $equipment_names !== 'No equipment') {
                            $equipment = explode(', ', $equipment_names);
                            foreach ($equipment as $equip) {
                                if (trim($equip)) {
                                    echo '<span class="equipment-tag">' . htmlspecialchars(trim($equip)) . '</span>';
                                }
                            }
                        } else {
                            echo '<span class="equipment-tag" style="background: #f5f5f5; color: #999;">No equipment</span>';
                        }
                        ?>
                    </div>
                    
                    <div class="dl-btn-group mt-2" style="gap: 5px; display: flex;">
    
                        <!-- Details button always shown -->
                        <button type="button" 
                            class="dl-btn detailsProjectBtn"
                            style="padding: 5px 10px; font-size: 12px; background: #ff9800; border: none; cursor: pointer;" 
                            data-id="<?php echo $id; ?>">
                            Details
                        </button>

                        <!-- Show Manage only if status = active -->
                        <?php if (strtolower($row['status']) === 'active'): ?>
                            <button type="button" 
                                class="dl-btn editProjectBtn"
                                style="padding: 5px 10px; font-size: 12px; background: #ff9800; border: none; cursor: pointer;" 
                                data-id="<?php echo $id; ?>">
                                Manage
                            </button>

                        <?php else: ?>
                            <!-- When completed show Done -->


                            <button type="button" 
                                class="btn btn-warning dl-btn showPDFBtn"
                                data-id="<?php echo $id; ?>"
                                >
                                Show
                            </button>


                            <button type="button"
                                class="btn btn-success sendEmailModalBtn"
                                data-project_id="<?php echo $id; ?>"
                                data-project="<?php echo $name; ?>"
                                data-price="<?php echo $total_price; ?>"
                                data-email="<?php echo $client_email; ?>"
                                data-services="<?php echo $service_names; ?>"
                                data-land_nb="<?php echo $land_numbers; ?>"
                                data-land_address="<?php echo $land_address; ?>"
                                >
                                
                                Done
                            </button>
 
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
<?php
    }
    
    echo '</div>'; // Close the row
}

mysqli_close($con);
?>