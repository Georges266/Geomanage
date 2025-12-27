<?php
 include 'includes/header.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Sales_Person") { 
    exit();
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
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
        <div class="row">
            <div class="col-12">
                <div class="tab-navigation mb-40">
                    <button class="tab-btn active" onclick="openTab('land-listings')">
                        <i class="fas fa-map-marked-alt"></i> Land Listings (8)
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
                        <div class="row">
                            <div class="col-md-3 padding-10">
                                <input type="text" class="form-control" placeholder="Search listings...">
                            </div>
                            <div class="col-md-3 padding-10">
                                <select class="form-control" id="filter-status">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending Review</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="sold">Sold</option>
                                </select>
                            </div>
                            <div class="col-md-3 padding-10">
                                <select class="form-control" id="filter-zone">
                                    <option value="">All Zones</option>
                                    <option value="urban">Urban</option>
                                    <option value="suburban">Suburban</option>
                                    <option value="rural">Rural</option>
                                    <option value="remote">Remote</option>
                                </select>
                            </div>
                            <div class="col-md-3 padding-10">
                                <button class="default-btn" style="width: 100%; padding: 10px;" onclick="filterListings()">Filter</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Land Listings -->
            <div class="row">
                <!-- Listing 1 - Pending -->
                <div class="col-lg-4 col-md-6 padding-10">
                    <div class="service-item box-shadow" style="padding: 15px;">
                        <div class="service-content" style="padding: 0;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h4 style="font-size: 16px; margin: 0;">5.2 Acres - Commercial</h4>
                                <span class="status-badge status-pending">Pending</span>
                            </div>
                            <p style="font-size: 13px; margin: 5px 0;"><strong>Location:</strong> 123 Commercial Blvd</p>
                            <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Zone:</strong> Urban</p>
                            <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Price:</strong> $450,000</p>
                            <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Owner:</strong> ABC Development Corp</p>
                            <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Listed:</strong> Jan 15, 2024</p>
                            <div class="listing-actions mt-3" style="gap: 5px; display: flex;">
                                <button class="dl-btn" style="padding: 5px 10px; font-size: 12px; background: #4caf50; flex: 1;" onclick="approveListing(1)">Approve</button>
                                <button class="dl-btn" style="padding: 5px 10px; font-size: 12px; background: #f44336; flex: 1;" onclick="rejectListing(1)">Reject</button>
                                <button class="dl-btn" style="padding: 5px 10px; font-size: 12px; flex: 1;" onclick="viewListingDetails(1)">Details</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Listing 2 - Approved -->
                <div class="col-lg-4 col-md-6 padding-10">
                    <div class="service-item box-shadow" style="padding: 15px;">
                        <div class="service-content" style="padding: 0;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h4 style="font-size: 16px; margin: 0;">12.5 Acres - Residential</h4>
                                <span class="status-badge status-approved">Approved</span>
                            </div>
                            <p style="font-size: 13px; margin: 5px 0;"><strong>Location:</strong> 456 Green Valley Rd</p>
                            <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Zone:</strong> Suburban</p>
                            <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Price:</strong> $280,000</p>
                            <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Owner:</strong> Private Seller</p>
                            <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Approved:</strong> Jan 12, 2024</p>
                            <div class="listing-actions mt-3" style="gap: 5px; display: flex;">
                                <button class="dl-btn" style="padding: 5px 10px; font-size: 12px; background: #2196f3; flex: 1;" onclick="markAsSold(2)">Mark Sold</button>
                                <button class="dl-btn" style="padding: 5px 10px; font-size: 12px; flex: 1;" onclick="viewListingDetails(2)">Details</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Listing 3 - Rejected -->
                <div class="col-lg-4 col-md-6 padding-10">
                    <div class="service-item box-shadow" style="padding: 15px;">
                        <div class="service-content" style="padding: 0;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h4 style="font-size: 16px; margin: 0;">8.0 Acres - Industrial</h4>
                                <span class="status-badge status-denied">Rejected</span>
                            </div>
                            <p style="font-size: 13px; margin: 5px 0;"><strong>Location:</strong> 789 Industrial Park</p>
                            <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Zone:</strong> Urban</p>
                            <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Price:</strong> $320,000</p>
                            <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Owner:</strong> Mega Industries</p>
                            <p style="font-size: 12px; margin: 3px 0; color: #666;"><strong>Rejected:</strong> Jan 10, 2024</p>
                            <p style="font-size: 11px; color: #f44336; margin: 5px 0;"><strong>Reason:</strong> Zoning compliance issues</p>
                            <div class="listing-actions mt-3" style="gap: 5px; display: flex;">
                                <button class="dl-btn" style="padding: 5px 10px; font-size: 12px; background: #4caf50; flex: 1;" onclick="reopenListing(3)">Reopen</button>
                                <button class="dl-btn" style="padding: 5px 10px; font-size: 12px; flex: 1;" onclick="viewListingDetails(3)">Details</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Land Listing Details Modal -->
