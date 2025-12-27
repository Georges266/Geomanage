<?php
include 'includes/header.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "LeadEngineer") {
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
                <h1>Deliverable Review</h1>
                <p>Review and approve deliverables submitted by your team</p>
            </div>
            <div class="col-lg-4 text-right">
                <div class="engineer-info">
                    <strong>Lead Engineer: John Smith</strong><br>
                    <small>Pending Reviews: 3</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Review Section -->
<section class="padding">
    <div class="container">
        <!-- Pending Reviews -->
        <div class="service-item box-shadow mb-5" style="padding: 25px;">
            <h3 class="mb-4" style="color: #263a4f;">Pending Reviews</h3>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Land</th>
                            <th>Submitted By</th>
                            <th>Submitted Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Topographic Map - Lot 2458</td>
                            <td>Commercial Land Survey</td>
                            <td>Lot 2458</td>
                            <td>Mike Chen</td>
                            <td>Jan 25, 2024</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="reviewSubmission(1)">Review</button>
                            </td>
                        </tr>
                        <tr>
                            <td>ALTA Survey - Lot 2457</td>
                            <td>Commercial Land Survey</td>
                            <td>Lot 2457</td>
                            <td>Sarah Johnson</td>
                            <td>Jan 24, 2024</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="reviewSubmission(2)">Review</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Boundary Survey - Lot 1872</td>
                            <td>Residential Subdivision</td>
                            <td>Lot 1872</td>
                            <td>David Wilson</td>
                            <td>Jan 23, 2024</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="reviewSubmission(3)">Review</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Review History -->
        <div class="service-item box-shadow" style="padding: 25px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 style="color: #263a4f; margin: 0;">Review History</h4>
                <div class="search-filter">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" class="form-control" placeholder="Search by project name..." id="projectSearch">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" onclick="searchProjects()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped" id="reviewHistoryTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Submitted By</th>
                            <th>Submitted Date</th>
                            <th>Reviewed Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reviewHistoryBody">
                        <tr>
                            <td>Boundary Survey - Lot 1872</td>
                            <td>Commercial Land Survey</td>
                            <td>Mike Chen</td>
                            <td>Jan 22, 2024</td>
                            <td>Jan 24, 2024</td>
                            <td><span class="status-badge status-revisions">Revisions Requested</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewReview(1)">View</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Site Plan - Lot 2456</td>
                            <td>Commercial Land Survey</td>
                            <td>Emily Davis</td>
                            <td>Jan 20, 2024</td>
                            <td>Jan 21, 2024</td>
                            <td><span class="status-badge status-approved">Approved</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewReview(2)">View</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Construction Staking - Lot 2459</td>
                            <td>Commercial Land Survey</td>
                            <td>Robert Garcia</td>
                            <td>Jan 18, 2024</td>
                            <td>Jan 19, 2024</td>
                            <td><span class="status-badge status-approved">Approved</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewReview(3)">View</button>
                                <button class="btn btn-sm btn-outline-success" onclick="downloadApproved(3)">Download</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Subdivision Plat - Lot 1872</td>
                            <td>Residential Subdivision</td>
                            <td>Lisa Rodriguez</td>
                            <td>Jan 17, 2024</td>
                            <td>Jan 18, 2024</td>
                            <td><span class="status-badge status-approved">Approved</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewReview(4)">View</button>
                                <button class="btn btn-sm btn-outline-success" onclick="downloadApproved(4)">Download</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Grading Plan - Lot 1873</td>
                            <td>Residential Subdivision</td>
                            <td>David Wilson</td>
                            <td>Jan 15, 2024</td>
                            <td>Jan 16, 2024</td>
                            <td><span class="status-badge status-revisions">Revisions Requested</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewReview(5)">View</button>
                            </td>
                        </tr>
                        <tr>
                            <td>Right of Way Survey - Highway 45</td>
                            <td>Infrastructure Project</td>
                            <td>Sarah Johnson</td>
                            <td>Jan 12, 2024</td>
                            <td>Jan 14, 2024</td>
                            <td><span class="status-badge status-approved">Approved</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewReview(6)">View</button>
                                <button class="btn btn-sm btn-outline-success" onclick="downloadApproved(6)">Download</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="text-center mt-4" style="display: none;">
                <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                <h5>No deliverables found</h5>
                <p class="text-muted">Try searching for a different project name.</p>
            </div>
        </div>
    </div>
</section>

<!-- Review Modal -->
<div id="reviewModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 800px;">
        <div class="land-modal-header">
            <h3>Review Deliverable</h3>
            <span class="land-modal-close">&times;</span>
        </div>
        <div class="land-modal-body">
            <div id="reviewContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Review History Modal -->
<div id="historyModal" class="land-modal">
    <div class="land-modal-content" style="max-width: 800px;">
        <div class="land-modal-header">
            <h3>Review Details</h3>
            <span class="land-modal-close">&times;</span>
        </div>
        <div class="land-modal-body">
            <div id="historyContent">
                <!-- Content will be populated by JavaScript -->
            </div>
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
.status-under-review { background: #fff3cd; color: #856404; }
.status-revisions { background: #ffeaa7; color: #856404; }
.status-approved { background: #d4edda; color: #155724; }
.status-rejected { background: #f8d7da; color: #721c24; }
.search-filter {
    display: flex;
    gap: 10px;
    align-items: center;
}
</style>



<?php include 'includes/footer.html'; ?>
</body>
</html>