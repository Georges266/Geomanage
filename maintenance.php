<?php
include 'includes/header.php'; 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Maintenance_Technician") {
    exit();
}
?>
<!doctype html>
<html class="no-js" lang="en">
<body>
<div class="site-preloader-wrap">
    <div class="spinner"></div>
</div>

<!-- Page Header -->
<section class="page-header padding bg-grey">
    <div class="container">
        <div class="row d-flex align-items-center">
            <div class="col-lg-8">
                <h1>Equipment Maintenance</h1>
                <p>Manage maintenance requests and equipment status</p>
            </div>
            <div class="col-lg-4 text-right">
                <div class="engineer-info">
                    <strong>Maintenance Technician: Alex Johnson</strong><br>
                    <small>Pending Requests: 3</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Maintenance Section -->
<section class="padding">
    <div class="container">
        <!-- Maintenance Requests -->
        <div class="service-item box-shadow mb-5" style="padding: 25px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 style="color: #263a4f; margin: 0;">Maintenance Requests</h3>
                <div class="search-filter">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" class="form-control" placeholder="Search by equipment or ID..." id="maintenanceSearch">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="searchMaintenance()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Equipment</th>
                            <th>Requested By</th>
                            <th>Request Date</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="maintenanceBody">
                        <tr>
                            <td>#MT-001</td>
                            <td>Total Station - Leica TS16</td>
                            <td>John Smith</td>
                            <td>Jan 28, 2024</td>
                            <td><span class="badge badge-danger">High</span></td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="viewMaintenanceRequest(1)">View Details</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#MT-002</td>
                            <td>GPS Rover - Trimble R12</td>
                            <td>Sarah Wilson</td>
                            <td>Jan 27, 2024</td>
                            <td><span class="badge badge-warning">Medium</span></td>
                            <td><span class="status-badge status-in-progress">In Progress</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="viewMaintenanceRequest(2)">View Details</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#MT-003</td>
                            <td>Data Collector - TSC7</td>
                            <td>Mike Chen</td>
                            <td>Jan 26, 2024</td>
                            <td><span class="badge badge-success">Low</span></td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="viewMaintenanceRequest(3)">View Details</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#MT-004</td>
                            <td>Laser Scanner - Faro Focus</td>
                            <td>Emily Davis</td>
                            <td>Jan 25, 2024</td>
                            <td><span class="badge badge-danger">High</span></td>
                            <td><span class="status-badge status-in-progress">In Progress</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="viewMaintenanceRequest(4)">View Details</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="text-center mt-4" style="display: none;">
                <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                <h5>No maintenance requests found</h5>
                <p class="text-muted">Try searching for different equipment or request ID.</p>
            </div>
        </div>

        <!-- Equipment Maintenance History -->
        <div class="service-item box-shadow" style="padding: 25px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 style="color: #263a4f; margin: 0;">Equipment Maintenance History</h3>
                <div class="search-filter">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" class="form-control" placeholder="Search equipment by name, type, or ID..." id="historySearch">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="searchHistory()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Equipment ID</th>
                            <th>Equipment Name</th>
                            <th>Type</th>
                            <th>Last Maintenance</th>
                            <th>Total Maintenances</th>
                            <th>Current Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody">
                        <tr>
                            <td>#EQ-101</td>
                            <td>Total Station - Leica TS16</td>
                            <td>Total Station</td>
                            <td>Jan 15, 2024</td>
                            <td>5</td>
                            <td><span class="status-badge status-operational">Operational</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewEquipmentHistory(1)">View History</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#EQ-102</td>
                            <td>GPS Rover - Trimble R12</td>
                            <td>GPS</td>
                            <td>Jan 12, 2024</td>
                            <td>8</td>
                            <td><span class="status-badge status-operational">Operational</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewEquipmentHistory(2)">View History</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#EQ-103</td>
                            <td>GPS Base Station</td>
                            <td>GPS</td>
                            <td>Jan 24, 2024</td>
                            <td>3</td>
                            <td><span class="status-badge status-operational">Operational</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewEquipmentHistory(3)">View History</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#EQ-104</td>
                            <td>Data Collector - TSC7</td>
                            <td>Data Collector</td>
                            <td>Jan 08, 2024</td>
                            <td>6</td>
                            <td><span class="status-badge status-operational">Operational</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewEquipmentHistory(4)">View History</button>
                            </td>
                        </tr>
                        <tr>
                            <td>#EQ-105</td>
                            <td>Laser Scanner - Faro Focus</td>
                            <td>Laser Scanner</td>
                            <td>Dec 28, 2023</td>
                            <td>4</td>
                            <td><span class="status-badge status-operational">Operational</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewEquipmentHistory(5)">View History</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- No Results Message -->
            <div id="noHistoryResults" class="text-center mt-4" style="display: none;">
                <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                <h5>No equipment found</h5>
                <p class="text-muted">Try searching with different keywords.</p>
            </div>
        </div>
    </div>
