<?php
include 'includes/header.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Admin") {
    header("Location: ../no_access.php");
    exit();
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Add this to includes/header.php before closing </head> tag -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Page Header -->
<section class="page-header padding bg-grey">
    <div class="container">
        <div class="row d-flex align-items-center">
            <div class="col-lg-12">
                <h1>Service Requests Management</h1>
                <p>Review and respond to customer service requests</p>

            </div>
            
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="padding">
    <div class="container">
        <!-- Filter Section -->
        <div class="row mb-30">
            <div class="col-md-12">
                <div class="service-item box-shadow padding-15">
                    <div class="row">
                        <div class="col-md-4 padding-10">
                            <label style="font-weight: 600; margin-bottom: 5px; display: block;">Filter by Client Name</label>
                            <input type="text" class="form-control" id="clientNameFilter" placeholder="Search by client name...">
                        </div>
                        <div class="col-md-3 padding-10">
                            <label style="font-weight: 600; margin-bottom: 5px; display: block;">Filter by Service Type</label>
                            <select class="form-control" id="serviceTypeFilter">
                                <option value="">All Services</option>
                               <?php
                                 include 'includes/connect.php';

                                 $resultat = mysqli_query($con,"SELECT * FROM service");
              
                                  while($row = mysqli_fetch_array($resultat)) {
                                  echo "<option value='".$row['service_id']."'>".	
                                  $row['service_name'] ;
                                 echo "</option>";
                                 }


                                  mysqli_close($con);
                                ?> 
                            </select>
                        </div>
                        <div class="col-md-3 padding-10">
                            <label style="font-weight: 600; margin-bottom: 5px; display: block;">Filter by Date Range</label>
                            <select class="form-control" id="dateRangeFilter">
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="3months">Last 3 Months</option>
                            </select>
                        </div>
                        <div class="col-md-2 padding-10" style="display: flex; align-items: flex-end;">
                            <button class="default-btn" id="applyFilters" style="width: 100%; padding: 10px;">
                                <i class="fas fa-filter"></i> Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="row">
            <div class="col-12">
                <div class="tab-navigation mb-40">
                    <button class="tab-btn active" data-status="pending">
                        <i class="fas fa-clock"></i> Pending Requests
                    </button>
                    <button class="tab-btn" data-status="approved">
                        <i class="fas fa-check-circle"></i> Approved
                    </button>
                    <button class="tab-btn" data-status="rejected">
                        <i class="fas fa-times-circle"></i> Rejected
                    </button>
                    <button class="tab-btn" data-status="services">
                        <i class="fas fa-times-circle"></i> Services
                    </button>
                </div>
            </div>
        </div>

        <!-- Requests Table Container -->
        <div class="row">
            <div class="col-12">
                <div id="userTable"></div>
            </div>
        </div>
    </div>
</section>

<!-- Response Modal -->
<div class="modal fade" id="respondModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title">Land Details & Response</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      
      <!-- Modal Body -->
      <div class="modal-body">

        <!-- Land Info (loaded via PHP/AJAX) -->
        <div id="landDetails" class="mb-4">
          <p>Loading land information...</p>
        </div>

        <!-- Admin Form -->
        <div class="response-form-section">


          <div class="mb-3">
            <label for="requestPrice" class="form-label">Price (USD)</label>
            <input type="number" class="form-control" id="requestPrice" placeholder="Enter price" min="0" step="0.01" required>
          </div>

          <div class="mb-3">
            <label for="rejectionReason" class="form-label">Rejection reason</label>
            <input type="text" class="form-control" id="rejectionReason"  min="0" step="0.01" >
          </div>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer d-flex justify-content-between">
        <button type="button" class="btn btn-danger" id="denyRequest">
          <i class="fas fa-times"></i> Deny Request
        </button>
        <button type="button" class="btn btn-success" id="approveRequest">
          <i class="fas fa-check"></i> Approve Request
        </button>
      </div>

    </div>
  </div>
</div>


