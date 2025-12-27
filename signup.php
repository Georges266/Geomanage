<?php 
include 'includes/connect.php';
include 'includes/header.php';
?>
<!doctype html>
<html class="no-js" lang="en"> 
<body class="bg-dots">
<div class="site-preloader-wrap">
<div class="spinner"></div>
</div>

<!-- Signup Section -->
<section class="contact-section padding" style="min-height: calc(100vh - 100px); display: flex; align-items: center;">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-6 col-md-8">
<div class="contact-form box-shadow" style="background: #fff; padding: 40px; border-radius: 5px;">
<div class="text-center mb-30">
<h2 style="margin-bottom: 10px;">Create Account</h2>
<p style="color: #8d9aa8;">Join us today</p>
</div>

<!-- Message Container -->
<div id="message-container" style="margin-bottom: 20px;"></div>

<form id="signupForm" method="post" class="form-horizontal">
<div class="form-group colum-row row">
<div class="col-sm-6">
<input type="text" name="fname" class="form-control" placeholder="First Name" required>
</div>
<div class="col-sm-6">
<input type="text" name="lname" class="form-control" placeholder="Last Name" required>
</div>
</div>

<div class="form-group colum-row row">
<div class="col-md-12">
<input type="email" name="email" class="form-control" placeholder="Email Address" required>
</div>
</div>

<div class="form-group colum-row row">
<div class="col-md-12">
<input type="tel" name="phonenb" class="form-control" placeholder="Phone Number">
</div>
</div>

<div class="form-group colum-row row">
<div class="col-sm-6">
<input type="password" name="password" class="form-control" placeholder="Password" required>
</div>
<div class="col-sm-6">
<input type="password" name="password2" class="form-control" placeholder="Confirm Password" required>
</div>
</div>

<div class="form-group row">
<div class="col-md-12">
<div style="display: flex; align-items: center; margin-bottom: 25px;">
<input type="checkbox" id="terms" style="margin-right: 8px;" required>
<label for="terms" style="margin-bottom: 0; font-size: 14px;">I agree to the 
<a href="#" style="color: #ff7607;">Terms</a> and 
<a href="#" style="color: #ff7607;">Privacy Policy</a></label>
</div>
</div>
</div>

<div class="form-group row">
<div class="col-md-12">
<button type="submit" name="submit" class="default-btn" style="width: 100%;">Create Account</button>
</div>
</div>

<div class="text-center mt-4" style="border-top: 1px solid #e5e5e5; padding-top: 20px;">
<p style="margin-bottom: 0; color: #8d9aa8;">Already have an account? 
<a href="login.php" style="color: #ff7607; font-weight: 600;">Login</a></p>
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

<script>
$('#signupForm').submit(function(e) {
    e.preventDefault();
    $.ajax({
        url: 'signup-ajax.php',
        type: 'POST',
        data: $(this).serialize() + '&submit=1',
        success: function(response) {
            $('#message-container').html(response);
            if(response.includes('success')) {
                $('#signupForm')[0].reset();
            }
        }
    });
});
</script>

</body>
</html>