</section>

<!-- Maintenance Details Modal -->
<div id="maintenanceModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 700px;">
        <div class="land-modal-header">
            <h3>Maintenance Request Details</h3>
            <span class="land-modal-close">&times;</span>
        </div>
        <div class="land-modal-body">
            <div id="maintenanceDetails">
                <div class="maintenance-detail-section">
                    <h5>Request Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Request ID:</strong> #MT-001</p>
                            <p><strong>Equipment:</strong> Total Station - Leica TS16</p>
                            <p><strong>Equipment ID:</strong> #EQ-101</p>
                            <p><strong>Priority:</strong> <span class="badge badge-danger">High</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Requested By:</strong> John Smith</p>
                            <p><strong>Request Date:</strong> Jan 28, 2024</p>
                            <p><strong>Status:</strong> <span class="status-badge status-pending">Pending</span></p>
                        </div>
                    </div>
                </div>

                <div class="maintenance-detail-section">
                    <h5>Issue Description</h5>
                    <p>The total station is showing calibration errors and the measurements are inconsistent. The device needs recalibration and possible internal adjustments. Last calibration was 6 months ago.</p>
                </div>

                <div class="maintenance-detail-section">
                    <h5>Equipment Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Model:</strong> Leica TS16</p>
                            <p><strong>Serial Number:</strong> TS16-2023-0042</p>
                            <p><strong>Purchase Date:</strong> March 15, 2023</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Last Maintenance:</strong> Jan 15, 2024</p>
                            <p><strong>Warranty Status:</strong> Active (Until Mar 2025)</p>
                            <p><strong>Location:</strong> Warehouse B</p>
                        </div>
                    </div>
                </div>

                <div class="form-group text-right" style="gap: 10px; display: flex; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="dl-btn" style="background: #666; padding: 8px 15px;" onclick="closeModal('maintenanceModal')">Close</button>
                    <button type="button" class="dl-btn" style="background: #2196f3; padding: 8px 15px;" onclick="openStatusModal()">Update Status</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Equipment History Modal -->
