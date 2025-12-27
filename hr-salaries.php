<?php
include 'includes/header.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "HR") {
    exit();
}
?>
<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Salary Management | HR Portal</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Reusing the same CSS files as your land estimator -->
</head>
<body>
<div class="site-preloader-wrap">
    <div class="spinner"></div>
</div>

<!-- Page Header -->
<section class="page-header padding">
    <div class="container">
        <div class="page-content text-center">
            <h2>Salary Management System</h2>
            <p>Manage employee salaries and compensation details</p>
        </div>
    </div>
</section>

<!-- Salary Management Section -->
<section class="contact-section padding bg-grey">
    <div class="dots"></div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="section-heading text-center mb-40">
                    <span>HR Portal</span>
                    <h2>Employee Salary Management</h2>
                    <p>View and update employee compensation details</p>
                </div>
                
                <div class="contact-form box-shadow" style="background: #fff; padding: 40px; border-radius: 5px;">
                    <!-- Employee Search and Filters -->
                    <div class="form-group colum-row row mb-4">
                        <div class="col-md-6">
                            <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Search Employee</label>
                            <input type="text" class="form-control" id="employee-search" placeholder="Search by name or ID">
                        </div>
                        <div class="col-md-3">
                            <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Department</label>
                            <select class="form-control" id="department-filter">
                                <option value="">All Departments</option>
                                <option value="sales">Sales</option>
                                <option value="engineering">Engineering</option>
                                <option value="marketing">Marketing</option>
                                <option value="hr">Human Resources</option>
                                <option value="finance">Finance</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Status</label>
                            <select class="form-control" id="status-filter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="on-leave">On Leave</option>
                                <option value="probation">Probation</option>
                            </select>
                        </div>
                    </div>

                    <!-- Employee Salary Table -->
                    <div class="table-responsive">
                        <table class="table table-striped" id="salary-table">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Current Salary</th>
                                    <th>Last Review</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="employees-tbody">
                                <!-- Employee data will be loaded here dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- No Results Message -->
                    <div id="no-results" style="display: none; text-align: center; padding: 30px;">
                        <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                        <h4 style="color: #8d9aa8;">No employees found</h4>
                        <p>Try adjusting your search or filter criteria</p>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="loading-indicator" style="text-align: center; padding: 30px;">
                        <div class="spinner"></div>
                        <p>Loading employee data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Salary Edit Modal -->
<div id="salary-edit-modal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
    <div class="modal-content" style="background: #fff; margin: 50px auto; padding: 30px; border-radius: 5px; width: 90%; max-width: 600px; position: relative;">
        <span class="close-btn" style="position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #8d9aa8;">&times;</span>
        
        <div class="text-center mb-30">
            <h3 style="color: #263a4f; margin-bottom: 10px;">Edit Employee Salary</h3>
            <p style="color: #8d9aa8;">Update compensation details</p>
        </div>
        
        <form id="salary-edit-form">
            <div class="form-group colum-row row">
                <div class="col-md-6">
                    <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Employee Name</label>
                    <input type="text" class="form-control" id="edit-employee-name" readonly>
                </div>
                <div class="col-md-6">
                    <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Employee ID</label>
                    <input type="text" class="form-control" id="edit-employee-id" readonly>
                </div>
            </div>
            <div class="form-group colum-row row">
                <div class="col-md-6">
                    <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Current Salary</label>
                    <input type="text" class="form-control" id="edit-current-salary" readonly>
                </div>
                <div class="col-md-6">
                    <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">New Salary</label>
                    <input type="number" class="form-control" id="edit-new-salary" min="0" step="1000" required>
                </div>
            </div>
            <div class="form-group colum-row row">
                <div class="col-md-6">
                    <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Effective Date</label>
                    <input type="date" class="form-control" id="edit-effective-date" required>
                </div>
                <div class="col-md-6">
                    <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Change Reason</label>
                    <select class="form-control" id="edit-change-reason" required>
                        <option value="">Select Reason</option>
                        <option value="annual-review">Annual Review</option>
                        <option value="promotion">Promotion</option>
                        <option value="performance">Performance Adjustment</option>
                        <option value="market-adjustment">Market Adjustment</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Notes</label>
                <textarea class="form-control" id="edit-notes" rows="3" placeholder="Add any additional notes..."></textarea>
            </div>
            <div class="form-group row mt-4">
                <div class="col-md-6">
                    <button type="button" class="default-btn" style="width: 100%; background: #6c757d;" id="cancel-edit">Cancel</button>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="default-btn" style="width: 100%;">Update Salary</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Features Section -->
<section class="service-section padding">
    <div class="container">
        <div class="section-heading text-center mb-40">
            <span>Management Tools</span>
            <h2>Salary Administration Features</h2>
        </div>
        <div class="row">
            <div class="col-lg-3 col-sm-6 padding-15">
                <div class="service-item box-shadow text-center">
                    <i class="fas fa-chart-line"></i>
                    <h3>Salary Review</h3>
                    <p>Track and manage employee salary reviews and adjustments</p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 padding-15">
                <div class="service-item box-shadow text-center">
                    <i class="fas fa-calculator"></i>
                    <h3>Budget Planning</h3>
                    <p>Plan department budgets with salary forecasting tools</p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 padding-15">
                <div class="service-item box-shadow text-center">
                    <i class="fas fa-trophy"></i>
                    <h3>Performance Link</h3>
                    <p>Connect salary adjustments to performance metrics</p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 padding-15">
                <div class="service-item box-shadow text-center">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Compliance</h3>
                    <p>Ensure salary practices meet regulatory requirements</p>
                </div>
            </div>
        </div>
    </div>
</section>


<?php include 'includes/footer.html'; ?>
</body>
</html>