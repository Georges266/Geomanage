<?php 
include 'includes/connect.php';
define('SALT' , 'd#f453dd');
session_start(); // allows auto login after registration

if (isset($_POST['submit'])) {
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $lname = mysqli_real_escape_string($con, $_POST['lname']);
    $fullname = trim($fname . " " . $lname);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $phonenb = mysqli_real_escape_string($con, $_POST['phonenb']);

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<div class="alert alert-danger">Invalid email format.</div>';
    } 
    // Passwords must match
    else if ($password !== $password2) {
        echo '<div class="alert alert-danger">Passwords do not match.</div>';
    } 
    // Password must be strong
    else if (!isStrong($password)) {
        echo '<div class="alert alert-danger">Password must contain at least one uppercase, lowercase, number, special character, and be longer than 8 characters.</div>';
    } 
    else {
        // Check if email exists
        $check_email = "SELECT * FROM user WHERE email = '$email'";
        $result = mysqli_query($con, $check_email);

        if (mysqli_num_rows($result) > 0) {
            echo '<div class="alert alert-danger">Email already registered.</div>';
        } else {
            // Create account
            $hashedPassword = md5(SALT.md5(SALT.$password));
            $created_at = date('Y-m-d H:i:s');

            $insert = "INSERT INTO user (full_name, email, password, phone, salary, role, created_at) 
                       VALUES ('$fullname', '$email', '$hashedPassword', '$phonenb', 0.00, 'Client', '$created_at')";

            if (mysqli_query($con, $insert)) {
                $user_id = mysqli_insert_id($con);
                $client_insert = "INSERT INTO client (user_id, address) VALUES ('$user_id', '')";
                mysqli_query($con, $client_insert);

                // Automatically log the user in
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $email;
                $_SESSION['fullname'] = $fullname;
                $_SESSION['role'] = 'Client';

                // Redirect to homepage or dashboard
                echo '<script>window.location.href = "index.php";</script>';
                exit;
            } else {
                echo '<div class="alert alert-danger">An unexpected error occurred. Please try again later.</div>';
            }
        }
    }
}

// Password strength function
function isStrong($p)
{
    $uppercase = preg_match('@[A-Z]@', $p);
    $lowercase = preg_match('@[a-z]@', $p);
    $number    = preg_match('@[0-9]@', $p);
    $specialChars = preg_match('@[^\w]@', $p);
    $length  = strlen($p);

    return $uppercase && $lowercase && $number && $specialChars && $length > 8;
}
?>
