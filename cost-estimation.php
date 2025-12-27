<?php
include 'includes/header.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Client") {
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
<section class="page-header padding">
<div class="container">
<div class="page-content text-center">
<h2>Land Cost Estimator</h2>
<p>Get an instant estimate for your land property</p>
</div>
</div>
</section>

<!-- Estimator Section -->
<section class="contact-section padding bg-grey">
<div class="dots"></div>
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-8 col-md-10">
<div class="section-heading text-center mb-40">
<span>Smart Pricing</span>
<h2>Land Value Calculator</h2>
<p>Enter your land details to get an instant market value estimate</p>
</div>
<div class="contact-form box-shadow" style="background: #fff; padding: 40px; border-radius: 5px;">
<form class="form-horizontal" id="land-estimator-form">
<div class="form-group colum-row row">
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Land Area (Acres)</label>
<input type="number" class="form-control" id="land-area" placeholder="Enter area in acres" min="0.1" step="0.1" required>
</div>
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Property Type</label>
<select class="form-control" id="property-type" required>
<option value="">Select Type</option>
<option value="residential">Residential</option>
<option value="commercial">Commercial</option>
<option value="industrial">Industrial</option>
<option value="agricultural">Agricultural</option>
</select>
</div>
</div>
<div class="form-group colum-row row">
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Location Zone</label>
<select class="form-control" id="location-zone" required>
<option value="">Select Zone</option>
<option value="prime">Prime Urban</option>
<option value="urban">Urban</option>
<option value="suburban">Suburban</option>
<option value="rural">Rural</option>
<option value="remote">Remote</option>
</select>
</div>
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Topography</label>
<select class="form-control" id="topography" required>
<option value="">Select Topography</option>
<option value="flat">Flat</option>
<option value="gently-sloping">Gently Sloping</option>
<option value="hilly">Hilly</option>
<option value="waterfront">Waterfront</option>
</select>
</div>
</div>
<div class="form-group colum-row row">
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Utilities Available</label>
<div style="display: flex; flex-wrap: wrap; gap: 10px;">
<div style="display: flex; align-items: center;">
<input type="checkbox" id="water" value="water" style="margin-right: 5px;">
<label for="water" style="margin-bottom: 0; font-size: 14px;">Water</label>
</div>
<div style="display: flex; align-items: center;">
<input type="checkbox" id="electricity" value="electricity" style="margin-right: 5px;">
<label for="electricity" style="margin-bottom: 0; font-size: 14px;">Electricity</label>
</div>
<div style="display: flex; align-items: center;">
<input type="checkbox" id="sewer" value="sewer" style="margin-right: 5px;">
<label for="sewer" style="margin-bottom: 0; font-size: 14px;">Sewer</label>
</div>
<div style="display: flex; align-items: center;">
<input type="checkbox" id="gas" value="gas" style="margin-right: 5px;">
<label for="gas" style="margin-bottom: 0; font-size: 14px;">Gas</label>
</div>
</div>
</div>
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Road Access</label>
<select class="form-control" id="road-access" required>
<option value="">Select Access</option>
<option value="paved">Paved Road</option>
<option value="gravel">Gravel Road</option>
<option value="dirt">Dirt Road</option>
<option value="limited">Limited Access</option>
</select>
</div>
</div>
<div class="form-group row">
<div class="col-md-12">
<button type="submit" class="default-btn" style="width: 100%;">Calculate Estimate</button>
</div>
</div>
</form>

<!-- Results Section -->
<div id="estimation-results" style="display: none; margin-top: 30px; padding: 20px; background: #f9fafa; border-radius: 5px; border-left: 4px solid #ff7607;">
<div class="text-center">
<h3 style="color: #263a4f; margin-bottom: 15px;">Estimated Land Value</h3>
<div id="estimated-price" style="font-size: 36px; font-weight: 700; color: #ff7607; margin-bottom: 10px;">$0</div>
<p style="color: #8d9aa8; margin-bottom: 20px;">Based on current market data and your inputs</p>
<div class="row">
<div class="col-md-6">
<div style="background: #fff; padding: 15px; border-radius: 3px; margin-bottom: 10px;">
<small style="color: #8d9aa8;">Price per Acre</small>
<div id="price-per-acre" style="font-weight: 600; color: #263a4f;">$0</div>
</div>
</div>
<div class="col-md-6">
<div style="background: #fff; padding: 15px; border-radius: 3px; margin-bottom: 10px;">
<small style="color: #8d9aa8;">Confidence Level</small>
<div id="confidence-level" style="font-weight: 600; color: #263a4f;">High</div>
</div>
</div>
</div>
<button class="default-btn" style="margin-top: 15px;" onclick="window.location.href='contact.html'">Get Detailed Valuation</button>
</div>
</div>
</div>
</div>
</div>
</div>
</section>

<!-- How It Works Section -->
<section class="service-section padding">
<div class="container">
<div class="section-heading text-center mb-40">
<span>Our Methodology</span>
<h2>How We Calculate Land Values</h2>
</div>
<div class="row">
<div class="col-lg-3 col-sm-6 padding-15">
<div class="service-item box-shadow text-center">
<i class="flaticon-factory"></i>
<h3>Market Analysis</h3>
<p>We analyze recent sales data and market trends in your area</p>
</div>
</div>
<div class="col-lg-3 col-sm-6 padding-15">
<div class="service-item box-shadow text-center">
<i class="flaticon-worker"></i>
<h3>Location Factors</h3>
<p>Prime locations, accessibility, and neighborhood development</p>
</div>
</div>
<div class="col-lg-3 col-sm-6 padding-15">
<div class="service-item box-shadow text-center">
<i class="flaticon-gear"></i>
<h3>Property Features</h3>
<p>Size, topography, utilities, and development potential</p>
</div>
</div>
<div class="col-lg-3 col-sm-6 padding-15">
<div class="service-item box-shadow text-center">
<i class="flaticon-control-system"></i>
<h3>Zoning & Regulations</h3>
<p>Local zoning laws and development restrictions</p>
</div>
</div>
</div>
</div>
</section>



<?php include 'includes/footer.html'; ?>
</body>
</html>