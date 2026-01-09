<?php
include 'includes/header.php';
include 'includes/connect.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Sales_Person") { 
    exit();
}
$user_id = $_SESSION['user_id'];
$get_sales_id = "SELECT `sales_person_id` FROM `sales_person` WHERE user_id = '$user_id'";
$result = mysqli_query($con, $get_sales_id);
$row = mysqli_fetch_assoc($result);
$sales_person_id = $row['sales_person_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $listing_id = intval($_POST['listing_id']);
        
        if ($action == 'approve') {
            $sql = "UPDATE land_listing SET approval_status = 'Approved' WHERE listing_id = $listing_id";
            if (mysqli_query($con, $sql)) {
                $_SESSION['success_message'] = 'Listing approved successfully';
            } else {
                $_SESSION['error_message'] = 'Failed to approve listing';
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        
        if ($action == 'reject') {
            $reason = mysqli_real_escape_string($con, $_POST['reason']);
            $sql = "UPDATE land_listing SET approval_status = 'Rejected', rejection_reason = '$reason' WHERE listing_id = $listing_id";
            if (mysqli_query($con, $sql)) {
                $_SESSION['success_message'] = 'Listing rejected successfully';
            } else {
                $_SESSION['error_message'] = 'Failed to reject listing';
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        
                    if ($action == 'mark_sold') {
                $sql1 = "SELECT `asking_price`, `company_percentage` 
                        FROM `land_listing` 
                        WHERE listing_id = $listing_id";
                $r = mysqli_query($con, $sql1);

                // Since one row is always guaranteed:
                $row = mysqli_fetch_assoc($r);

                $asking_price = $row['asking_price'];
                $company_percentage = $row['company_percentage'];

                $company_profit = $asking_price * ($company_percentage / 100);

                $sql = "UPDATE land_listing 
                        SET status = 'sold', company_profit = '$company_profit', sales_person_id='$sales_person_id'  
                        WHERE listing_id = $listing_id";

                if (mysqli_query($con, $sql)) {
                    $_SESSION['success_message'] = 'Listing marked as sold';
                } else {
                    $_SESSION['error_message'] = 'Failed to mark as sold';
                }

                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        
        if ($action == 'reopen') {
            $sql = "UPDATE land_listing SET approval_status = 'Pending', status = 'not sold', rejection_reason = NULL WHERE listing_id = $listing_id";
            if (mysqli_query($con, $sql)) {
                $_SESSION['success_message'] = 'Listing reopened successfully';
            } else {
                $_SESSION['error_message'] = 'Failed to reopen listing';
            }
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Fetch listings with filters
$where_conditions = array("1=1");

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status = mysqli_real_escape_string($con, $_GET['status']);
    $where_conditions[] = "ll.approval_status = '$status'";
}

if (isset($_GET['zone']) && $_GET['zone'] !== '') {
    $zone = mysqli_real_escape_string($con, $_GET['zone']);
    $where_conditions[] = "ll.land_type = '$zone'";
}

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $search = mysqli_real_escape_string($con, $_GET['search']);
    $where_conditions[] = "(ll.land_address LIKE '%$search%' OR ll.description LIKE '%$search%' OR ll.land_number LIKE '%$search%')";
}

$where_clause = implode(" AND ", $where_conditions);

$sql = "SELECT ll.*, 
        c.user_id, u.full_name as client_name
        FROM land_listing ll
        LEFT JOIN client c ON ll.client_id = c.client_id
        LEFT JOIN user u ON c.user_id = u.user_id
        WHERE $where_clause
        ORDER BY ll.listing_date DESC";

$result = mysqli_query($con, $sql);
$listings = array();
while ($row = mysqli_fetch_assoc($result)) {
    $listings[] = $row;
}

// Count listings by status
$count_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN approval_status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN approval_status = 'Approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN approval_status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
    SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold
    FROM land_listing";
$count_result = mysqli_query($con, $count_sql);
$status_counts = mysqli_fetch_assoc($count_result);

// Get selected listing details for modal
$selected_listing = null;
if (isset($_GET['view_id'])) {
    $view_id = intval($_GET['view_id']);
    $detail_sql = "SELECT ll.*, 
                   c.user_id, u.full_name as client_name
                   FROM land_listing ll
                   LEFT JOIN client c ON ll.client_id = c.client_id
                   LEFT JOIN user u ON c.user_id = u.user_id
                   WHERE ll.listing_id = $view_id";
    $detail_result = mysqli_query($con, $detail_sql);
    $selected_listing = mysqli_fetch_assoc($detail_result);
    
    // Get photos for this listing
    $photos_sql = "SELECT photo_path FROM land_photos WHERE listing_id = $view_id";
    $photos_result = mysqli_query($con, $photos_sql);
    $photos = array();
    while ($photo_row = mysqli_fetch_assoc($photos_result)) {
        $photos[] = $photo_row['photo_path'];
    }
    $selected_listing['photos'] = $photos;
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <style>
    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    .status-pending, .status-Pending { background: #fff3cd; color: #856404; }
    .status-approved, .status-Approved { background: #d4edda; color: #155724; }
    .status-denied, .status-rejected, .status-Rejected { background: #f8d7da; color: #721c24; }
    .status-sold { background: #e2e3e5; color: #383d41; }

    .property-images-gallery {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        background: #fafafa;
    }

    .property-image {
        cursor: pointer;
        transition: transform 0.2s;
        border: 2px solid #e0e0e0;
    }

    .property-image:hover {
        transform: scale(1.02);
        border-color: #2196f3;
    }
    
    .land-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }
    
    .land-modal.active {
        display: block;
    }
    
    .land-modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 0;
        border: 1px solid #888;
        border-radius: 8px;
        max-width: 800px;
    }
    
    .land-modal-header {
        padding: 15px 20px;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .land-modal-close {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .land-modal-close:hover {
        color: #000;
    }
    
    .land-modal-body {
        padding: 20px;
    }
    
    .reject-form {
        display: none;
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }
    .form-control, 
select.form-control {
    font-size: 14px !important;
    color: #263a4f !important;
    background-color: #ffffff !important;
    border: 1px solid #ddd !important;
    padding: 10px 12px !important;
    height: auto !important;
}

.form-control option,
select.form-control option {
    font-size: 14px !important;
    color: #263a4f !important;
    background-color: #ffffff !important;
    padding: 8px !important;
}
    </style>
</head>
<body>
<div class="site-preloader-wrap">
    <div class="spinner"></div>
</div>

<!-- Page Header -->
<section class="page-header padding bg-grey">
    <div class="container">
        <div class="row d-flex align-items-center">
            <div class="col-lg-8">
                <h1>Land & Equipment Management</h1>
                <p>Manage land listings for sale and track equipment usage</p>
            </div>
        </div>
    </div>
</section>

<!-- Tab Navigation -->
<section class="padding">
    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
            ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
            ?>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-12">
                <div class="tab-navigation mb-40">
                    <button class="tab-btn active" onclick="openTab('land-listings')">
                        <i class="fas fa-map-marked-alt"></i> Land Listings (<?php echo $status_counts['total']; ?>)
                    </button>
                </div>
            </div>
        </div>

        <!-- Land Listings Tab -->
        <div id="land-listings" class="tab-content active">
            <!-- Search and Filter -->
            <div class="row mb-30">
                <div class="col-md-12">
                    <div class="service-item box-shadow padding-15">
                        <form method="GET" action="" id="filterForm">
                            <div class="row">
                                <div class="col-md-3 padding-10">
                                    <input type="text" name="search" class="form-control" placeholder="Search listings..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                </div>
                                <div class="col-md-3 padding-10">
                                    <select class="form-control" name="status" id="filter-status">
                                        <option value="">All Status</option>
                                        <option value="Pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Pending') ? 'selected' : ''; ?>>Pending Review</option>
                                        <option value="Approved" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                        <option value="Rejected" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                        <option value="sold" <?php echo (isset($_GET['status']) && $_GET['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                                    </select>
                                </div>
                                <div class="col-md-3 padding-10">
                                    <select class="form-control" name="zone" id="filter-zone">
                                        <option value="">All Zones</option>
                                        <option value="Residential" <?php echo (isset($_GET['zone']) && $_GET['zone'] == 'Residential') ? 'selected' : ''; ?>>Residential</option>
                                        <option value="Commercial" <?php echo (isset($_GET['zone']) && $_GET['zone'] == 'Commercial') ? 'selected' : ''; ?>>Commercial</option>
                                        <option value="Agricultural" <?php echo (isset($_GET['zone']) && $_GET['zone'] == 'Agricultural') ? 'selected' : ''; ?>>Agricultural</option>
                                        <option value="Industrial" <?php echo (isset($_GET['zone']) && $_GET['zone'] == 'Industrial') ? 'selected' : ''; ?>>Industrial</option>
                                    </select>
                                </div>
                                <div class="col-md-3 padding-10">
                                    <button type="submit" class="default-btn" style="width: 100%; padding: 10px;">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Land Listings -->
            <div class="row">
                <?php if (empty($listings)): ?>
                    <div class="col-12">
                        <div class="service-item box-shadow padding-15 text-center">
                            <p>No listings found matching your criteria.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($listings as $listing): ?>
                    <div class="col-lg-4 col-md-6 padding-10">
                        <div class="service-item box-shadow" style="padding: 15px;">
                            <div class="service-content" style="padding: 0;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 style="font-size: 16px; margin: 0;"><?php echo htmlspecialchars($listing['land_area']); ?> m² - <?php echo htmlspecialchars($listing['land_type']); ?></h4>
                                    <span class="status-badge status-<?php echo htmlspecialchars($listing['approval_status']); ?>">
                                        <?php echo htmlspecialchars($listing['approval_status']); ?>
                                    </span>
                                </div>
                                <p style="font-size: 13px; margin: 5px 0;"><strong>Location:</strong> <?php echo htmlspecialchars($listing['land_address']); ?></p>
                                <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Land Number:</strong> <?php echo htmlspecialchars($listing['land_number']); ?></p>
                                <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Price:</strong> $<?php echo number_format($listing['asking_price'], 2); ?></p>
                                <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Owner:</strong> <?php echo htmlspecialchars($listing['contact_name']); ?></p>
                                <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Listed:</strong> <?php echo date('M d, Y', strtotime($listing['listing_date'])); ?></p>
                                
                                <?php if ($listing['approval_status'] == 'Rejected' && $listing['rejection_reason']): ?>
                                <p style="font-size: 11px; color: #f44336; margin: 5px 0;"><strong>Reason:</strong> <?php echo htmlspecialchars($listing['rejection_reason']); ?></p>
                                <?php endif; ?>
                                
                                <div class="listing-actions mt-3" style="gap: 5px; display: flex;">
                                    <?php if ($listing['approval_status'] == 'Pending'): ?>
                                        <form method="POST" style="flex: 1; margin: 0;">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                                            <button type="submit" class="dl-btn" style="padding: 5px 10px; font-size: 12px; background: #4caf50; width: 100%;" onclick="return confirm('Are you sure you want to approve this listing?')">Approve</button>
                                        </form>
                                        <button class="dl-btn" style="padding: 5px 10px; font-size: 12px; background: #f44336; flex: 1;" onclick="showRejectForm(<?php echo $listing['listing_id']; ?>)">Reject</button>
                                    <?php elseif ($listing['approval_status'] == 'Approved' && $listing['status'] != 'sold'): ?>
                                        <form method="POST" style="flex: 1; margin: 0;">
                                            <input type="hidden" name="action" value="mark_sold">
                                            <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                                            <button type="submit" class="dl-btn" style="padding: 5px 10px; font-size: 12px; background: #2196f3; width: 100%;" onclick="return confirm('Are you sure you want to mark this as sold?')">Mark Sold</button>
                                        </form>
                                    <?php elseif ($listing['approval_status'] == 'Rejected'): ?>
                                        <form method="POST" style="flex: 1; margin: 0;">
                                            <input type="hidden" name="action" value="reopen">
                                            <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                                            <button type="submit" class="dl-btn" style="padding: 5px 10px; font-size: 12px; background: #4caf50; width: 100%;" onclick="return confirm('Are you sure you want to reopen this listing?')">Reopen</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="?view_id=<?php echo $listing['listing_id']; ?>" class="dl-btn" style="padding: 5px 10px; font-size: 12px; flex: 1; text-align: center; text-decoration: none;">Details</a>
                                </div>
                                
                                <div class="reject-form" id="reject-form-<?php echo $listing['listing_id']; ?>">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                                        <label>Rejection Reason:</label>
                                        <textarea name="reason" class="form-control" rows="2" required></textarea>
                                        <div style="margin-top: 10px; display: flex; gap: 5px;">
                                            <button type="submit" class="dl-btn" style="padding: 5px 10px; font-size: 12px; background: #f44336; flex: 1;">Submit</button>
                                            <button type="button" class="dl-btn" style="padding: 5px 10px; font-size: 12px; background: #666; flex: 1;" onclick="hideRejectForm(<?php echo $listing['listing_id']; ?>)">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Land Listing Details Modal -->
<div id="listingDetailsModal" class="land-modal <?php echo $selected_listing ? 'active' : ''; ?>">
    <div class="land-modal-content" style="max-width: 800px;">
        <div class="land-modal-header">
            <h3>Land Listing Details</h3>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="land-modal-close" style="text-decoration: none;">&times;</a>
        </div>
        <div class="land-modal-body">
            <?php if ($selected_listing): ?>
            <div class="listing-details">
                <?php if (!empty($selected_listing['photos'])): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h5>Property Images</h5>
                        <div class="property-images-gallery">
                            <div class="row">
                                <?php foreach ($selected_listing['photos'] as $photo): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="property-image" style="width: 100%; height: 250px; background: #f0f0f0; border-radius: 5px; overflow: hidden;">
                                        <img src="<?php echo htmlspecialchars($photo); ?>" alt="Property View" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Property Information</h5>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($selected_listing['land_address']); ?></p>
                        <p><strong>Size:</strong> <?php echo htmlspecialchars($selected_listing['land_area']); ?> m²</p>
                        <p><strong>Type:</strong> <?php echo htmlspecialchars($selected_listing['land_type']); ?></p>
                        <p><strong>Land Number:</strong> <?php echo htmlspecialchars($selected_listing['land_number']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Listing Details</h5>
                        <p><strong>Asking Price:</strong> $<?php echo number_format($selected_listing['asking_price'], 2); ?></p>
                        <p><strong>Listed Date:</strong> <?php echo date('F d, Y', strtotime($selected_listing['listing_date'])); ?></p>
                        <p><strong>Status:</strong> <span class="status-badge status-<?php echo $selected_listing['approval_status']; ?>"><?php echo htmlspecialchars($selected_listing['approval_status']); ?></span></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5>Owner Information</h5>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($selected_listing['contact_name']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($selected_listing['contact_phone']); ?></p>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5>Description</h5>
                        <p><?php echo nl2br(htmlspecialchars($selected_listing['description'])); ?></p>
                    </div>
                </div>

                <?php if ($selected_listing['rejection_reason']): ?>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 style="color: #f44336;">Rejection Reason</h5>
                        <p><?php echo nl2br(htmlspecialchars($selected_listing['rejection_reason'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-group text-right" style="gap: 10px; display: flex; justify-content: flex-end;">
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="dl-btn" style="background: #666; padding: 8px 15px; text-decoration: none;">Close</a>
                    <?php if ($selected_listing['approval_status'] === 'Pending'): ?>
                        <form method="POST" style="display: inline; margin: 0;">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="listing_id" value="<?php echo $selected_listing['listing_id']; ?>">
                            <button type="submit" class="dl-btn" style="background: #4caf50; padding: 8px 15px;" onclick="return confirm('Are you sure you want to approve this listing?')">Approve Listing</button>
                        </form>
                        <button type="button" class="dl-btn" style="background: #f44336; padding: 8px 15px;" onclick="showRejectFormModal(<?php echo $selected_listing['listing_id']; ?>)">Reject Listing</button>
                        
                        <div class="reject-form" id="reject-form-modal-<?php echo $selected_listing['listing_id']; ?>" style="width: 100%; margin-top: 15px;">
                            <form method="POST">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="listing_id" value="<?php echo $selected_listing['listing_id']; ?>">
                                <label>Rejection Reason:</label>
                                <textarea name="reason" class="form-control" rows="3" required></textarea>
                                <div style="margin-top: 10px; display: flex; gap: 5px;">
                                    <button type="submit" class="dl-btn" style="padding: 8px 15px; background: #f44336; flex: 1;">Submit Rejection</button>
                                    <button type="button" class="dl-btn" style="padding: 8px 15px; background: #666; flex: 1;" onclick="hideRejectFormModal(<?php echo $selected_listing['listing_id']; ?>)">Cancel</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openTab(tabName) {
    const tabcontent = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabcontent.length; i++) {
        tabcontent[i].classList.remove("active");
    }

    const tabbuttons = document.getElementsByClassName("tab-btn");
    for (let i = 0; i < tabbuttons.length; i++) {
        tabbuttons[i].classList.remove("active");
    }

    document.getElementById(tabName).classList.add("active");
    event.currentTarget.classList.add("active");
}

function showRejectForm(listingId) {
    document.getElementById('reject-form-' + listingId).style.display = 'block';
}

function hideRejectForm(listingId) {
    document.getElementById('reject-form-' + listingId).style.display = 'none';
}

function showRejectFormModal(listingId) {
    document.getElementById('reject-form-modal-' + listingId).style.display = 'block';
}

function hideRejectFormModal(listingId) {
    document.getElementById('reject-form-modal-' + listingId).style.display = 'none';
}
</script>

<?php include 'includes/footer.html'; ?>
</body>
</html>