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
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-file-invoice-dollar"></i> Service Request Details & Pricing
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      
      <!-- Modal Body -->
      <div class="modal-body">
        <div class="row">
            <!-- Left Column: Land Details -->
            <div class="col-lg-7">
                <div id="landDetails" class="land-info-section">
                    <p class="text-center text-muted">
                        <i class="fas fa-spinner fa-spin"></i> Loading land information...
                    </p>
                </div>
            </div>

            <!-- Right Column: Price Calculation -->
            <div class="col-lg-5">
                <div class="pricing-section">
                    <div class="pricing-header">
                        <h5><i class="fas fa-calculator"></i> Price Calculation</h5>
                    </div>

                    <!-- Loading State -->
                    <div id="priceLoadingState" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                        <p class="text-muted">Calculating automated price...</p>
                    </div>

                    <!-- Price Breakdown (Hidden Initially) -->
                    <div id="priceBreakdownSection" style="display: none;">
                        <!-- Automated Price Display -->
                        <div class="alert alert-info mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-robot"></i> <strong>Automated Price</strong>
                                </div>
                                <div class="automated-price-value">
                                    $<span id="automatedPriceDisplay">0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Calculation Breakdown -->
                        <div class="calculation-breakdown">
                            <h6 class="breakdown-title">
                                <i class="fas fa-list-ul"></i> Price Breakdown
                                <button type="button" class="btn btn-sm btn-link" id="toggleBreakdown">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </h6>
                            <div id="breakdownDetails" style="display: none;">
                                <table class="table table-sm breakdown-table">
                                    <tbody>
                                        <tr>
                                            <td><i class="fas fa-tag text-primary"></i> Base Price</td>
                                            <td class="text-end">$<span id="breakdown_base">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-road text-warning"></i> Distance Cost</td>
                                            <td class="text-end">$<span id="breakdown_distance">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-mountain text-success"></i> Terrain Cost</td>
                                            <td class="text-end">$<span id="breakdown_terrain">0.00</span></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-expand text-info"></i> Area Cost</td>
                                            <td class="text-end">$<span id="breakdown_area">0.00</span></td>
                                        </tr>
                                        <tr class="table-active fw-bold">
                                            <td>Total</td>
                                            <td class="text-end">$<span id="breakdown_total">0.00</span></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Calculation Details -->
                                <div class="calculation-details">
                                    
                                    <p class="detail-item mb-1">
                                        <i class="fas fa-chart-line"></i> 
                                        <strong>Terrain Factor:</strong> <span id="detail_terrain_factor">-</span>
                                    </p>
                                    <p class="detail-item mb-0">
                                        <i class="fas fa-layer-group"></i> 
                                        <strong>Area Factor:</strong> <span id="detail_area_factor">-</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Final Price Input -->
                        <div class="final-price-section mt-4">
                            <label for="requestPrice" class="form-label fw-bold">
                                <i class="fas fa-dollar-sign"></i> Final Price (USD) *
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       class="form-control form-control-lg" 
                                       id="requestPrice" 
                                       placeholder="Enter final price" 
                                       min="0" 
                                       step="0.01" 
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="useAutomatedPrice"
                                        title="Use automated price">
                                    <i class="fas fa-magic"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> 
                                You can adjust the automated price as needed
                            </small>
                        </div>

                        <!-- Rejection Reason (Hidden by default) -->
                        <div class="rejection-section mt-3" style="display: none;">
                            <label for="rejectionReason" class="form-label fw-bold">
                                <i class="fas fa-comment-slash"></i> Rejection Reason *
                            </label>
                            <textarea class="form-control" 
                                      id="rejectionReason" 
                                      rows="3" 
                                      placeholder="Enter reason for rejection..."></textarea>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div id="priceErrorState" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Manual Quote Required</strong>
                            <p class="mb-0 mt-2" id="priceErrorMessage"></p>
                        </div>
                        <div class="manual-price-section">
                            <label for="requestPrice" class="form-label fw-bold">
                                <i class="fas fa-dollar-sign"></i> Manual Price (USD) *
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       class="form-control form-control-lg" 
                                       id="requestPriceManual" 
                                       placeholder="Enter price manually" 
                                       min="0" 
                                       step="0.01" 
                                       required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer d-flex justify-content-between">
        <div>
          <button type="button" class="btn btn-info" id="viewMapBtn">
            <i class="fas fa-map-marked-alt"></i> View on Map
          </button>
        </div>
        <div>
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
        <label>Service Name </label>
        <input type="text" id="add_service_name" class="form-control" placeholder="Enter service name" required>
      </div>

      <div class="mb-3">
        <label>Base Price </label>
        <input type="number" id="add_min_price" class="form-control" placeholder="Enter minimum price" step="0.01" min="0" required>
      </div>

       

      <div class="mb-3">
        <label>Description </label>
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
// Global variables
let automatedPrice = 0;
let currentRequestId = null;

