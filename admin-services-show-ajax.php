<?php
include 'includes/connect.php';


// Get filters from POST request
$status = isset($_POST['status']) ? mysqli_real_escape_string($con, $_POST['status']) : 'pending';
$clientName = isset($_POST['clientName']) ? mysqli_real_escape_string($con, $_POST['clientName']) : '';
$serviceType = isset($_POST['serviceType']) ? mysqli_real_escape_string($con, $_POST['serviceType']) : '';
$dateRange = isset($_POST['dateRange']) ? $_POST['dateRange'] : '';


//services
// Example: handle services tab
if ($status === 'services') {
    $query = "SELECT * FROM service  ";
    $result = mysqli_query($con, $query);

        // Add the "Add New Service" button here
    echo '<div class="row mb-3">
            <div class="col-12 text-end">
                <button class="btn btn-success" id="addServiceBtn">
                    <i class="fas fa-plus"></i> Add New Service
                </button>
            </div>
          </div>'; 

    echo '<div class="row">';
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div class='col-lg-4 mb-3'>
                <div class='card p-3 request-card'>
                    <h5>".htmlspecialchars($row['service_name'])."</h5>
                    <p>".htmlspecialchars($row['description'])."</p>
                    <p><strong>range Price:</strong> ".(int)$row['min_price']."-".$row['max_price']."</p>
                    <p><strong>ID:</strong> ".(int)$row['service_id']."</p>
                    <p>".htmlspecialchars($row['status'])."</p>

                    <!-- Edit Button -->
            <button class='btn btn-warning btn-sm editServiceBtn' data-id='".(int)$row['service_id']."'>Edit</button>
            <button class='btn btn-danger btn-sm deleteServiceBtn' data-id='".(int)$row['service_id']."'>Deativate</button>


                </div>
              </div>";
    }
    echo '</div>';
    mysqli_close($con);
    exit;
}




// Base query
$query = "SELECT s.service_id,sr.request_id, u.full_name AS client_name, s.service_name, 
                 sr.request_date, sr.STATUS, c.client_id
          FROM service_request sr
          JOIN client c ON sr.client_id = c.client_id
          JOIN user u ON c.user_id = u.user_id
          JOIN service s ON sr.service_id = s.service_id
          WHERE sr.STATUS = '$status'";

// Apply filters
if (!empty($clientName)) {
    $query .= " AND u.full_name LIKE '%$clientName%'";
}
if (!empty($serviceType)) {
    $query .= " AND s.service_id = '$serviceType'";
}

// Date filter
if (!empty($dateRange)) {
    switch ($dateRange) {
        case 'today':
            $query .= " AND DATE(sr.request_date) = CURDATE()";
            break;
        case 'week':
            $query .= " AND YEARWEEK(sr.request_date) = YEARWEEK(CURDATE())";
            break;
        case 'month':
            $query .= " AND YEAR(sr.request_date) = YEAR(CURDATE()) AND MONTH(sr.request_date) = MONTH(CURDATE())";
            break;
        case '3months':
            $query .= " AND sr.request_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
            break;
    }
}

$query .= " ORDER BY sr.request_date DESC";

$result = mysqli_query($con, $query);

if ($result) {
    if (mysqli_num_rows($result) == 0) {
        echo "<div class='col-12'><p class='text-center text-muted my-3'>No $status requests found.</p></div>";
    } else {
        echo '<div class="row">';
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['request_id'];
            $client = htmlspecialchars($row['client_name']);
            $service = htmlspecialchars($row['service_name']);
            $date = date("M d, Y", strtotime($row['request_date']));
            $clientId = $row['client_id'];

            // Determine status badge class
            $statusClass = 'status-' . strtolower($row['STATUS']);
?>
<div class="col-lg-6 mb-3">
    <div class="card p-3 request-card">
        <div class="d-flex justify-content-between mb-2 align-items-start">
            <h5>Service Request #<?php echo $id; ?></h5>
            <span class="status-badge <?php echo $statusClass; ?>">
                <?php echo ucfirst($row['STATUS']); ?>
            </span>
        </div>
        <p><strong>Client:</strong> <?php echo $client; ?></p>
        <p><strong>Service:</strong> <?php echo $service; ?></p>
        <p><strong>Date:</strong> <?php echo $date; ?></p>

        <?php if (strtolower($row['STATUS']) === 'pending'): ?>
        <button type="button" class="btn  btn-primary btn-sm respond-btn" 
                data-id="<?php echo $id; ?>" 
                data-client-id="<?php echo $clientId; ?>">
            Respond
        </button>
        <?php endif; ?>

        <?php if (strtolower($row['STATUS']) === 'rejected'): ?>
        <button type="button" class="btn  btn-primary btn-sm viewReasonBtn" 
                data-id="<?php echo $id; ?>" 
                >
            Rejection Details
        </button>
        <?php endif; ?>

         <?php if (strtolower($row['STATUS']) === 'approved'): ?>
        <button type="button" class="btn  btn-primary btn-sm viewDetailsBtn" 
                data-id="<?php echo $id; ?>" 
                >
             Details
        </button>
        <?php endif; ?>
    </div>
</div>
<?php
        }
        echo '</div>';
    }
} else {
    echo "<div class='col-12'><p class='text-center text-danger my-3'>Query failed: " . mysqli_error($con) . "</p></div>";
}

mysqli_close($con);
?>


 

 



 