<!-- approved details Modal -->
<div class="modal fade" id="approvedModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title">Land Details & Response</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      
      <!-- Modal Body -->
      <div class="modal-body">

        <!-- Land Info (loaded via PHP/AJAX) -->
        <div id="approvedLandDetails" class="mb-4">
          <p>Loading land information...</p>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>



<!-- Rejection Reason Modal -->
<div class="modal fade" id="rejectionReasonModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title">Rejection Reason</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <p id="rejectionReasonText">Loading reason...</p>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<!-- Edit Service Modal - Custom -->
<div id="editServiceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow: auto;">
  <div style="background: white; margin: 50px auto; max-width: 600px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div style="padding: 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
      <h5 style="margin: 0;">Service Info</h5>
      <button type="button" id="closeEditModal" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
    </div>
    <div style="padding: 20px;" id="editServiceModalBody">
      <!-- Content will load here -->
    </div>
    <div style="padding: 20px; border-top: 1px solid #ddd;">
      <button type="button" id="closeEditModalBtn" class="btn btn-secondary">Close</button>
    </div>
  </div>
</div>


<!-- Add Service Modal -->
<div id="addServiceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow: auto;">
  <div style="background: white; margin: 50px auto; max-width: 600px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div style="padding: 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
      <h5 style="margin: 0;">Add New Service</h5>
      <button type="button" id="closeAddModal" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
    </div>
    <div style="padding: 20px;">
      
      <div class="mb-3">
        <label>Service Name *</label>
        <input type="text" id="add_service_name" class="form-control" placeholder="Enter service name" required>
      </div>

      <div class="mb-3">
        <label>Min Price *</label>
        <input type="number" id="add_min_price" class="form-control" placeholder="Enter minimum price" step="0.01" min="0" required>
      </div>

      <div class="mb-3">
        <label>Max Price *</label>
        <input type="number" id="add_max_price" class="form-control" placeholder="Enter maximum price" step="0.01" min="0" required>
      </div>

      <div class="mb-3">
        <label>Description *</label>
        <textarea id="add_description" class="form-control" rows="4" placeholder="Enter service description" required></textarea>
      </div>

      <button type="button" class="btn btn-success" id="saveNewServiceBtn">
        <i class="fas fa-plus"></i> Add Service
      </button>
      
    </div>
    <div style="padding: 20px; border-top: 1px solid #ddd;">
      <button type="button" id="closeAddModalBtn" class="btn btn-secondary">Close</button>
    </div>
  </div>
</div>
 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>

/* Ensure proper page structure and footer positioning */
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}

body {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* Main content should take up available space */
section.padding {
  flex: 1 0 auto;
  min-height: 70vh;
}

/* Ensure footer stays at bottom */
footer {
  flex-shrink: 0;
  margin-top: auto;
  position: relative;
  z-index: 1;
}

/* Fix Bootstrap modal z-index issues */
.modal-backdrop {
  z-index: 1040 !important;
}

.modal {
  z-index: 1050 !important;
}

.modal-dialog {
  z-index: 1051 !important;
}

/* Custom modals should be even higher */
#editServiceModal,
#addServiceModal {
  z-index: 1060 !important;
}

/* Ensure modal content is clickable */
.modal-content {
  position: relative;
  z-index: 1052 !important;
  pointer-events: auto !important;
}

.modal-body,
.modal-header,
.modal-footer {
  pointer-events: auto !important;
}

/* Prevent modals from affecting page layout */
.modal-open {
  overflow: hidden;
}

.modal-open body {
  padding-right: 0 !important;
}