// Load requests via AJAX
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
            $('#userTable').html('<div class="col-12"><p class="text-center text-danger p-3">Error loading requests.</p></div>');
        }
    });
}

// Tab Navigation
$(document).on('click', '.tab-btn', function() {
    $('.tab-btn').removeClass('active');
    $(this).addClass('active');
    loadRequests($(this).data('status'));
});

// Apply Filters
$(document).on('click', '#applyFilters', function() {
    loadRequests($('.tab-btn.active').data('status'));
});

$(document).ready(function() {
    loadRequests('pending');
});

$('#serviceTypeFilter, #dateRangeFilter').on('change', function() {
    loadRequests($('.tab-btn.active').data('status'));
});

// Open respond modal with automated price calculation
$(document).on('click', '.respond-btn', function() {
    currentRequestId = $(this).data('id');
    $('#respondModal').modal('show');

    // Reset all states
    $('#requestPrice, #requestPriceManual, #rejectionReason').val('');
    $('#priceLoadingState').show();
    $('#priceBreakdownSection, #priceErrorState').hide();
    $('.rejection-section').hide();
    automatedPrice = 0;

    // Load land details
    $('#landDetails').html('<p class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Loading...</p>');
    $.post('admin-services-fetch-land-info.php', { request_id: currentRequestId }, function(response) {
        $('#landDetails').html(response);
    });

    // Calculate automated price
    $.post('admin-services-calculate-price.php', { request_id: currentRequestId }, function(response) {
        try {
            const data = JSON.parse(response);
            
            if (data.error) {
                $('#priceLoadingState').hide();
                $('#priceErrorState').show();
                $('#priceErrorMessage').text(data.message);
            } else {
                automatedPrice = parseFloat(data.automated_price);
                
                $('#priceLoadingState').hide();
                $('#priceBreakdownSection').show();
                
                // Display automated price
                $('#automatedPriceDisplay').text(data.automated_price);
                
                // Set breakdown
                $('#breakdown_base').text(data.breakdown.base_price);
                $('#breakdown_distance').text(data.breakdown.distance_cost);
                $('#breakdown_terrain').text(data.breakdown.terrain_cost);
                $('#breakdown_area').text(data.breakdown.area_cost);
                $('#breakdown_total').text(data.breakdown.total);
                
                // Set details
                $('#detail_area').text(data.details.area);
                $('#detail_distance').text(data.details.distance);
                $('#detail_terrain_factor').text(data.details.terrain_factor);
                $('#detail_area_factor').text(data.details.area_factor);
                
                // Pre-fill price input
                $('#requestPrice').val(data.automated_price);
            }
        } catch (e) {
            console.error('Parse error:', e);
            $('#priceLoadingState').hide();
            $('#priceErrorState').show();
            $('#priceErrorMessage').text('Error calculating price. Please enter manually.');
        }
    }).fail(function() {
        $('#priceLoadingState').hide();
        $('#priceErrorState').show();
        $('#priceErrorMessage').text('Failed to calculate price. Please enter manually.');
    });
});

// Toggle breakdown details
$(document).on('click', '#toggleBreakdown', function() {
    $('#breakdownDetails').slideToggle(300);
    $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
});

// Use automated price button
$(document).on('click', '#useAutomatedPrice', function() {
    if (automatedPrice > 0) {
        $('#requestPrice').val(automatedPrice.toFixed(2));
        $(this).html('<i class="fas fa-check"></i>');
        setTimeout(() => $(this).html('<i class="fas fa-magic"></i>'), 1000);
    }
});

// Approve Request
$('#approveRequest').click(function() {
    let price = $('#requestPrice').val() || $('#requestPriceManual').val();
    
    if (!price || parseFloat(price) <= 0) {
        alert("Please enter a valid price to approve this request.");
        $('#requestPrice, #requestPriceManual').addClass('is-invalid');
        return;
    }

    $.post('admin-services-status-update-query.php', {
        request_id: currentRequestId,
        status: 'approved',
        price: price
    }, function(response) {
        if (response.includes('success') || !response.includes('error')) {
            alert('Request approved successfully!');
            $('#respondModal').modal('hide');
            loadRequests('pending');
        } else {
            alert(response);
        }
    });
});