<div id="historyModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 800px;">
        <div class="land-modal-header">
            <h3>Equipment Maintenance History</h3>
            <span class="land-modal-close">&times;</span>
        </div>
        <div class="land-modal-body">
            <div id="equipmentHistoryDetails">
                <div class="maintenance-detail-section">
                    <h5>Equipment Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Equipment ID:</strong> #EQ-101</p>
                            <p><strong>Name:</strong> Total Station - Leica TS16</p>
                            <p><strong>Type:</strong> Total Station</p>
                            <p><strong>Model:</strong> Leica TS16</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Serial Number:</strong> TS16-2023-0042</p>
                            <p><strong>Purchase Date:</strong> March 15, 2023</p>
                            <p><strong>Current Status:</strong> <span class="status-badge status-operational">Operational</span></p>
                            <p><strong>Total Maintenances:</strong> 5</p>
                        </div>
                    </div>
                </div>

                <div class="maintenance-detail-section">
                    <h5>Maintenance History</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Request ID</th>
                                    <th>Type</th>
                                    <th>Technician</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Jan 15, 2024</td>
                                    <td>#MT-015</td>
                                    <td>Calibration</td>
                                    <td>Alex Johnson</td>
                                    <td>Full calibration performed. All measurements within tolerance.</td>
                                    <td><span class="status-badge status-operational">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>Dec 10, 2023</td>
                                    <td>#MT-012</td>
                                    <td>Repair</td>
                                    <td>Mike Torres</td>
                                    <td>Replaced battery pack. Fixed display issue.</td>
                                    <td><span class="status-badge status-operational">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>Nov 05, 2023</td>
                                    <td>#MT-008</td>
                                    <td>Inspection</td>
                                    <td>Alex Johnson</td>
                                    <td>Routine inspection. No issues found.</td>
                                    <td><span class="status-badge status-operational">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>Sep 20, 2023</td>
                                    <td>#MT-005</td>
                                    <td>Calibration</td>
                                    <td>Sarah Chen</td>
                                    <td>6-month calibration check. Minor adjustments made.</td>
                                    <td><span class="status-badge status-operational">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>Jul 15, 2023</td>
                                    <td>#MT-002</td>
                                    <td>Inspection</td>
                                    <td>Alex Johnson</td>
                                    <td>Initial setup and inspection after purchase.</td>
                                    <td><span class="status-badge status-operational">Completed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-group text-right" style="margin-top: 20px;">
                    <button type="button" class="dl-btn" style="background: #666; padding: 8px 15px;" onclick="closeModal('historyModal')">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 500px;">
        <div class="land-modal-header">
            <h3>Update Equipment Status</h3>
            <span class="land-modal-close">&times;</span>
        </div>
        <div class="land-modal-body">
            <form id="statusUpdateForm">
                <div class="form-group">
                    <label>Equipment</label>
                    <input type="text" class="form-control" id="equipmentName" value="Total Station - Leica TS16" readonly>
                </div>

                <div class="form-group">
                    <label>Current Status</label>
                    <input type="text" class="form-control" id="currentStatus" value="Pending" readonly>
                </div>

                <div class="form-group">
                    <label>Update Status To</label>
                    <select class="form-control" id="newStatus" required>
                        <option value="">Select Status</option>
                        <option value="in-progress">In Progress</option>
                        <option value="operational">Operational</option>
                        <option value="needs-parts">Needs Parts</option>
                        <option value="out-of-service">Out of Service</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Maintenance Notes</label>
                    <textarea class="form-control" id="maintenanceNotes" rows="4" placeholder="Describe the work performed, parts replaced, or any issues encountered..."></textarea>
                </div>

                <div class="form-group">
                    <label>Next Maintenance Date (if applicable)</label>
                    <input type="date" class="form-control" id="nextMaintenanceDate">
                </div>

                <div class="form-group text-right" style="gap: 10px; display: flex; justify-content: flex-end;">
                    <button type="button" class="dl-btn" style="background: #666; padding: 8px 15px;" onclick="closeModal('statusModal')">Cancel</button>
                    <button type="submit" class="dl-btn" style="background: #4caf50; padding: 8px 15px;">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 400px;">
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
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}
.status-pending { background: #fff3cd; color: #856404; }
.status-in-progress { background: #cce7ff; color: #004085; }
.status-operational { background: #d4edda; color: #155724; }
.status-needs-parts { background: #ffeaa7; color: #856404; }
.status-out-of-service { background: #f8d7da; color: #721c24; }
.search-filter {
    display: flex;
    gap: 10px;
    align-items: center;
}
.maintenance-detail-section {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 5px;
}
.maintenance-detail-section h5 {
    color: #263a4f;
    margin-bottom: 15px;
    font-size: 16px;
    font-weight: 600;
}
.maintenance-detail-section p {
    margin-bottom: 8px;
    font-size: 14px;
}
</style>

<script>
// Modal Management
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

// View Maintenance Request Details
function viewMaintenanceRequest(requestId) {
    // In a real application, this would fetch data based on requestId
    openModal('maintenanceModal');
}

// View Equipment History
function viewEquipmentHistory(equipmentId) {
    // In a real application, this would fetch equipment history based on equipmentId
    openModal('historyModal');
}

// Open Status Update Modal
function openStatusModal() {
    closeModal('maintenanceModal');
    openModal('statusModal');
}

// Search Maintenance Requests
function searchMaintenance() {
    const searchTerm = document.getElementById('maintenanceSearch').value.toLowerCase();
    const tbody = document.getElementById('maintenanceBody');
    const rows = tbody.getElementsByTagName('tr');
    const noResults = document.getElementById('noResults');
    let visibleCount = 0;

    for (let row of rows) {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    }

    noResults.style.display = visibleCount === 0 ? 'block' : 'none';
}

// Search Equipment History
function searchHistory() {
    const searchTerm = document.getElementById('historySearch').value.toLowerCase();
    const tbody = document.getElementById('historyBody');
    const rows = tbody.getElementsByTagName('tr');
    const noResults = document.getElementById('noHistoryResults');
    let visibleCount = 0;

    for (let row of rows) {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    }

    noResults.style.display = visibleCount === 0 ? 'block' : 'none';
}

// Show Success Message
function showSuccess(message, details) {
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successDetails').textContent = details;
    openModal('successModal');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Close modals when clicking X
    const closeButtons = document.querySelectorAll('.land-modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.land-modal');
            closeModal(modal.id);
        });
    });
    
    // Close modals when clicking outside
    const modals = document.querySelectorAll('.land-modal');
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modals.forEach(modal => {
                if (modal.style.display === 'block') {
                    closeModal(modal.id);
                }
            });
        }
    });

    // Handle status update form submission
    document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        closeModal('statusModal');
        showSuccess('Status Updated', 'Equipment status has been updated successfully.');
    });

    // Enable search on Enter key
    document.getElementById('maintenanceSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchMaintenance();
        }
    });

    document.getElementById('historySearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchHistory();
        }
    });
});
</script>

<?php include 'includes/footer.html'; ?>
</body>
</html>