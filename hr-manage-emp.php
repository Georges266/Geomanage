<?php
include 'includes/header.php';
include 'includes/connect.php';

// Check authorization
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "HR") {
    exit();
}

// Handle employee promotion
if (isset($_POST['promote_user_id']) && isset($_POST['confirm_promotion'])) {
    $userId = (int)$_POST['promote_user_id'];
    $newRole = mysqli_real_escape_string($con, $_POST['new_position']);
    $newSalary = (float)$_POST['new_salary'];
    $contractStart = mysqli_real_escape_string($con, $_POST['contract_start']);
    $contractEnd = mysqli_real_escape_string($con, $_POST['contract_end']);
    $signingDate = mysqli_real_escape_string($con, $_POST['signing_date']);
    
    if ($userId > 0 && !empty($newRole) && $newSalary > 0) {
        // Update user role
        $updateRole = "UPDATE user SET role = '$newRole' WHERE user_id = $userId";
        mysqli_query($con, $updateRole);
        
        // Insert new contract with position
        $insertContract = "INSERT INTO contract (user_id, position, salary, signing_date, start_date, end_date) 
                          VALUES ($userId, '$newRole', $newSalary, '$signingDate', '$contractStart', '$contractEnd')";
        
        if (mysqli_query($con, $insertContract)) {
            header("Location: hr-manage-emp.php?msg=promoted");
            exit();
        } else {
            header("Location: hr-manage-emp.php?msg=promotion_error");
            exit();
        }
    }
}

// Handle employee removal
if (isset($_POST['user_id']) && isset($_POST['confirm_removal'])) {
    $userId = (int)$_POST['user_id'];
    
    if ($userId > 0) {
        // Check if contract end date has passed
        $checkSql = "SELECT end_date FROM contract WHERE user_id = $userId ORDER BY contract_id DESC LIMIT 1";
        $checkResult = mysqli_query($con, $checkSql);
        
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            $contractData = mysqli_fetch_assoc($checkResult);
            $endDate = strtotime($contractData['end_date']);
            $today = strtotime(date('Y-m-d'));
            
            if ($today < $endDate) {
                header("Location: hr-manage-emp.php?msg=contract_active");
                exit();
            }
        }
        
        // Set user as inactive
        $updateSql = "UPDATE user SET active = 0 WHERE user_id = $userId";
        
        if (mysqli_query($con, $updateSql)) {
            header("Location: hr-manage-emp.php?msg=removed");
            exit();
        }
    }
}

// Build the base query
$sql = "SELECT u.user_id, u.full_name, u.email, u.phone, u.role,
               c.contract_id, c.position, c.end_date, c.signing_date, c.salary
        FROM user u
        LEFT JOIN contract c ON u.user_id = c.user_id
        WHERE u.role NOT IN ('Admin','CLIENT') AND u.active = 1";

// Apply search filter
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($con, $_GET['search']);
    $sql .= " AND (u.full_name LIKE '%$search%' OR u.email LIKE '%$search%')";
}

// Apply position filter
if (isset($_GET['position']) && !empty($_GET['position'])) {
    $position = mysqli_real_escape_string($con, $_GET['position']);
    $sql .= " AND u.role = '$position'";
}

// Apply date filters
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $startDate = mysqli_real_escape_string($con, $_GET['start_date']);
    $sql .= " AND c.end_date >= '$startDate'";
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $endDate = mysqli_real_escape_string($con, $_GET['end_date']);
    $sql .= " AND c.end_date <= '$endDate'";
}

// Order by contract_id DESC to get the most recent contract first
$sql .= " ORDER BY u.user_id, c.contract_id DESC";

$result = mysqli_query($con, $sql);

