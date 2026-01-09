<!doctype html>
<html class="no-js" lang="en"> 
<?php
include 'includes/connect.php';
include 'includes/header.php'; 

// Get filters
$propertyType = isset($_GET['property_type']) ? trim($_GET['property_type']) : '';
$location     = isset($_GET['location']) ? trim($_GET['location']) : '';
$minPrice     = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? $_GET['min_price'] : 0;
$maxPrice     = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? $_GET['max_price'] : 999999999;

// Initialize success message variable
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_listing'])) {
if (!isset($_SESSION['user_id'])) {
    $error_message = "You must be logged in to submit a listing.";
    return;
}
$user = $_SESSION['user_id'];
$q1 = "SELECT client_id FROM client WHERE user_id = $user";
$r = mysqli_query($con, $q1);

if (mysqli_num_rows($r) == 0) {
    $error_message = "Client profile not found.";
    return;
}

$row_client = mysqli_fetch_assoc($r);
$client = $row_client['client_id'];

    $land_number = mysqli_real_escape_string($con, $_POST['land_number']);
    $land_type = mysqli_real_escape_string($con, $_POST['land_type']);
    $land_area = mysqli_real_escape_string($con, $_POST['land_area']);
    $asking_price = mysqli_real_escape_string($con, $_POST['asking_price']);
    $location = mysqli_real_escape_string($con, $_POST['location']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $contact_name = mysqli_real_escape_string($con, $_POST['contact_name']);
    $contact_phone = mysqli_real_escape_string($con, $_POST['contact_phone']);

    // Insert the land info
    $query = "INSERT INTO land_listing 
    (`listing_date`, `asking_price`, `status`, `approval_status`, `description`, `client_id`, 
     `land_number`, `land_area`, `land_type`, `land_address`, `contact_name`, `contact_phone`)
    VALUES (CURRENT_TIMESTAMP, '$asking_price', 'not sold', 'Pending', '$description', '$client', 
            '$land_number', '$land_area', '$land_type', '$location', '$contact_name', '$contact_phone')";

    if (mysqli_query($con, $query)) {
        $listing_id = mysqli_insert_id($con);
        
        // Handle photo uploads
        $upload_dir = "uploads/listing_photo/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (!empty($_FILES['land_photos']['name'][0])) {
            foreach ($_FILES['land_photos']['tmp_name'] as $key => $tmp_name) {
                $file_name = basename($_FILES['land_photos']['name'][$key]);
                $file_tmp = $_FILES['land_photos']['tmp_name'][$key];
                $file_size = $_FILES['land_photos']['size'][$key];

                // Validate file type and size
                $allowed_ext = ['jpg', 'jpeg', 'png'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (!in_array($file_ext, $allowed_ext) || $file_size > 5 * 1024 * 1024) {
                    continue; // skip invalid files
                }

                $unique_name = time() . "_" . uniqid() . "." . $file_ext;
                $target_path = $upload_dir . $unique_name;

                if (move_uploaded_file($file_tmp, $target_path)) {
                    $photo_query = "INSERT INTO land_photos (listing_id, photo_path) VALUES ('$listing_id', '$target_path')";
                    mysqli_query($con, $photo_query);
                }
            }
        }
        
        $success_message = "Your land listing has been submitted successfully! It will be reviewed by our team shortly.";
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit();
    } else {
        $error_message = "Error submitting listing: " . mysqli_error($con);
    }
}

// Check for success parameter in URL
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Your land listing has been submitted successfully! It will be reviewed by our team shortly.";
}
?>
<body>
<div class="site-preloader-wrap">
<div class="spinner"></div>
</div>

<!-- Page Header -->
<section class="page-header padding">
<div class="container">
<div class="page-content text-center">
<h2>Lands For Sale</h2>
<p>Premium Land Properties Available for Development</p>
<?php if (!isset($_SESSION['user_id'])){ ?>
    <a href="login.php" class="default-btn" style="margin-top: 20px;">
        <i class="fas fa-plus-circle"></i> List Your Land For Sale
    </a>
<?php }else{?>
    <button class="default-btn" id="open-upload-modal" style="margin-top: 20px;">
<i class="fas fa-plus-circle"></i> List Your Land For Sale
</button>
<?php } ?>
</div>
</div>
</section>