/* Tab Navigation Styling */
.tab-navigation {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.tab-btn {
    background: #fff;
    border: 2px solid #e0e0e0;
    padding: 12px 25px;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    color: #666;
    transition: all 0.3s ease;
}

.tab-btn:hover {
    border-color: #ff7607;
    color: #ff7607;
}

.tab-btn.active {
    background: #ff7607;
    border-color: #ff7607;
    color: #fff;
}

.tab-btn i {
    margin-right: 8px;
}

/* Request Cards Styling */
.request-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.request-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.status-in-progress {
    background: #cce5ff;
    color: #004085;
}

.status-planning {
    background: #e7e7e7;
    color: #383d41;
}

.status-on-hold {
    background: #fff3cd;
    color: #856404;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

/* Land Details Styling */
.land-details-section {
    border-left: 4px solid #ff7607;
}

.land-details-section h5 {
    color: #ff7607;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 10px;
}

.land-details-section p {
    margin-bottom: 8px;
    font-size: 14px;
}

.land-details-section strong {
    color: #495057;
    min-width: 120px;
    display: inline-block;
}
</style>


<script>
// 🔹 Load requests via AJAX (with filters)
function loadRequests(status = 'pending') {
    $.ajax({
        url: 'admin-services-show-ajax.php',
        type: 'POST',
        data: { 
            status: status,
            clientName: $('#clientNameFilter').val(),
            serviceType: $('#serviceTypeFilter').val(),
            dateRange: $('#dateRangeFilter').val()
        },
        success: function(response) {
            $('#userTable').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error loading requests:', error);
            $('#userTable').html(
                '<div class="col-12"><p class="text-center text-danger p-3">Error loading requests.</p></div>'
            );
        }
    });
}

// 🔹 Tab Navigation (Pending / Approved / Rejected)
$(document).on('click', '.tab-btn', function() {
    $('.tab-btn').removeClass('active');
    $(this).addClass('active');
    const status = $(this).data('status');
    loadRequests(status);
});

// 🔹 Apply Filters
$(document).on('click', '#applyFilters', function() {
    const activeTab = $('.tab-btn.active').data('status');
    loadRequests(activeTab);
});

// 🔹 Load pending requests on page load
$(document).ready(function() {
    loadRequests('pending');
});

$('#serviceTypeFilter, #dateRangeFilter').on('change', function() {
    const activeTab = $('.tab-btn.active').data('status'); // ✅ Get current tab
     loadRequests(activeTab);
});

// for land info in the form 
let currentRequestId = null;

$(document).on('click', '.respond-btn', function() {
    currentRequestId = $(this).data('id');   
    $('#respondModal').modal('show');

        // Clear previous input
    $('#requestPrice').val('');
    $('#rejectionReason').val('');

    $('#landDetails').html('<p>Loading...</p>');  

    $.post('admin-services-fetch-land-info.php', { request_id: currentRequestId }, function(response) {
        $('#landDetails').html(response);  
    }).fail(function() {
        $('#landDetails').html('<p class="text-danger">Failed to load land info.</p>');
    });
});

$('#approveRequest').click(function() {
    let price = $('#requestPrice').val();
    if (!price) {
        alert("Please enter a price to approve this request.");
        $('#requestPrice') 
        return; // stop execution
    }

    $.post('admin-services-status-update-query.php', {
        request_id: currentRequestId,
        status: 'approved',
        price: price,
         
    }, function(response) {
        alert(response);
        $('#respondModal').modal('hide');
        loadRequests();  
    });
});

$('#denyRequest').click(function() {
    let notes = $('#rejectionReason').val();
     if (!notes) {
        alert("Please enter a rejection reason.");
        $('#rejectionReason')
        return; // stop execution
    }
    $.post('admin-services-status-update-query.php', {
        request_id: currentRequestId,
        status: 'rejected',
        notes: notes
    }, function(response) {
        alert(response);
        $('#respondModal').modal('hide');
        loadRequests();  
    });
});



// open rejected modal
$(document).on('click', '.viewReasonBtn', function() {
    let id = $(this).data('id');

    // Move modal to body if not already there
    if ($('#rejectionReasonModal').parent()[0].tagName !== 'BODY') {
        $('#rejectionReasonModal').appendTo('body');
    }

    $('#rejectionReasonModal').modal('show');

    $.post('admin-services-fetch-rejection-reason.php', { 
        id: id 
    }, function(response) {
        $('#rejectionReasonText').html(response);  
    }).fail(function() {
        $('#rejectionReasonText').html('<p class="text-danger">Failed to load reason.</p>');
    });
});



// for land info in the form approved 

$(document).on('click', '.viewDetailsBtn', function() {
    let id = $(this).data('id');   
    $('#approvedModal').modal('show');

       
    $('#approvedLandDetails').html('<p>Loading...</p>');  

    $.post('admin-services-fetch-land-info.php', { request_id: id }, function(response) {
        $('#approvedLandDetails').html(response);  
    }).fail(function() {
        $('#approvedLandDetails').html('<p class="text-danger">Failed to load land info.</p>');
    });
});


// Open Edit Modal
$(document).on('click', '.editServiceBtn', function() {
    let serviceId = $(this).data('id');
    
    // Move modal to body if not already there
    if ($('#editServiceModal').parent()[0].tagName !== 'BODY') {
        $('#editServiceModal').appendTo('body');
    }//If the modal is trapped inside another element, move it directly under the <body> tag"
    
    $('#editServiceModal').fadeIn(300);
    $('body').css('overflow', 'hidden'); //"Lock the page so the user can't scroll while the modal is open"
                                         //Why? Forces focus on the modal
    $('#editServiceModalBody').html('<p class="text-center">Loading...</p>'); //Put a 'Loading...' message inside the modal while we fetch data
    
    $.post('admin-services-fetch-service-info.php', 
    { service_id: serviceId }, function(data) {
        $('#editServiceModalBody').html(data);//"When data comes back, replace 'Loading...' with the actual form"
    }).fail(function() {
        $('#editServiceModalBody').html('<p class="text-danger">Error loading service info.</p>');
    });
});

// Close Edit Modal
$(document).on('click', '#closeEditModal, #closeEditModalBtn', function() {
    $('#editServiceModal').fadeOut(300);
    $('body').css('overflow', ''); //Unlock the page so the user can scroll again
});

 

// Save Service Changes
$(document).on('click', '#saveServiceBtn', function() {
    $.post('admin-services-edit-query.php', {
        service_id: $('#edit_service_id').val(),
        status: $('#edit_service_status').val(),
        service_name: $('#edit_service_name').val(),
        min_price: $('#edit_min_price').val(),
        max_price: $('#edit_max_price').val(),
        description: $('#edit_description').val()
    }, function(response) {
        alert(response);
        $('#editServiceModal').fadeOut(300);
        $('body').css('overflow', '');
        loadRequests('services');
    }).fail(function(xhr, status, error) {
        alert('Error saving service: ' + error);
    });
});

// Delete Service
$(document).on('click', '.deleteServiceBtn', function() {
    const serviceId = $(this).data('id');
    if(!confirm("Are you sure you want to deactivate this service?")) return;

    $.post('admin-services-delete-query.php', { service_id: serviceId }, function(response) {
        alert(response);  
        loadRequests('services');  
    });
});


// Open Add Service Modal
$(document).on('click', '#addServiceBtn', function() {
    if ($('#addServiceModal').parent()[0].tagName !== 'BODY') {
        $('#addServiceModal').appendTo('body');
    }
    
    $('#addServiceModal').fadeIn(300);
    $('body').css('overflow', 'hidden');
    
    // Clear all fields
    $('#add_service_name').val('');
    $('#add_min_price').val('');
    $('#add_max_price').val('');
    $('#add_description').val('');
});

// Close Add Service Modal
$(document).on('click', '#closeAddModal, #closeAddModalBtn', function() {
    $('#addServiceModal').fadeOut(300);
    $('body').css('overflow', '');
});



// Save New Service
$(document).on('click', '#saveNewServiceBtn', function() {
    let serviceName = $('#add_service_name').val().trim();
    let minPrice = $('#add_min_price').val();
    let maxPrice = $('#add_max_price').val();
    let description = $('#add_description').val().trim();
    
    
    $.post('admin-services-add-service-query.php', {
        service_name: serviceName,
        min_price: minPrice,
        max_price: maxPrice,
        description: description
    }, function(response) {
        alert(response);
        $('#addServiceModal').fadeOut(300);
        $('body').css('overflow', '');
        loadRequests('services'); // Refresh the services list
    }).fail(function(xhr, status, error) {
        alert('Error adding service: ' + error);
    });
});




</script>


<?php include 'includes/footer.html'; ?>