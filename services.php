<?php
include 'includes/connect.php';
include 'includes/header.php';

$success_message = '';
$error_message = '';
$selectedService = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;

// Get selected service details if service_id is provided
$selectedServiceName = '';
if ($selectedService) {
  $service_query = "SELECT service_name FROM service WHERE service_id = $selectedService";
  $service_result = mysqli_query($con, $service_query);
  if ($service_row = mysqli_fetch_assoc($service_result)) {
    $selectedServiceName = $service_row['service_name'];
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'] ?? null;

  if (!$user_id) {
    $error_message = "Error: User not logged in. Please log in and try again.";
  } else {
    $service_id = mysqli_real_escape_string($con, $_POST['service_id'] ?? '');
    $full_name = mysqli_real_escape_string($con, $_POST['full_name'] ?? '');
    $email = mysqli_real_escape_string($con, $_POST['email'] ?? '');
    $phone = mysqli_real_escape_string($con, $_POST['phone'] ?? '');
    $location = mysqli_real_escape_string($con, $_POST['location'] ?? '');
    $area_size = mysqli_real_escape_string($con, $_POST['area_size'] ?? '');
    $land_number = mysqli_real_escape_string($con, $_POST['land_number'] ?? '');
    $land_type = mysqli_real_escape_string($con, $_POST['land_type'] ?? '');
    $longitude = mysqli_real_escape_string($con, $_POST['longitude'] ?? '');
    $latitude = mysqli_real_escape_string($con, $_POST['latitude'] ?? '');
    $land_description = mysqli_real_escape_string($con, $_POST['land_description'] ?? '');
    $area_numeric = !empty($area_size) ? (float)$area_size : 0;
    $slope = mysqli_real_escape_string($con, $_POST['slope'] ?? '');
    $distance_from_office = mysqli_real_escape_string($con, $_POST['distance_from_office'] ?? '');
    $terrain_factor = mysqli_real_escape_string($con, $_POST['terrain_factor'] ?? '');
    $elevation_avg = mysqli_real_escape_string($con, $_POST['elevation_avg'] ?? '');
    $elevation_max = mysqli_real_escape_string($con, $_POST['elevation_max'] ?? '');
    $elevation_min = mysqli_real_escape_string($con, $_POST['elevation_min'] ?? '');
    $slope = mysqli_real_escape_string($con, $_POST['slope'] ?? '');

    try {
      // Get or create client record
      $get_client = "SELECT client_id FROM client WHERE user_id = '$user_id'";
      $client_result = mysqli_query($con, $get_client);

      if (mysqli_num_rows($client_result) > 0) {
        $client_row = mysqli_fetch_assoc($client_result);
        $client_id = $client_row['client_id'];
      } else {
        $insert_client = "INSERT INTO client (user_id, address) VALUES ('$user_id', '$location')";
        if (mysqli_query($con, $insert_client)) {
          $client_id = mysqli_insert_id($con);
        } else {
          throw new Exception("Failed to create client record.");
        }
      }

      // Check if land already exists, otherwise insert
      $check_land = "SELECT land_id FROM land WHERE land_number = '$land_number'";
      $land_result = mysqli_query($con, $check_land);

      if (mysqli_num_rows($land_result) > 0) {
    $land_row = mysqli_fetch_assoc($land_result);
    $land_id = $land_row['land_id'];
    
    // Check if ownership already exists
    $check_ownership = "SELECT * FROM owns_client_land 
                        WHERE client_id = '$client_id' AND land_id = '$land_id'";
    $ownership_result = mysqli_query($con, $check_ownership);
    
    if (mysqli_num_rows($ownership_result) == 0) {
        // Ownership doesn't exist, create it
        $insert_ownership = "INSERT INTO owns_client_land (client_id, land_id) 
                             VALUES ('$client_id', '$land_id')";
        mysqli_query($con, $insert_ownership);
    }
} else {
        $insert_land = "INSERT INTO land 
    (land_address, land_area, land_type, coordinates_latitude, coordinates_longitude, 
     general_description, specific_location_notes, land_number,
     elevation_avg, elevation_max, elevation_min, slope, terrain_factor, distance_from_office) 
    VALUES 
    ('$location', '$area_numeric', '$land_type', $latitude, $longitude, 
     '$land_description', ' ', '$land_number',
     " . (!empty($elevation_avg) ? "'$elevation_avg'" : "NULL") . ", 
     " . (!empty($elevation_max) ? "'$elevation_max'" : "NULL") . ", 
     " . (!empty($elevation_min) ? "'$elevation_min'" : "NULL") . ", 
     " . (!empty($slope) ? "'$slope'" : "NULL") . ", 
     " . (!empty($terrain_factor) ? "'$terrain_factor'" : "NULL") . ", 
     " . (!empty($distance_from_office) ? "'$distance_from_office'" : "NULL") . ")";

        if (mysqli_query($con, $insert_land)) {
          $land_id = mysqli_insert_id($con);
          $insert_ownership = "INSERT INTO owns_client_land (client_id, land_id) 
                         VALUES ('$client_id', '$land_id')";
                         if (!mysqli_query($con, $insert_ownership)) {
        throw new Exception("Failed to link client with land ownership.");
    }
        } else {
          throw new Exception("Failed to insert land information.");
        }
      }
      // Insert into service_request
      $status = 'Pending';
      $request_date = date('Y-m-d');
      $insert_request = "INSERT INTO service_request 
                (status, request_date, approval_status, rejection_reason, client_id, service_id, land_id, project_id) 
                VALUES 
                ('$status','$request_date', NULL, NULL, '$client_id', '$service_id', '$land_id', NULL)";

      if (mysqli_query($con, $insert_request)) {
        $request_id = mysqli_insert_id($con);

        // ===================================================
        // HANDLE FILE UPLOADS
        // ===================================================
        $upload_dir = 'uploads/service_requests/';

        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
          mkdir($upload_dir, 0777, true);
        }

        // Function to get or create document type
        function getOrCreateDocumentType($con, $type_name, $description)
        {
          $type_name = mysqli_real_escape_string($con, $type_name);
          $description = mysqli_real_escape_string($con, $description);

          // Check if document type exists
          $check_type = "SELECT document_type_id FROM document_type WHERE type_name = '$type_name'";
          $result = mysqli_query($con, $check_type);

          if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['document_type_id'];
          } else {
            // Create new document type
            $insert_type = "INSERT INTO document_type (type_name, description) VALUES ('$type_name', '$description')";
            if (mysqli_query($con, $insert_type)) {
              return mysqli_insert_id($con);
            }
          }
          return null;
        }

        // Define document types based on service
        $file_configs = [];

        switch ($service_id) {
          case 1: // Boundary Survey
            $file_configs = [
              // Required
              'title_deed' => [
                'type_name' => 'Title Deed',
                'description' => 'Title deed or ownership certificate'
              ],
              'authorization_letter' => [
                'type_name' => 'Authorization Letter',
                'description' => 'Owner authorization letter (if engineer acts on behalf)'
              ],
              // Optional
              'optional_files' => [
                'type_name' => 'Optional Documents',
                'description' => 'Previous survey plan, property registration extract'
              ]
            ];
            break;

          case 2: // Topographic Survey
            $file_configs = [
              // Required
              'title_deed' => [
                'type_name' => 'Title Deed',
                'description' => 'Title deed or ownership certificate'
              ],
              // Optional
              'optional_files' => [
                'type_name' => 'Optional Documents',
                'description' => 'Site plan, architectural drawings, previous survey data'
              ]
            ];
            break;

          case 3: // Construction Staking
            $file_configs = [
              // Required
              'building_permit' => [
                'type_name' => 'Building Permit',
                'description' => 'Approved building permit'
              ],
              'title_deed' => [
                'type_name' => 'Title Deed',
                'description' => 'Title deed or ownership certificate'
              ],
              // Optional
              'optional_files' => [
                'type_name' => 'Optional Documents',
                'description' => 'Construction drawings, site plan'
              ]
            ];
            break;

          case 4: // Subdivision Planning
            $file_configs = [
              // Required
              'parent_title_deed' => [
                'type_name' => 'Parent Title Deed',
                'description' => 'Title deed of parent parcel'
              ],
              'authorization_letter' => [
                'type_name' => 'Authorization Letter',
                'description' => 'Owner authorization letter'
              ],
              // Optional
              'optional_files' => [
                'type_name' => 'Optional Documents',
                'description' => 'Subdivision layout (if prepared), previous survey plan'
              ]
            ];
            break;

          case 5: // GIS Mapping
            $file_configs = [
              // Required
              'title_deed' => [
                'type_name' => 'Title Deed',
                'description' => 'Title deed or ownership certificate (to prove authority to request data)'
              ],
              // Optional
              'optional_files' => [
                'type_name' => 'Optional Documents',
                'description' => 'Existing digital data (shapefiles, GeoJSON, CAD), previous survey maps'
              ]
            ];
            break;
        }

        // Process single file uploads (required documents)
        foreach ($file_configs as $field_name => $config) {
          if ($field_name === 'optional_files') continue; // Skip, handled separately

          if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] == 0) {
            $file_name = $_FILES[$field_name]['name'];
            $file_tmp = $_FILES[$field_name]['tmp_name'];
            $file_size = $_FILES[$field_name]['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

            if ($file_size > 10485760) continue; // Skip if > 10MB

            if (in_array($file_ext, $allowed)) {
              $document_type_id = getOrCreateDocumentType($con, $config['type_name'], $config['description']);

              if ($document_type_id) {
                $new_filename = $request_id . '_' . $field_name . '_' . time() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                  $insert_doc = "INSERT INTO submitted_document 
                                        (upload_date, file_name, file_path, document_type_id) 
                                        VALUES 
                                        (NOW(), '$new_filename', '$upload_path', '$document_type_id')";

                  if (mysqli_query($con, $insert_doc)) {
                    $document_id = mysqli_insert_id($con);

                    $link_doc = "INSERT INTO has_servicerequest_submitteddocument 
                                            (service_request_id, submitted_document_id) 
                                            VALUES 
                                            ('$request_id', '$document_id')";

                    mysqli_query($con, $link_doc);
                  }
                }
              }
            }
          }
        }

        // Process multiple optional files
        if (isset($_FILES['optional_files']) && !empty($_FILES['optional_files']['name'][0])) {
          $optional_config = $file_configs['optional_files'];
          $document_type_id = getOrCreateDocumentType($con, $optional_config['type_name'], $optional_config['description']);

          $file_count = count($_FILES['optional_files']['name']);

          for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['optional_files']['error'][$i] == 0) {
              $file_name = $_FILES['optional_files']['name'][$i];
              $file_tmp = $_FILES['optional_files']['tmp_name'][$i];
              $file_size = $_FILES['optional_files']['size'][$i];
              $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

              $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'shp', 'geojson', 'dwg', 'dxf'];

              if ($file_size > 10485760) continue;

              if (in_array($file_ext, $allowed)) {
                $new_filename = $request_id . '_optional_' . $i . '_' . time() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                  $insert_doc = "INSERT INTO submitted_document 
                                        (upload_date, file_name, file_path, document_type_id) 
                                        VALUES 
                                        (NOW(), '$new_filename', '$upload_path', '$document_type_id')";

                  if (mysqli_query($con, $insert_doc)) {
                    $document_id = mysqli_insert_id($con);

                    $link_doc = "INSERT INTO has_servicerequest_submitteddocument 
                                            (service_request_id, submitted_document_id) 
                                            VALUES 
                                            ('$request_id', '$document_id')";

                    mysqli_query($con, $link_doc);
                  }
                }
              }
            }
          }
        }

        $success_message = "Your service request has been submitted successfully! Our team will contact you soon.";
      } else {
        throw new Exception("Failed to submit service request. Please try again.");
      }
    } catch (Exception $e) {
      $error_message = $e->getMessage();
    }
  }
}
?>

