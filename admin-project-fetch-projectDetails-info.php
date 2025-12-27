<?php
include 'includes/connect.php';

$project_id = (int)$_POST['project_id'];

// Get project details with documents and lands
$query = "SELECT 
    submitted_document.document_id,
    document_type.type_name,
    project.project_id, 
    land.land_id,
    land.land_address,
    land.land_number,
    service_request.request_id
FROM submitted_document 
JOIN document_type 
    ON document_type.document_type_id = submitted_document.document_type_id 
JOIN has_servicerequest_submitteddocument 
    ON has_servicerequest_submitteddocument.submitted_document_id = submitted_document.document_id 
JOIN service_request 
    ON service_request.request_id = has_servicerequest_submitteddocument.service_request_id 
JOIN project 
    ON project.project_id = service_request.project_id
JOIN land 
    ON land.land_id = service_request.land_id
WHERE project.project_id = $project_id";
$result = mysqli_query($con, $query);

// Get all lands for this project
$landsQuery = "
SELECT DISTINCT 
    land.land_id,
    land.land_address,
    land.land_number,
    land.land_area,
    land.land_type
FROM land
JOIN service_request ON service_request.land_id = land.land_id
WHERE service_request.project_id = $project_id
ORDER BY land.land_id
";
$landsResult = mysqli_query($con, $landsQuery);

// Get total price of service requests for this project
$priceQuery = "
SELECT SUM(sr.price) as total_price
FROM service_request sr
WHERE sr.project_id = $project_id
";
$priceResult = mysqli_query($con, $priceQuery);
$totalPriceRow = mysqli_fetch_assoc($priceResult);
$totalPrice = $totalPriceRow['total_price'] ?? 0;

// Update the project table with total_cost
$updateQuery = "UPDATE project SET total_cost = $totalPrice WHERE project_id = $project_id";
mysqli_query($con, $updateQuery);

$hasDocuments = $result && mysqli_num_rows($result) > 0;
$hasLands = $landsResult && mysqli_num_rows($landsResult) > 0;

?>

<div class="submitted-documents">
    <h4>Project Details for Project #<?php echo $project_id; ?></h4>
    
    <!-- Lands Section -->
    <div class="mb-4">
        <h5>Associated Lands</h5>
        <?php if ($hasLands): ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Land ID</th>
                    <th>Land Number</th>
                    <th>Address</th>
                    <th>Area (sq m)</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($land = mysqli_fetch_assoc($landsResult)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($land['land_id']); ?></td>
                    <td><?php echo htmlspecialchars($land['land_number'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($land['land_address']); ?></td>
                    <td><?php echo htmlspecialchars($land['land_area']); ?></td>
                    <td><?php echo htmlspecialchars($land['land_type']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted">No lands associated with this project.</p>
        <?php endif; ?>
    </div>

    <!-- Documents Section -->
    <div class="mb-4">
        <h5>Submitted Documents</h5>
        <?php if ($hasDocuments): ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Document ID</th>
                    <th>Type</th>
                    <th>Land ID</th>
                    <th>Land Number</th>
                    <th>Land Address</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['document_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['type_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['land_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['land_number'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['land_address']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted">No documents submitted for this project yet.</p>
        <?php endif; ?>
    </div>

    <!-- Total Price -->
    <div class="alert alert-info">
        <strong>Total Service Requests Price: </strong>$<?php echo number_format($totalPrice, 2); ?>
    </div>
</div>

<?php
mysqli_close($con);
?>