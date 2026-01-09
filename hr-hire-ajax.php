<?php
// ========================================
// FILE: hr-hire-ajax.php
// ========================================
include 'includes/connect.php';
session_start();

if(isset($_POST['hire'])){
    $applicationId = intval($_POST['application_id']);
    
    // Check current status
    $check_status_query = "SELECT status FROM job_application WHERE application_id = '$applicationId'";
    $status_result = mysqli_query($con, $check_status_query);
    $current_app = mysqli_fetch_assoc($status_result);
    
    if (!$current_app) {
        echo json_encode(['status' => 'error', 'message' => 'Application not found.']);
        exit();
    }

    $valid_hire_statuses = ['Interview Scheduled', 'Interview Rescheduled', 'Under Review'];
    if (!in_array($current_app['status'], $valid_hire_statuses)) {
        echo json_encode(['status' => 'error', 'message' => 'Cannot hire - application status is: ' . $current_app['status']]);
        exit();
    }

    $fullName = mysqli_real_escape_string($con, $_POST['full_name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $role = mysqli_real_escape_string($con, $_POST['role']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    
    $startDate = mysqli_real_escape_string($con, $_POST['start_date']);
    $endDate = mysqli_real_escape_string($con, $_POST['end_date']);
    $signingDate = mysqli_real_escape_string($con, $_POST['signing_date']);
    $salary = (float)$_POST['salary'];
   // $position = mysqli_real_escape_string($con, $_POST['position']);
    
    $createdAt = date('Y-m-d H:i:s');

    mysqli_begin_transaction($con);
    
    try {
        // Insert into user table 
        $insertUser = "INSERT INTO user (full_name, email, password, phone, role, created_at)
                       VALUES ('$fullName', '$email', '$password', '$phone', '$role', '$createdAt')";
        
        if (!mysqli_query($con, $insertUser)) {
            throw new Exception("Error creating user: " . mysqli_error($con));
        }
        
        $userId = mysqli_insert_id($con);

        // Insert into role-specific table
        switch ($role) {
            case 'HR':
                $insertRoleTable = "INSERT INTO hr (user_id) VALUES ('$userId')";
                break;
            
            case 'LeadEngineer':
                $licenseNumber = mysqli_real_escape_string($con, $_POST['license_number'] ?? 'TBD');
                $yearsExperience = isset($_POST['years_experience']) ? (int)$_POST['years_experience'] : 0;
                $insertRoleTable = "INSERT INTO lead_engineer (user_id, license_number, years_experience) 
                                   VALUES ('$userId', '$licenseNumber', '$yearsExperience')";
                break;
            
            case 'Sales_Person':
                $commissionRate = isset($_POST['commission_rate']) ? (float)$_POST['commission_rate'] : 0.00;
                $insertRoleTable = "INSERT INTO sales_person (user_id, commission_rate) 
                                   VALUES ('$userId', '$commissionRate')";
                break;
            
            case 'Maintenance_Technician':
                $insertRoleTable = "INSERT INTO maintenance_technician (user_id) VALUES ('$userId')";
                break;
            
            case 'Surveyor':
                $yearsExperience = isset($_POST['years_experience']) ? (int)$_POST['years_experience'] : 0;
                $status = 'Available';
                $roleDescription = mysqli_real_escape_string($con, $_POST['role_description'] ?? 'General Surveyor');
                
                $insertRoleTable = "INSERT INTO surveyor (user_id, years_experience, status, assigned_date, role_description, project_id) 
                                   VALUES ('$userId', '$yearsExperience', '$status', NULL, '$roleDescription', NULL)";
                break;
            
            default:
                throw new Exception("Invalid role specified");
        }
        
        if (!mysqli_query($con, $insertRoleTable)) {
            throw new Exception("Error inserting into role table: " . mysqli_error($con));
        }
        
        // Insert into contract table
        $insertContract = "INSERT INTO contract (start_date, end_date, signing_date, salary, position, user_id)
                          VALUES ('$startDate','$endDate' , '$signingDate', '$salary', '$role', '$userId')";
        
        if (!mysqli_query($con, $insertContract)) {
            throw new Exception("Error creating contract: " . mysqli_error($con));
        }
        
        // Update application status
        $updateApp = "UPDATE job_application SET status='Hired' WHERE application_id=$applicationId";
        if (!mysqli_query($con, $updateApp)) {
            throw new Exception("Error updating application status: " . mysqli_error($con));
        }
        
        mysqli_commit($con);
        echo json_encode(['status' => 'success', 'message' => 'Candidate hired successfully! User and contract created.']);
        
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>