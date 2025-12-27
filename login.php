
<?php  
include 'includes/connect.php';
include 'includes/header.php';
define('SALT' , 'd#f453dd');
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = $_POST['password'];
   $password_user = md5(SALT.md5(SALT.$password));
    // Check users table
    $user_query = "SELECT * FROM user WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($con, $user_query);
if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['full_name'] = $row['full_name'];

        // Role check
        if ($row['role'] === "Admin") {
            header("Location: admin-services.php");
        } 
        else if ($row['role'] === "Client") {
            header("Location: index.php");
        }
        else if ($row['role'] === "Surveyor") {
            header("Location: surveyor-deliverables.php");
        }
        else if ($row['role'] === "LeadEngineer") {
            header("Location: lead_eng-dashboard.php");
        }
        else if ($row['role'] === "HR") {
            header("Location: hr-applications.php");
        }
        else if ($row['role'] === "Sales_Person") {
            header("Location: admin-selling.php");
        }
        else if ($row['role'] === "Maintenance_Technician") {
            header("Location: maintenance.php");
        }
        exit();
    } else {
        $error= "Invalid email or password";
    }
}


?>
<!doctype html>
<html class="no-js" lang="en"> 
<body>
<div class="site-preloader-wrap">
<div class="spinner"></div>
</div>




<!-- Login Section -->
<section class="contact-section padding " style="min-height: calc(100vh - 100px); display: flex; align-items: center; position: relative; z-index: 1;">
<div class="container">
    <?php if($error){ ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php }?>
<div class="row justify-content-center">
<div class="col-lg-5 col-md-6">
<div class="contact-form box-shadow" style="background: #fff; padding: 40px; border-radius: 5px;">
<div class="text-center mb-30">
<h2 style="margin-bottom: 10px;">Welcome Back</h2>
<p style="color: #8d9aa8;">Login to your account</p>
</div>
<form method="post"class="form-horizontal">
<div class="form-group colum-row row">
<div class="col-md-12">
<input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
</div>
</div>
<div class="form-group colum-row row">
<div class="col-md-12">
<input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
</div>
</div>
<div class="form-group row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
<div class="col-md-6">
<div style="display: flex; align-items: center;">
<input type="checkbox" id="remember" style="margin-right: 8px;">
<label for="remember" style="margin-bottom: 0; font-size: 14px;">Remember me</label>
</div>
</div>
<div class="col-md-6 text-right">
<a href="#" style="color: #ff7607; font-size: 14px;">Forgot Password?</a>
</div>
</div>
<div class="form-group row">
<div class="col-md-12">
<button type="submit" class="default-btn" style="width: 100%;">Login</button>
</div>
</div>
<div class="text-center mt-4" style="border-top: 1px solid #e5e5e5; padding-top: 20px;">
<p style="margin-bottom: 0; color: #8d9aa8;">Don't have an account? <a href="signup.php" style="color: #ff7607; font-weight: 600;">Sign up</a></p>
</div>
</form>
</div>
</div>
</div>
</div>
</section>

<script src="js/vendor/jquery-1.12.4.min.js"></script>
<script src="js/vendor/bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>