<div id="listingDetailsModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 800px;">
        <div class="land-modal-header">
            <h3>Land Listing Details</h3>
            <span class="land-modal-close">&times;</span>
        </div>
        <div class="land-modal-body">
            <div class="listing-details">
                <!-- Property Images Gallery -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5>Property Images</h5>
                        <div class="property-images-gallery">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="property-image" style="width: 100%; height: 250px; background: #f0f0f0; border-radius: 5px; overflow: hidden;">
                                        <img src="uploads/listing_photo/1765914757_6941b88536382.jpg" alt="Property View 1" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="property-image" style="width: 100%; height: 250px; background: #f0f0f0; border-radius: 5px; overflow: hidden;">
                                        <img src="uploads/listing_photo/1765914757_6941b88536fcb.jpg" alt="Property View 2" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="property-image" style="width: 100%; height: 150px; background: #f0f0f0; border-radius: 5px; overflow: hidden;">
                                        <img src="uploads/listing_photo/1765915269_6941ba8589262.jpg" alt="Property View 3" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Property Information</h5>
                        <p><strong>Address:</strong> 123 Commercial Blvd</p>
                        <p><strong>Size:</strong> 5.2 acres</p>
                        <p><strong>Zone:</strong> Urban - Commercial</p>
                        <p><strong>Topography:</strong> Flat</p>
                        <p><strong>Access:</strong> Paved road</p>
                    </div>
                    <div class="col-md-6">
                        <h5>Listing Details</h5>
                        <p><strong>Asking Price:</strong> $450,000</p>
                        <p><strong>Listed Date:</strong> January 15, 2024</p>
                        <p><strong>Status:</strong> <span class="status-badge status-pending">Pending Review</span></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5>Owner Information</h5>
                        <p><strong>Name:</strong> ABC Development Corp</p>
                        <p><strong>Contact:</strong> John Manager</p>
                        <p><strong>Phone:</strong> (555) 123-4567</p>
                        <p><strong>Email:</strong> john@abcdev.com</p>
                    </div>
                </div>

                <div class="form-group">
                    <label>Admin Notes</label>
                    <textarea class="form-control" rows="3" placeholder="Add review notes..."></textarea>
                </div>

                <div class="form-group text-right" style="gap: 10px; display: flex; justify-content: flex-end;">
                    <button type="button" class="dl-btn" style="background: #666; padding: 8px 15px;" onclick="closeModal('listingDetailsModal')">Close</button>
                    <button type="button" class="dl-btn" style="background: #4caf50; padding: 8px 15px;" onclick="approveCurrentListing()">Approve Listing</button>
                    <button type="button" class="dl-btn" style="background: #f44336; padding: 8px 15px;" onclick="rejectCurrentListing()">Reject Listing</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Message Modal -->
<div id="successModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 350px;">
        <div class="land-modal-body text-center">
            <i class="fas fa-check-circle" style="font-size: 48px; color: #4caf50; margin-bottom: 15px;"></i>
            <h4 id="successMessage">Success!</h4>
            <p id="successDetails" style="font-size: 14px;"></p>
            <button type="button" class="dl-btn" style="background: #4caf50; padding: 8px 20px; margin-top: 15px;" onclick="closeModal('successModal')">OK</button>
        </div>
    </div>
</div>

<style>
.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}
.status-pending { background: #fff3cd; color: #856404; }
.status-approved { background: #d4edda; color: #155724; }
.status-denied, .status-rejected { background: #f8d7da; color: #721c24; }
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
</style>

<script>
// Tab Navigation
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

// Modal Management
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Land Listing Functions
function approveListing(listingId) {
    showSuccess('Listing Approved', `Land listing #${listingId} has been approved and published.`);
}

function rejectListing(listingId) {
    showSuccess('Listing Rejected', `Land listing #${listingId} has been rejected.`);
}

function markAsSold(listingId) {
    showSuccess('Listing Marked Sold', `Land listing #${listingId} has been marked as sold.`);
}

function reopenListing(listingId) {
    showSuccess('Listing Reopened', `Land listing #${listingId} has been reopened for review.`);
}

function viewListingDetails(listingId) {
    openModal('listingDetailsModal');
}

function approveCurrentListing() {
    closeModal('listingDetailsModal');
    showSuccess('Listing Approved', 'The land listing has been approved successfully.');
}

function rejectCurrentListing() {
    closeModal('listingDetailsModal');
    showSuccess('Listing Rejected', 'The land listing has been rejected.');
}

function filterListings() {
    const status = document.getElementById('filter-status').value;
    const zone = document.getElementById('filter-zone').value;
    
    showSuccess('Filter Applied', `Filtered by: ${status || 'All Status'}, ${zone || 'All Zones'}`);
}

function showSuccess(message, details) {
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successDetails').textContent = details;
    openModal('successModal');
}

// Initialize modals
document.addEventListener('DOMContentLoaded', function() {
    const closeButtons = document.querySelectorAll('.land-modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.land-modal');
            closeModal(modal.id);
        });
    });
    
    const modals = document.querySelectorAll('.land-modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                if (modal.style.display === 'block') {
                    closeModal(modal.id);
                }
            });
        }
    });
});
</script>

<?php include 'includes/footer.html'; ?>
</body>
</html>