<?php if ($success_message): ?>
<div class="container" style="margin-top: 20px;">
<div class="alert alert-success" style="padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; text-align: center;">
<i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
</div>
</div>
<?php endif; ?>

<?php if ($error_message): ?>
<div class="container" style="margin-top: 20px;">
<div class="alert alert-danger" style="padding: 15px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; text-align: center;">
<i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
</div>
</div>
<?php endif; ?>

<!-- Upload Land Modal -->
<div id="upload-land-modal" class="land-modal">
<div class="land-modal-content" style="max-width: 800px;">
<span class="land-modal-close" id="close-upload-modal">&times;</span>
<div class="land-modal-body">
<h3 style="margin-bottom: 25px; color: #263a4f; text-align: center;">List Your Land For Sale</h3>
<form id="upload-land-form" method="post" enctype="multipart/form-data" style="padding: 20px;">
<!-- Land Details -->
<div class="row">
<div class="col-md-6 padding-15">
<label style="display: block; margin-bottom: 8px; font-weight: 600; color: #263a4f;">Land Number *</label>
<input type="text" name="land_number" class="form-control" placeholder="e.g., Zahle - 123/45" required>
</div>
</div>

<div class="row">
<div class="col-md-6 padding-15">
<label style="display: block; margin-bottom: 8px; font-weight: 600; color: #263a4f;">Asking Price ($) *</label>
<input type="number" name="asking_price" class="form-control" placeholder="e.g., 250000" min="0" step="0.01" required>
</div>
<div class="col-md-6 padding-15">
<label style="display: block; margin-bottom: 8px; font-weight: 600; color: #263a4f;">Land Area (m²) *</label>
<input type="number" name="land_area" class="form-control" placeholder="e.g., 5000" min="1" required>
</div>
</div>