// Group results to get only the most recent contract per user
$employees = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Only add the first occurrence of each user (which is their most recent contract)
        if (!isset($employees[$row['user_id']])) {
            $employees[$row['user_id']] = $row;
        }
    }
}
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Employee Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <h1>HR Employee Management</h1>
                    <p>Manage employee roles and company workforce</p>
                </div>
            </div>
        </div>
    </section>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'removed'): ?>
    <div class="alert alert-success">Employee removed successfully.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'promoted'): ?>
    <div class="alert alert-success">Employee promoted successfully.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'contract_active'): ?>
    <div class="alert alert-danger">Cannot remove employee - their contract has not ended yet.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'promotion_error'): ?>
    <div class="alert alert-danger">Error promoting employee. Please try again.</div>
    <?php endif; ?>

    <!-- Main Content -->
    <section class="padding-30">
        <div class="container">
            <!-- Filter Section -->
            <div class="row mb-30">
                <div class="col-md-12">
                    <div class="service-item box-shadow padding-15">
                        <form method="GET" action="hr-manage-emp.php" id="filterForm">
                            <div class="row">
                                <!-- Search -->
                                <div class="col-md-3 padding-10">
                                    <input type="text" id="search" name="search" class="form-control" placeholder="Search employees..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                </div>
                                <!-- Position -->
                                <div class="col-md-3 padding-10">
                                    <select id="position" name="position" class="form-control" style="height: 40px; padding: 8px 12px;">
                                        <option value="">All Positions</option>
                                        <?php
                                        $rolesSql = "SELECT DISTINCT role FROM user WHERE role NOT IN ('Admin', 'CLIENT') ORDER BY role";
                                        $rolesResult = mysqli_query($con, $rolesSql);
                                        if ($rolesResult && mysqli_num_rows($rolesResult) > 0) {
                                            while ($roleRow = mysqli_fetch_assoc($rolesResult)) {
                                                $selected = (isset($_GET['position']) && $_GET['position'] === $roleRow['role']) ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($roleRow['role']) . '" ' . $selected . '>' . htmlspecialchars($roleRow['role']) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <!-- Contract End Date From -->
                                <div class="col-md-2 padding-10">
                                    <input type="date" id="start_date" name="start_date" class="form-control" placeholder="From Date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>" title="Contract end from">
                                </div>
                                <!-- Contract End Date To -->
                                <div class="col-md-2 padding-10">
                                    <input type="date" id="end_date" name="end_date" class="form-control" placeholder="To Date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>" title="Contract end to">
                                </div>
                                <!-- Filter Button -->
                                <div class="col-md-2 padding-10">
                                    <button type="submit" class="default-btn" style="width: 100%; padding: 10px;">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <?php if (!empty($_GET)): ?>
                                    <a href="hr-manage-emp.php" class="default-btn" style="width: 100%; padding: 10px; margin-top: 5px; display: inline-block; text-align: center; background: #999;">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Employee Grid -->
            <div class="row">
                <?php
                if (!empty($employees)) {
                    foreach ($employees as $row) {
                        $initials = strtoupper(substr($row['full_name'], 0, 1) . substr(strstr($row['full_name'], ' '), 1, 1));
                        
                        // Check if contract has ended
                        $canTakeAction = false;
                        if ($row['end_date']) {
                            $endDate = strtotime($row['end_date']);
                            $today = strtotime(date('Y-m-d'));
                            $canTakeAction = ($today >= $endDate);
                        }

                        echo '<div class="col-lg-4 col-md-6 padding-10">';
                        echo '  <div class="service-item box-shadow" style="padding: 15px;">';
                        echo '    <div class="employee-header">';
                        echo '      <div class="employee-avatar">' . htmlspecialchars($initials) . '</div>';
                        echo '      <div class="employee-info">';
                        echo '        <h3>' . htmlspecialchars($row['full_name']) . '</h3>';
                        echo '        <span class="employee-role">' . htmlspecialchars($row['role']) . '</span>';
                        echo '      </div>';
                        echo '    </div>';
                        echo '    <div class="employee-details">';
                        echo '      <div class="detail-row"><i class="fas fa-envelope"></i> <span>' . htmlspecialchars($row['email']) . '</span></div>';
                        echo '      <div class="detail-row"><i class="fas fa-phone"></i> <span>' . htmlspecialchars($row['phone']) . '</span></div>';
                        
                        if ($row['contract_id']) {
                            echo '  <div class="detail-row"><i class="fas fa-id-badge"></i> <span>Contract ID: ' . htmlspecialchars($row['contract_id']) . '</span></div>';
                            
                            if (!empty($row['position'])) {
                                echo '  <div class="detail-row"><i class="fas fa-briefcase"></i> <span>Position: ' . htmlspecialchars($row['position']) . '</span></div>';
                            }
                            
                            echo '  <div class="detail-row"><i class="fas fa-calendar-alt"></i> <span>End Date: ' . htmlspecialchars($row['end_date']) . '</span></div>';
                            echo '  <div class="detail-row"><i class="fas fa-file-signature"></i> <span>Signing: ' . htmlspecialchars($row['signing_date']) . '</span></div>';
                            echo '  <div class="detail-row"><i class="fas fa-dollar-sign"></i> <span>Salary: $' . number_format($row['salary'], 2) . '</span></div>';
                        } else {
                            echo '  <div class="detail-row"><span>No contract found</span></div>';
                        }
                        
                        echo '    </div>';
                        echo '    <div class="dl-btn-group mt-2" style="gap: 5px;">';
                        
                        if ($canTakeAction) {
                            echo '      <button type="button" class="dl-btn" style="background:#4caf50;" onclick="openPromoteModal(' . $row['user_id'] . ', \'' . htmlspecialchars(addslashes($row['full_name'])) . '\', \'' . htmlspecialchars(addslashes($row['role'])) . '\', \'' . htmlspecialchars(addslashes($row['email'])) . '\', ' . ($row['salary'] ? $row['salary'] : 0) . ')">';
                            echo '        <i class="fas fa-arrow-up"></i> Promote';
                            echo '      </button>';
                            echo '      <button type="button" class="dl-btn" style="background:#f44336;" onclick="openRemoveModal(' . $row['user_id'] . ', \'' . htmlspecialchars(addslashes($row['full_name'])) . '\', \'' . htmlspecialchars(addslashes($row['role'])) . '\', \'' . htmlspecialchars(addslashes($row['email'])) . '\', \'' . htmlspecialchars(addslashes($row['phone'])) . '\')">';
                            echo '        <i class="fas fa-user-times"></i> Remove';
                            echo '      </button>';
                        } else {
                            echo '      <button type="button" class="dl-btn" style="background:#999; cursor:not-allowed;" disabled title="Contract has not ended yet">';
                            echo '        <i class="fas fa-arrow-up"></i> Promote';
                            echo '      </button>';
                            echo '      <button type="button" class="dl-btn" style="background:#999; cursor:not-allowed;" disabled title="Contract has not ended yet">';
                            echo '        <i class="fas fa-user-times"></i> Remove';
                            echo '      </button>';
                        }
                        
                        echo '    </div>';
                        echo '  </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="col-12"><p style="text-align: center; padding: 20px; font-size: 16px; color: #666;">No employees found matching your filters.</p></div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Promote Modal -->
    <div id="promoteModal" class="land-modal">
        <div class="land-modal-content" style="max-width: 500px;">
            <div class="land-modal-header">
                <h3>Promote Employee</h3>
                <span class="land-modal-close" onclick="closeModal('promoteModal')">&times;</span>
            </div>
            <div class="land-modal-body">
                <div class="application-info mb-3" style="padding: 10px; background: #f9f9f9; border-radius: 5px;">
                    <div style="text-align: center; margin-bottom: 15px;">
                        <div class="employee-avatar" style="width: 80px; height: 80px; font-size: 32px; margin: 0 auto 10px;" id="promoteAvatar">JS</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 20px;" id="promoteEmployeeName">John Smith</h3>
                        <span class="employee-role" id="promoteCurrentRole">Current: Surveyor</span>
                    </div>
                    <div style="font-size: 14px; color: #666;">
                        <div style="padding: 8px 0;"><strong>Email:</strong> <span id="promoteEmployeeEmail">john.smith@company.com</span></div>
                        <div style="padding: 8px 0;"><strong>Current Salary:</strong> $<span id="promoteCurrentSalary">50000</span></div>
                    </div>
                </div>

                <form method="post" action="hr-manage-emp.php" id="promoteForm">
                    <input type="hidden" name="promote_user_id" id="promoteUserId">
                    <input type="hidden" name="confirm_promotion" value="1">
                    
                    <div class="form-group">
                        <label>New Position</label>
                        <select class="form-control" name="new_position" required>
                            <option value="">Select New Position</option>
                            <?php
                            $rolesSql = "SELECT DISTINCT role FROM user WHERE role NOT IN ('Admin', 'CLIENT') ORDER BY role";
                            $rolesResult = mysqli_query($con, $rolesSql);
                            
                            if ($rolesResult && mysqli_num_rows($rolesResult) > 0) {
                                while ($roleRow = mysqli_fetch_assoc($rolesResult)) {
                                    echo '<option value="' . htmlspecialchars($roleRow['role']) . '">' . htmlspecialchars($roleRow['role']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>New Salary</label>
                        <input type="number" class="form-control" name="new_salary" placeholder="65000" required min="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label>Contract Start Date</label>
                        <input type="date" class="form-control" name="contract_start" required>
                    </div>

                    <div class="form-group">
                        <label>Contract End Date</label>
                        <input type="date" class="form-control" name="contract_end" required>
                    </div>

                    <div class="form-group">
                        <label>Contract Signing Date</label>
                        <input type="date" class="form-control" name="signing_date" required>
                    </div>

                    <div class="form-group text-right" style="gap: 10px; display: flex; justify-content: flex-end;">
                        <button type="button" class="dl-btn" style="background: #999; padding: 8px 15px;" onclick="closeModal('promoteModal')">Cancel</button>
                        <button type="submit" class="dl-btn" style="background: #4caf50; padding: 8px 15px;">Confirm Promotion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Remove Employee Modal -->
    <div id="removeModal" class="land-modal">
        <div class="land-modal-content" style="max-width: 500px;">
            <div class="land-modal-header">
                <h3>Remove Employee</h3>
                <span class="land-modal-close" onclick="closeModal('removeModal')">&times;</span>
            </div>
            <div class="land-modal-body">
                <div class="application-info mb-3" style="padding:10px; background:#f9f9f9; border-radius:5px;">
                    <h3 id="removeEmployeeName"></h3>
                    <span class="employee-role" id="removeEmployeeRole"></span>
                    <p><strong>Email:</strong> <span id="removeEmployeeEmail"></span></p>
                    <p><strong>Phone:</strong> <span id="removeEmployeePhone"></span></p>
                </div>

                <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:6px; padding:15px; margin-bottom:20px;">
                    <p style="margin:0; font-size:14px; color:#856404;">
                        <strong>Warning:</strong> This action will permanently remove the employee from the system.
                    </p>
                </div>

                <form method="post" action="hr-manage-emp.php" id="removeEmployeeForm">
                    <input type="hidden" name="user_id" id="removeUserId">
                    <input type="hidden" name="confirm_removal" value="1">
                    <div class="form-group text-right" style="gap:10px; display:flex; justify-content:flex-end;">
                        <button type="button" class="dl-btn" style="background:#999;" onclick="closeModal('removeModal')">Cancel</button>
                        <button type="submit" class="dl-btn" style="background:#f44336;">
                            <i class="fas fa-user-times"></i> Confirm Removal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function openPromoteModal(userId, fullName, currentRole, email, currentSalary) {
            const nameParts = fullName.split(' ');
            const initials = (nameParts[0]?.charAt(0) || '') + (nameParts[1]?.charAt(0) || '');
            
            document.getElementById('promoteUserId').value = userId;
            document.getElementById('promoteAvatar').textContent = initials.toUpperCase();
            document.getElementById('promoteEmployeeName').textContent = fullName;
            document.getElementById('promoteCurrentRole').textContent = 'Current: ' + currentRole;
            document.getElementById('promoteEmployeeEmail').textContent = email;
            document.getElementById('promoteCurrentSalary').textContent = currentSalary;
            
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="contract_start"]').value = today;
            document.querySelector('input[name="signing_date"]').value = today;
            
            openModal('promoteModal');
        }

        function openRemoveModal(userId, fullName, role, email, phone) {
            document.getElementById('removeUserId').value = userId;
            document.getElementById('removeEmployeeName').textContent = fullName;
            document.getElementById('removeEmployeeRole').textContent = role;
            document.getElementById('removeEmployeeEmail').textContent = email;
            document.getElementById('removeEmployeePhone').textContent = phone;
            openModal('removeModal');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('land-modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
    
    <?php include 'includes/footer.html'; ?>
</body>
</html>