// Deny Request
let rejectionMode = false;
$('#denyRequest').click(function() {
    if (!rejectionMode) {
        // First click: show rejection section
        $('.rejection-section').slideDown();
        $('#rejectionReason').focus();
        $(this).html('<i class="fas fa-exclamation-triangle"></i> Confirm Rejection');
        $(this).removeClass('btn-danger').addClass('btn-warning');
        rejectionMode = true;
    } else {
        // Second click: submit rejection
        let notes = $('#rejectionReason').val().trim();
        
        if (!notes) {
            alert("Please enter a rejection reason.");
            $('#rejectionReason').addClass('is-invalid');
            return;
        }

        $.post('admin-services-status-update-query.php', {
            request_id: currentRequestId,
            status: 'rejected',
            notes: notes
        }, function(response) {
            if (response.includes('success') || !response.includes('error')) {
                alert('Request rejected.');
                $('#respondModal').modal('hide');
                loadRequests('pending');
                rejectionMode = false;
            } else {
                alert(response);
            }
        });
    }
});

// Reset rejection mode when modal closes
$('#respondModal').on('hidden.bs.modal', function() {
    rejectionMode = false;
    $('#denyRequest').html('<i class="fas fa-times"></i> Deny Request')
                     .removeClass('btn-warning').addClass('btn-danger');
});

// View rejection reason
$(document).on('click', '.viewReasonBtn', function() {
    let id = $(this).data('id');
    if ($('#rejectionReasonModal').parent()[0].tagName !== 'BODY') {
        $('#rejectionReasonModal').appendTo('body');
    }
    $('#rejectionReasonModal').modal('show');
    $.post('admin-services-fetch-rejection-reason.php', { id: id }, function(response) {
        $('#rejectionReasonText').html(response);
    });
});

// View approved details
$(document).on('click', '.viewDetailsBtn', function() {
    let id = $(this).data('id');
    $('#approvedModal').modal('show');
    $('#approvedLandDetails').html('<p>Loading...</p>');
    $.post('admin-services-fetch-land-info.php', { request_id: id }, function(response) {
        $('#approvedLandDetails').html(response);
    });
});

// View on Map
$(document).on('click', '#viewMapBtn', function() {
    if (!currentRequestId) {
        alert('Request ID not available.');
        return;
    }
    window.open(`admin-map-viewer.php?request_id=${currentRequestId}`, 'MapView', 'width=1200,height=800,scrollbars=yes,resizable=yes');
});

// Service Management
$(document).on('click', '.editServiceBtn', function() {
    let serviceId = $(this).data('id');
    if ($('#editServiceModal').parent()[0].tagName !== 'BODY') {
        $('#editServiceModal').appendTo('body');
    }
    $('#editServiceModal').fadeIn(300);
    $('body').css('overflow', 'hidden');
    $('#editServiceModalBody').html('<p class="text-center">Loading...</p>');
    $.post('admin-services-fetch-service-info.php', { service_id: serviceId }, function(data) {
        $('#editServiceModalBody').html(data);
    });
});

$(document).on('click', '#closeEditModal, #closeEditModalBtn', function() {
    $('#editServiceModal').fadeOut(300);
    $('body').css('overflow', '');
});

$(document).on('click', '#saveServiceBtn', function() {
    $.post('admin-services-edit-query.php', {
        service_id: $('#edit_service_id').val(),
        status: $('#edit_service_status').val(),
        service_name: $('#edit_service_name').val(),
        min_price: $('#edit_min_price').val(),
        
        description: $('#edit_description').val()
    }, function(response) {
        alert(response);
        $('#editServiceModal').fadeOut(300);
        $('body').css('overflow', '');
        loadRequests('services');
    });
});

$(document).on('click', '.deleteServiceBtn', function() {
    const serviceId = $(this).data('id');
    if(!confirm("Are you sure you want to deactivate this service?")) return;
    $.post('admin-services-delete-query.php', { service_id: serviceId }, function(response) {
        alert(response);
        loadRequests('services');
    });
});

$(document).on('click', '#addServiceBtn', function() {
    if ($('#addServiceModal').parent()[0].tagName !== 'BODY') {
        $('#addServiceModal').appendTo('body');
    }
    $('#addServiceModal').fadeIn(300);
    $('body').css('overflow', 'hidden');
    $('#add_service_name, #add_min_price,  #add_description').val('');
});

$(document).on('click', '#closeAddModal, #closeAddModalBtn', function() {
    $('#addServiceModal').fadeOut(300);
    $('body').css('overflow', '');
});

$(document).on('click', '#saveNewServiceBtn', function() {
    $.post('admin-services-add-service-query.php', {
        service_name: $('#add_service_name').val().trim(),
        min_price: $('#add_min_price').val(),
         
        description: $('#add_description').val().trim()
    }, function(response) {
        alert(response);
        $('#addServiceModal').fadeOut(300);
        $('body').css('overflow', '');
        loadRequests('services');
    });
});
</script>
 
<?php include 'includes/footer.html'; ?>