<div class="row">
<div class="col-md-6 padding-15">
<label style="display: block; margin-bottom: 8px; font-weight: 600; color: #263a4f;">Land Type *</label>
<select name="land_type" class="form-control" required>
<option value="">Select Land Type</option>
<option value="Residential">Residential</option>
<option value="Commercial">Commercial</option>
<option value="Industrial">Industrial</option>
<option value="Agricultural">Agricultural</option>
<option value="Waterfront">Waterfront</option>
<option value="Other">Other</option>
</select>
</div>
<div class="col-md-6 padding-15">
<label style="display: block; margin-bottom: 8px; font-weight: 600; color: #263a4f;">Location/Address *</label>
<input type="text" name="location" class="form-control" placeholder="e.g., North District, Zahle" required>
</div>
</div>
<div class="row">
<div class="col-md-12 padding-15">
<label style="display: block; margin-bottom: 8px; font-weight: 600; color: #263a4f;">
🗺️ Add Land Location Map (Optional but Recommended)
</label>
<div style="background: #e7f3ff; border: 2px solid #0066cc; border-radius: 8px; padding: 15px; margin-bottom: 15px;">
<p style="margin: 0 0 10px 0; color: #263a4f; font-size: 14px;">
<strong>Want to show your land location on a map?</strong>
</p>
<p style="margin: 0 0 12px 0; color: #495057; font-size: 13px;">
Use our interactive mapping tool to draw your land boundaries, then take a screenshot and upload it below with your other photos!
</p>
<button type="button" onclick="openMapForPhoto()" class="default-btn" style="width: 100%;">
<i class="fas fa-map-marked-alt"></i> Open Map Tool (You'll Take a Screenshot)
</button>
<div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; padding: 10px; margin-top: 12px;">
<p style="margin: 0; color: #856404; font-size: 12px;">
<strong>📸 How it works:</strong>
</p>
<ol style="margin: 5px 0 0 0; padding-left: 20px; color: #856404; font-size: 12px; line-height: 1.6;">
<li>Click the button above to open the map tool</li>
<li>Draw your land boundaries on the map</li>
<li>Take a screenshot using your device's screenshot feature</li>
<li>Upload the screenshot in the "Land Photos" section below</li>
</ol>
</div>
</div>
</div>
</div>

<!-- Land Photos (Multiple) -->
<div class="row">
<div class="col-md-12 padding-15">
<label style="display: block; margin-bottom: 8px; font-weight: 600; color: #263a4f;">Land Photos * (You can upload multiple photos)</label>
<div style="border: 2px dashed #ddd; border-radius: 5px; padding: 30px; text-align: center; background: #f8f9fa;">
<input type="file" name="land_photos[]" id="land-photos-input" class="form-control" accept="image/*" multiple required style="display: none;">
<div id="upload-area" style="cursor: pointer;">
<i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #ff7607; margin-bottom: 15px;"></i>
<p style="margin: 0; color: #6c757d; font-size: 16px;">Click to upload land photos</p>
<p style="margin: 5px 0 0 0; color: #999; font-size: 14px;">You can select multiple photos. Supported formats: JPG, PNG, JPEG (Max 5MB each)</p>
</div>
<div id="photos-preview" style="display: none; margin-top: 20px;">
<div id="photos-count" style="margin-bottom: 15px; color: #263a4f; font-weight: 600; font-size: 16px;"></div>
<div id="thumbnails-container" style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;"></div>
<button type="button" id="clear-all-photos" class="default-btn" style="background: #dc3545; margin-top: 15px;">Clear All Photos</button>
</div>
</div>
</div>
</div>

<!-- Description -->
<div class="row">
<div class="col-md-12 padding-15">
<label style="display: block; margin-bottom: 8px; font-weight: 600; color: #263a4f;">Description *</label>
<textarea name="description" class="form-control" rows="4" placeholder="Provide detailed description of your land..." required></textarea>
</div>
</div>

<!-- Contact Information -->
<div class="row">
<div class="col-md-6 padding-15">
<label style="display: block; margin-bottom: 8px; font-weight: 600; color: #263a4f;">Contact Name *</label>
<input type="text" name="contact_name" class="form-control" placeholder="Your full name" required>
</div>
<div class="col-md-6 padding-15">
<label style="display: block; margin-bottom: 8px; font-weight: 600; color: #263a4f;">Contact Phone *</label>
<input type="tel" name="contact_phone" class="form-control" placeholder="Your phone number" required>
</div>
</div>

<!-- Terms -->
<div class="row">
<div class="col-md-12 padding-15">
<div style="display: flex; align-items: center;">
<input type="checkbox" id="terms" name="terms" style="margin-right: 10px;" required>
<label for="terms" style="margin: 0; font-size: 14px;">
I agree that the information provided is accurate and I authorize the company to list my property for sale. I understand that a commission will be charged upon successful sale.
</label>
</div>
</div>
</div>

<!-- Submit Button -->
<div class="row">
<div class="col-md-12 padding-15" style="text-align: center;">
<button type="submit" name="submit_listing" class="default-btn" style="padding: 12px 40px; font-size: 16px;">
<i class="fas fa-check-circle"></i> Submit Land Listing
</button>
<button type="button" class="default-btn" id="cancel-upload" style="background: #6c757d; padding: 12px 40px; font-size: 16px; margin-left: 10px;">
Cancel
</button>
</div>
</div>
</form>
</div>
</div>
</div>
<!-- filter bar -->
<form method="GET" action="land-sale.php">
<section class="filter-section padding bg-grey">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="filter-wrap">
          <div class="row">
            <!-- Property Type -->
            <div class="col-lg-3 col-md-6 padding-15">
              <div class="filter-group">
                <label>Property Type</label>
                <select class="form-control filter-select" name="property_type" id="property-type">
                  <option value="">All Types</option>
                  <option value="residential">Residential</option>
                  <option value="commercial">Commercial</option>
                  <option value="industrial">Industrial</option>
                  <option value="agricultural">Agricultural</option>
                </select>
              </div>
            </div>

            <!-- Location -->
<div class="col-lg-3 col-md-6 padding-15">
  <div class="filter-group">
    <label>Location</label>
    <input type="text" class="form-control" name="location" id="location" placeholder="Enter town or area">
  </div>
</div>


            <!-- Price Range -->
            <div class="col-lg-3 col-md-6 padding-15">
              <div class="filter-group">
                <label>Price Range</label>
                <div class="price-range-inputs">
                  <div class="row">
                    <div class="col-6">
                      <input type="number" class="form-control price-input" name="min_price" id="min-price" placeholder="Min Price" min="0">
                    </div>
                    <div class="col-6">
                      <input type="number" class="form-control price-input" name="max_price" id="max-price" placeholder="Max Price" min="0">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Buttons -->
            <div class="col-lg-3 col-md-6 padding-15">
              <div class="filter-group">
                <label>&nbsp;</label>
                <div class="filter-buttons">
                  <button type="submit" class="default-btn filter-btn" style="width: 100%; margin-bottom: 10px;">Apply Filters</button>
                  <a href="land-sale.php" class="default-btn reset-btn" style="width: 100%; background: #6c757d;">Reset Filters</a>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</form>


<!-- Lands Section -->
<section class="projects-section padding">
<div class="container">
<div class="row d-flex align-items-center">
<div class="col-lg-8 col-md-6 sm-padding">
<div class="section-heading mb-40">
<span>Available Lands</span>
<h2>Discover premium land properties <br>ready for development</h2>
</div>
</div>
<div class="col-lg-4 col-md-6 sm-padding text-right">
<a href="#" class="default-btn">View All Lands</a>
</div>
</div>
<div class="row land-grid">
<?php
// Fetch approved land listings
$query = "
    SELECT ll.*, 
           COALESCE(lp.photo_path, 'img/default-land.jpg') AS photo_path
    FROM land_listing ll
    LEFT JOIN land_photos lp 
        ON lp.photo_id = (
            SELECT MIN(photo_id) 
            FROM land_photos 
            WHERE listing_id = ll.listing_id
        )
    WHERE ll.approval_status = 'Approved'AND ll.status !='sold'
";

// Add filters
if ($propertyType !== '') {
    $propertyTypeEsc = mysqli_real_escape_string($con, $propertyType);
    $query .= " AND LOWER(ll.land_type) = LOWER('$propertyTypeEsc')";
}

if ($location !== '') {
    $locationEsc = mysqli_real_escape_string($con, $location);
    $query .= " AND LOWER(ll.land_address) LIKE LOWER('%$locationEsc%')";
}

$query .= " AND ll.asking_price BETWEEN $minPrice AND $maxPrice";
$query .= " ORDER BY ll.listing_date DESC";

$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $photo = htmlspecialchars($row['photo_path']);
        $type = htmlspecialchars($row['land_type']);
        $address = htmlspecialchars($row['land_address']);
        $price = number_format($row['asking_price']);
        $id = $row['listing_id'];
        ?>
        <div class="col-lg-4 col-sm-6 padding-15 land-item" 
             data-type="<?= strtolower($type) ?>" 
             data-location="<?= strtolower($address) ?>" 
             data-price="<?= $row['asking_price'] ?>">
            <div class="project-item">
                <div class="project-thumb">
                    <img src="<?= $photo ?>" alt="<?= $type ?>">
                </div>
                <div class="overlay"></div>
                <a href="<?= $photo ?>" class="view-icon img-popup" data-gall="land">
                    <i class="fas fa-expand"></i>
                </a>
                <div class="projects-content">
                    <a href="#" class="category"><?= $type ?></a>
                    <h3><a href="#" class="tittle"><?= $address ?></a></h3>
                    <p class="land-price">$<?= $price ?></p>
                    <p class="land-details"><i class="fas fa-map-marker-alt"></i> <?= $address ?></p>
                    <button class="land-details-btn default-btn" data-land="<?= $id ?>">View Details</button>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo '<p style="text-align:center;width:100%;font-size:18px;">No lands available for sale yet.</p>';
}
?>
</div>
</div>
</section>