<!doctype html>
<html class="no-js" lang="en">

<head>
  <style>
    .service-item {
      transition: all 0.3s ease;
      text-decoration: none;
      display: block;
    }

    .service-item:hover {
      transform: translateY(-5px);
    }

    .service-item.selected-service {
      border: 3px solid #ff7607;
      background: #fff5ed;
    }

    .service-check {
      display: none;
      color: #ff7607;
      font-weight: bold;
      font-size: 18px;
      margin-top: 10px;
    }

    .service-item.selected-service .service-check {
      display: block;
    }

    .info-box {
      background: #e8f4f8;
      border-left: 4px solid #2196f3;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
    }

    .info-box i {
      color: #2196f3;
      margin-right: 8px;
    }

    .map-btn {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .map-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .map-data-display {
      background: #f8f9fa;
      border: 2px solid #e9ecef;
      border-radius: 8px;
      padding: 20px;
      margin-top: 20px;
    }

    .map-data-item {
      padding: 10px;
      background: white;
      border-radius: 5px;
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
    }

    .map-data-label {
      font-weight: 600;
      color: #495057;
    }

    .map-data-value {
      color: #0066cc;
      font-weight: 500;
    }

    /* Fix select dropdown visibility */
    select.form-control {
      color: #333;
      font-size: 14px;
      padding: 10px;
      background-color: #fff;
    }

    select.form-control option {
      color: #333;
      font-size: 14px;
      padding: 8px;
      background-color: #fff;
    }

    select.form-control:focus {
      color: #333;
      background-color: #fff;
    }
  </style>
</head>

<body>
  <div class="site-preloader-wrap">
    <div class="spinner"></div>
  </div>

  <?php if ($success_message): ?>
    <div class="alert alert-success" style="position: fixed; top: 100px; left: 50%; transform: translateX(-50%); z-index: 9999; width: 80%; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <strong>Success!</strong> <?php echo $success_message; ?>
      <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    <script>
      setTimeout(function() {
        window.location.href = 'services.php';
      }, 3000);
    </script>
  <?php endif; ?>

  <?php if ($error_message): ?>
    <div class="alert alert-danger" style="position: fixed; top: 100px; left: 50%; transform: translateX(-50%); z-index: 9999; width: 80%; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <strong>Error!</strong> <?php echo $error_message; ?>
      <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
  <?php endif; ?>

  <!-- Page Header -->
  <section class="page-header padding">
    <div class="container">
      <div class="page-content text-center">
        <h2>Request Our Surveying Services</h2>
        <p>Choose a surveying service to get started</p>
      </div>
    </div>
  </section>

  <!-- Service Options (Always visible) -->
  <section class="service-section padding bg-grey">
    <div class="container">
      <div class="section-heading text-center mb-40">
        <span>Our Services</span>
        <h2>What type of surveying do you need?</h2>
        <p>Select one of our professional land surveying services below</p>
      </div>

      <div class="row" id="service-options">
        <?php
        $icons = [
          "flaticon-factory",
          "flaticon-worker",
          "flaticon-conveyor"
        ];

        $query  = "SELECT * FROM service";
        $result = mysqli_query($con, $query);

        while ($row = mysqli_fetch_assoc($result)) {
          $icon = $icons[array_rand($icons)];
          $isSelected = ($selectedService == $row['service_id']) ? 'selected-service' : '';
        ?>

          <div class="col-lg-4 col-md-6 padding-15">
            <a href="services.php?service_id=<?php echo $row['service_id']; ?>" style="text-decoration:none; color:inherit;">
              <div class="service-item box-shadow text-center <?php echo $isSelected; ?>">
                <div class="service-icon">
                  <i class="<?php echo $icon; ?>"></i>
                </div>

                <h3><?php echo htmlspecialchars($row['service_name']); ?></h3>
                <p><?php echo htmlspecialchars($row['description']); ?></p>

                <div class="service-check">✓ Selected</div>
              </div>
            </a>
          </div>

        <?php } ?>
      </div>
    </div>
  </section>

  <!-- Request Form (Show only if service selected) -->
  <?php if ($selectedService): ?>
    <section class="contact-section padding" id="request-form-section">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8 col-md-10">
            <div class="section-heading text-center mb-40">
              <span>Almost Done</span>
              <h2>Complete Your <span style="color: #ff7607;"><?php echo htmlspecialchars($selectedServiceName); ?></span> Request</h2>
              <p>Fill in the details below and our team will reach out to you shortly</p>
            </div>

            <div class="contact-form box-shadow" style="background: #fff; padding: 40px; border-radius: 5px;">
              <form method="post" class="form-horizontal" id="service-request-form" enctype="multipart/form-data">
                <input type="hidden" name="service_id" value="<?php echo $selectedService; ?>">

                <!-- Full name & Email -->
                <div class="form-group colum-row row">
                  <div class="col-md-6">
                    <label>Full Name <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" name="full_name" placeholder="Your full name" required>
                  </div>
                  <div class="col-md-6">
                    <label>Email Address <span style="color: red;">*</span></label>
                    <input type="email" class="form-control" name="email" placeholder="Your email address" required>
                  </div>
                </div>

                <!-- Phone -->
                <div class="form-group colum-row row">
                  <div class="col-md-6">
                    <label>Phone Number <span style="color: red;">*</span></label>
                    <input type="tel" class="form-control" name="phone" placeholder="Your phone number" required>
                  </div>
                </div>

                <!-- Location -->
                <div class="form-group colum-row row">
                  <div class="col-md-12">
                    <label>Survey Location <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" name="location" placeholder="Address or coordinates of the property" required>
                  </div>
                </div>
                <!-- Area Size -->
                <div class="form-group colum-row row">
                  <div class="col-md-12">
                    <label>Area Size (m²)</label>
                    <input type="number" class="form-control" name="area_size" id="area_size" placeholder="Enter exact area if known (optional)" step="0.01">
                    <small style="color: #6c757d;">
                      <strong>Optional:</strong> If you know the exact area of your land, enter it here. Otherwise, leave it empty and we'll use the map calculation.
                    </small>
                    <div id="map-area-display" style="display: none; margin-top: 8px; padding: 8px; background: #e7f3ff; border-radius: 4px; font-size: 14px;">
                      <strong>Map calculated area:</strong> <span id="map-area-value">0</span> m²
                    </div>
                  </div>
                </div>
                <!-- coordinates -->
                <div class="form-group colum-row row">
                  <div class="col-md-12">
                    <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">
                      Land Location & Mapping
                    </label>
                    <button type="button" onclick="openMapTool()" class="map-btn">
                      <i class="fa fa-map-marked-alt"></i>
                      Open Interactive Map Tool
                    </button>
                    <p style="font-size: 13px; color: #6c757d; margin-top: 10px;">
                      Use our interactive map to mark your land boundaries. The system will automatically calculate coordinates, area, elevation, slope, and distance from our office.
                    </p>
                  </div>
                </div>

                <div class="form-group colum-row row" style="margin-top: 20px;">
                  <div class="col-md-6">
                    <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">
                      Latitude <small style="color: #6c757d;">(from map or manual)</small>
                    </label>
                    <input type="number" class="form-control" id="latitude" name="latitude" placeholder="e.g., 34.0522" step="0.000001" required>
                  </div>
                  <div class="col-md-6">
                    <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">
                      Longitude <small style="color: #6c757d;">(from map or manual)</small>
                    </label>
                    <input type="number" class="form-control" id="longitude" name="longitude" placeholder="e.g., -118.2437" step="0.000001" required>
                    <button type="button" class="btn btn-sm btn-secondary" id="get-location-btn" style="margin-top: 5px;">
                      <i class="fa fa-location-arrow"></i> Use My Current Location
                    </button>
                  </div>
                </div>

                <!-- Hidden fields for additional map data -->
                  <input type="hidden" name="elevation_avg" id="elevation_avg">
                  <input type="hidden" name="elevation_max" id="elevation_max">
                  <input type="hidden" name="elevation_min" id="elevation_min">
                  <input type="hidden" name="slope" id="slope">
                  <input type="hidden" name="terrain_factor" id="terrain_factor">
                  <input type="hidden" name="distance_from_office" id="distance_from_office">

                <!-- Map data display (will be shown after using map tool) -->
                <div id="map-data-display" style="display: none;"></div>

                <!-- Land number -->
                <div class="form-group colum-row row">
                  <div class="col-md-12">
                    <label>Land Number <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" name="land_number" placeholder="e.g. Zahle - 123/45" required>
                  </div>
                </div>

                <!-- Land type -->
                <div class="form-group colum-row row">
                  <div class="col-md-6">
                    <label>Land Type <span style="color: red;">*</span></label>
                    <select class="form-control" name="land_type" required>
                      <option value="">Select Land Type</option>
                      <option value="Residential">Residential</option>
                      <option value="Agricultural">Agricultural</option>
                      <option value="Commercial">Commercial</option>
                      <option value="Industrial">Industrial</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>
                </div>

                <!-- Land description -->
                <div class="form-group colum-row row">
                  <div class="col-md-12">
                    <label>General Description of the Land</label>
                    <textarea class="form-control" name="land_description" rows="3"
                      placeholder="Example: Flat area with nearby access road, has trees, etc."></textarea>
                  </div>
                </div>
                <hr style="margin: 30px 0;">
                <h4 style="margin-bottom: 20px;">Required Documents</h4>

                <!-- Service-specific REQUIRED file uploads -->
                <?php if ($selectedService == 1): // Boundary Survey 
                ?>
                  <div class="form-group colum-row row">
                    <div class="col-md-12">
                      <label>Title Deed / Ownership Certificate <span style="color: red;">*</span></label>
                      <input type="file" class="form-control" name="title_deed" accept=".pdf,.jpg,.jpeg,.png" required>
                      <small style="color: #8d9aa8;">PDF, JPG, or PNG (Max 10MB)</small>
                    </div>
                  </div>
                  <div class="form-group colum-row row">
                    <div class="col-md-12">
                      <label>Owner's Authorization Letter <span style="color: red;">*</span></label>
                      <input type="file" class="form-control" name="authorization_letter" accept=".pdf,.jpg,.jpeg,.png" required>
                      <small style="color: #8d9aa8;">Required if engineer acts on behalf - PDF, JPG, or PNG (Max 10MB)</small>
                    </div>
                  </div>

                <?php elseif ($selectedService == 2): // Topographic Survey 
                ?>
                  <div class="form-group colum-row row">
                    <div class="col-md-12">
                      <label>Title Deed / Ownership Certificate <span style="color: red;">*</span></label>
                      <input type="file" class="form-control" name="title_deed" accept=".pdf,.jpg,.jpeg,.png" required>
                      <small style="color: #8d9aa8;">PDF, JPG, or PNG (Max 10MB)</small>
                    </div>
                  </div>

                <?php elseif ($selectedService == 3): // Construction Staking 
                ?>
                  <div class="form-group colum-row row">
                    <div class="col-md-12">
                      <label>Approved Building Permit <span style="color: red;">*</span></label>
                      <input type="file" class="form-control" name="building_permit" accept=".pdf,.jpg,.jpeg,.png" required>
                      <small style="color: #8d9aa8;">PDF, JPG, or PNG (Max 10MB)</small>
                    </div>
                  </div>
                  <div class="form-group colum-row row">
                    <div class="col-md-12">
                      <label>Title Deed / Ownership Certificate <span style="color: red;">*</span></label>
                      <input type="file" class="form-control" name="title_deed" accept=".pdf,.jpg,.jpeg,.png" required>
                      <small style="color: #8d9aa8;">PDF, JPG, or PNG (Max 10MB)</small>
                    </div>
                  </div>

                <?php elseif ($selectedService == 4): // Subdivision Planning 
                ?>
                  <div class="form-group colum-row row">
                    <div class="col-md-12">
                      <label>Title Deed of Parent Parcel <span style="color: red;">*</span></label>
                      <input type="file" class="form-control" name="parent_title_deed" accept=".pdf,.jpg,.jpeg,.png" required>
                      <small style="color: #8d9aa8;">PDF, JPG, or PNG (Max 10MB)</small>
                    </div>
                  </div>
                  <div class="form-group colum-row row">
                    <div class="col-md-12">
                      <label>Owner's Authorization Letter <span style="color: red;">*</span></label>
                      <input type="file" class="form-control" name="authorization_letter" accept=".pdf,.jpg,.jpeg,.png" required>
                      <small style="color: #8d9aa8;">PDF, JPG, or PNG (Max 10MB)</small>
                    </div>
                  </div>

                <?php elseif ($selectedService == 5): // GIS Mapping 
                ?>
                  <div class="form-group colum-row row">
                    <div class="col-md-12">
                      <label>Title Deed / Ownership Certificate <span style="color: red;">*</span></label>
                      <input type="file" class="form-control" name="title_deed" accept=".pdf,.jpg,.jpeg,.png" required>
                      <small style="color: #8d9aa8;">PDF, JPG, or PNG (Max 10MB)</small>
                    </div>
                  </div>
                <?php endif; ?>

                <hr style="margin: 30px 0;">

                <!-- Optional Documents Section -->
                <div class="info-box">
                  <i class="fa fa-info-circle"></i>
                  <strong>Optional Documents:</strong> If you have any of the following documents available, uploading them can help <strong>reduce costs and speed up the service</strong>. However, if you don't have them, don't worry—we'll complete the service without any issues.
                </div>

                <h4 style="margin-bottom: 20px;">Optional Documents</h4>

                <?php if ($selectedService == 1): ?>
                  <p style="margin-bottom: 15px; color: #666;">
                    <strong>Helpful if available:</strong> Property registration extract, Cadastral map
                  </p>
                <?php elseif ($selectedService == 2): ?>
                  <p style="margin-bottom: 15px; color: #666;">
                    <strong>Helpful if available:</strong> Zoning map, Municipal approval letters, Site plan / architectural drawings
                  </p>
                <?php elseif ($selectedService == 3): ?>
                  <p style="margin-bottom: 15px; color: #666;">
                    <strong>Helpful if available:</strong> Municipal staking approval, Site coordinates
                  </p>
                <?php elseif ($selectedService == 4): ?>
                  <p style="margin-bottom: 15px; color: #666;">
                    <strong>Helpful if available:</strong> Municipality subdivision approval, Environmental clearance
                  </p>
                <?php elseif ($selectedService == 5): ?>
                  <p style="margin-bottom: 15px; color: #666;">
                    <strong>Helpful if available:</strong> Cadastral layers, Municipal datasets
                  </p>
                <?php endif; ?>

                <div class="form-group colum-row row">
                  <div class="col-md-12">
                    <label>Upload Optional Documents (Multiple files allowed)</label>
                    <input type="file" class="form-control" name="optional_files[]" multiple
                      accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.shp,.geojson,.dwg,.dxf">
                    <small style="color: #8d9aa8;">
                      You can select multiple files. Accepted: PDF, JPG, PNG, DOC, DOCX, SHP, GeoJSON, DWG, DXF (Max 10MB per file)
                    </small>
                  </div>
                </div>


                <!-- Terms -->
                <div class="form-group row">
                  <div class="col-md-12">
                    <div style="display: flex; align-items: center; margin: 25px 0;">
                      <input type="checkbox" id="consent" style="margin-right: 8px;" required>
                      <label for="consent" style="margin-bottom: 0; font-size: 14px;">
                        I agree to the <a href="#" style="color: #ff7607;">terms and conditions</a> <span style="color: red;">*</span>
                      </label>
                    </div>
                  </div>
                </div>

                <!-- Buttons -->
                <div class="form-group row">
                  <div class="col-md-12">
                    <button type="submit" class="default-btn" style="width: 100%; padding: 12px;">Submit Request</button>
                  </div>
                </div>
              </form>
            </div>

          </div>
        </div>
      </div>
    </section>
  <?php endif; ?>
  <script>
    // Store map data globally
    let mapData = {
      latitude: null,
      longitude: null,
      area: null,
      elevation_avg: null,
      slope: null,
      distance_from_office: null
    };

    // Open map in new window
    function openMapTool() {
      const mapWindow = window.open('map_tool.php', 'MapTool', 'width=1400,height=900');

      if (!mapWindow) {
        alert('Please allow pop-ups for this site to use the mapping tool.');
      }
    }

   // Listen for messages from map window
window.addEventListener('message', function(event) {
    // Verify the message is from our map tool
    if (event.data && event.data.type === 'MAP_DATA') {
        mapData = event.data.data;
        
        // Fill the visible form fields
        document.getElementById('latitude').value = parseFloat(mapData.latitude).toFixed(6);
        document.getElementById('longitude').value = parseFloat(mapData.longitude).toFixed(6);
        
        // Show map-calculated area but don't override manual input
        const areaInput = document.querySelector('input[name="area_size"]');
        const mapAreaDisplay = document.getElementById('map-area-display');
        const mapAreaValue = document.getElementById('map-area-value');
        
        if (mapData.area > 0) {
            mapAreaValue.textContent = Math.round(mapData.area);
            mapAreaDisplay.style.display = 'block';
            
            // Only fill the input if it's empty (user hasn't entered their own value)
            if (!areaInput.value || areaInput.value === '0') {
                areaInput.value = Math.round(mapData.area);
            }
        }
        
        // Fill hidden fields for database storage (elevation, slope, terrain, distance - all hidden from user)
        document.getElementById('elevation_avg').value = mapData.elevation_avg;
        document.getElementById('elevation_max').value = mapData.elevation_max;
        document.getElementById('elevation_min').value = mapData.elevation_min;
        document.getElementById('slope').value = mapData.slope;
        document.getElementById('terrain_factor').value = mapData.terrain_factor;
        document.getElementById('distance_from_office').value = mapData.distance_from_office;
        
        // Display the captured data (only coordinates and area)
        displayMapData();
        
        // Show success message
        showNotification('Map data loaded successfully! All fields have been filled.', 'success');
    }
});

function displayMapData() {
    let displayDiv = document.getElementById('map-data-display');
    displayDiv.style.display = 'block';
    displayDiv.className = 'map-data-display';
    
    let areaDisplay = '';
    if (mapData.area > 0) {
        areaDisplay = `
        <div class="map-data-item">
            <span class="map-data-label">📏 Map Calculated Area:</span>
            <span class="map-data-value">${Math.round(mapData.area)} m²</span>
        </div>`;
    }
    
    displayDiv.innerHTML = `
        <h4 style="margin: 20px 0 15px 0; color: #28a745;">
            <i class="fa fa-check-circle"></i> Map Data Captured Successfully
        </h4>
        <div class="map-data-item">
            <span class="map-data-label">📍 Coordinates:</span>
            <span class="map-data-value">${parseFloat(mapData.latitude).toFixed(6)}, ${parseFloat(mapData.longitude).toFixed(6)}</span>
        </div>
        ${areaDisplay}
        <button type="button" onclick="openMapTool()" class="btn btn-sm btn-secondary" style="margin-top: 10px;">
            <i class="fa fa-edit"></i> Edit Land Boundaries
        </button>
    `;
    // Note: Elevation, slope, terrain, and distance are all captured and stored in hidden fields but not displayed to user
}

    function showNotification(message, type) {
      const alertClass = type === 'success' ? 'alert-success' : 'alert-info';
      const notification = document.createElement('div');
      notification.className = `alert ${alertClass}`;
      notification.style.cssText = 'position: fixed; top: 100px; left: 50%; transform: translateX(-50%); z-index: 9999; width: 80%; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
      notification.innerHTML = `
        <strong>${type === 'success' ? 'Success!' : 'Info'}</strong> ${message}
        <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
    `;
      document.body.appendChild(notification);

      setTimeout(() => {
        notification.remove();
      }, 4000);
    }

    // Auto-detect location button
    document.addEventListener('DOMContentLoaded', function() {
      const getLocationBtn = document.getElementById('get-location-btn');
      if (getLocationBtn) {
        getLocationBtn.addEventListener('click', function() {
          const btn = this;
          const originalText = btn.innerHTML;

          if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
            return;
          }

          btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Getting location...';
          btn.disabled = true;

          navigator.geolocation.getCurrentPosition(
            function(position) {
              document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
              document.getElementById('longitude').value = position.coords.longitude.toFixed(6);

              btn.innerHTML = '<i class="fa fa-check"></i> Location Found!';
              setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
              }, 2000);
            },
            function(error) {
              let errorMsg = 'Unable to get location. ';
              switch (error.code) {
                case error.PERMISSION_DENIED:
                  errorMsg += 'Please allow location access.';
                  break;
                case error.POSITION_UNAVAILABLE:
                  errorMsg += 'Location information unavailable.';
                  break;
                case error.TIMEOUT:
                  errorMsg += 'Request timed out.';
                  break;
                default:
                  errorMsg += 'An unknown error occurred.';
              }
              alert(errorMsg);
              btn.innerHTML = originalText;
              btn.disabled = false;
            }, {
              enableHighAccuracy: true,
              timeout: 30000,
              maximumAge: 0
            }
          );
        });
      }
    });
  </script>
  <?php include 'includes/footer.html'; ?>

</body>

</html>