<?php
// Create modals for each listing
if (mysqli_num_rows($result) > 0) {
    mysqli_data_seek($result, 0);
    while ($row = mysqli_fetch_assoc($result)) {
        $listing_id = $row['listing_id'];
        $photos_query = "SELECT photo_path FROM land_photos WHERE listing_id = $listing_id";
        $photos_result = mysqli_query($con, $photos_query);

        // Fetch all photos
        $photos = [];
        while ($p = mysqli_fetch_assoc($photos_result)) {
            $photos[] = $p['photo_path'];
        }

        $main_photo = $photos[0] ?? 'img/default-land.jpg';
        $price = number_format($row['asking_price']);
        $type = htmlspecialchars($row['land_type']);
        $address = htmlspecialchars($row['land_address']);
        $area = htmlspecialchars($row['land_area']);
        $description = htmlspecialchars($row['description']);
        ?>

        <!-- Land Modal -->
        <div id="land-modal-<?= $listing_id ?>" class="land-modal">
            <div class="land-modal-content">
                <span class="land-modal-close">&times;</span>
                <div class="land-modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="land-modal-image">
                                <img src="<?= $main_photo ?>" alt="<?= $address ?>" style="width:100%;border-radius:5px;">
                                <?php if (count($photos) > 1): ?>
                                    <div style="display:flex;gap:10px;margin-top:10px;overflow-x:auto;">
                                        <?php foreach ($photos as $photo): ?>
                                            <img src="<?= $photo ?>" style="width:70px;height:70px;object-fit:cover;border-radius:5px;cursor:pointer;" onclick="document.querySelector('#land-modal-<?= $listing_id ?> .land-modal-image > img').src='<?= $photo ?>'">
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="land-modal-info">
                                <h3><?= $address ?></h3>
                                <p class="land-modal-price">$<?= $price ?></p>
                                <p class="land-modal-category"><?= $type ?> Land</p>
                                <div class="land-modal-features">
                                    <ul>
                                        <li><strong>Area:</strong> <?= $area ?> m²</li>
                                        <li><strong>Location:</strong> <?= $address ?></li>
                                        <li><strong>Status:</strong> <?= ucfirst($row['status']) ?></li>
                                    </ul>
                                </div>
                                <div class="land-modal-description">
                                    <h4>Description:</h4>
                                    <p><?= nl2br($description) ?></p>
                                </div>
                                <div class="land-modal-contact" style="margin-top:20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                                    <h4 style="margin-bottom: 10px; color: #263a4f;">Contact Our Sales Agent:</h4>
                                    <p style="margin: 5px 0;"><strong><i class="fas fa-user"></i> Agent Name:</strong> John Mitchell</p>
                                    <p style="margin: 5px 0;"><strong><i class="fas fa-phone"></i> Phone:</strong> +1 (555) 789-0123</p>
                                    <p style="margin: 5px 0;"><strong><i class="fas fa-envelope"></i> Email:</strong> j.mitchell@realestate.com</p>
                                </div>
                                <a href="tel:+15557890123" class="default-btn" style="margin-top:15px; width: 100%; text-align: center;">
                                    <i class="fas fa-phone-alt"></i> Call Sales Agent
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php } 
}
?>


<style>
.land-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.7);
    align-items: center;
    justify-content: center;
}

.land-modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 0;
    border: 1px solid #888;
    width: 90%;
    max-width: 800px;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
}

.land-modal-close {
    color: #aaa;
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 35px;
    font-weight: bold;
    z-index: 1;
    cursor: pointer;
}

.land-modal-close:hover,
.land-modal-close:focus {
    color: #000;
}

.land-modal-body {
    padding: 30px;
}

.alert {
    animation: slideDown 0.5s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
<script>
/**
 * Opens map tool in photo generation mode
 */
function openMapForPhoto() {
    // Open map tool with source=listing parameter
    const mapWindow = window.open(
        'map_tool.php?source=listing', 
        'MapToolPhoto', 
        'width=1400,height=900'
    );
    
    if (!mapWindow) {
        alert('Please allow pop-ups for this site to use the map tool.\n\n' +
              'Instructions:\n' +
              '1. Allow pop-ups in your browser\n' +
              '2. Click the button again\n' +
              '3. Draw your land boundaries\n' +
              '4. Download the map photo\n' +
              '5. Upload it in the photo section below');
    }
}
</script>
<?php include 'includes/footer.html'; ?>
</body>
</html>