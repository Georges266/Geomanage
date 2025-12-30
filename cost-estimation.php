<?php
include 'includes/header.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "Client") {
    exit();
}
?>

<!doctype html>
<html class="no-js" lang="en"> 

<body>
    <style>
.form-control, 
select.form-control {
    font-size: 14px !important;
    color: #263a4f !important;
    background-color: #ffffff !important;
    border: 1px solid #ddd !important;
    padding: 10px 12px !important;
    height: auto !important;
}

.form-control option,
select.form-control option {
    font-size: 14px !important;
    color: #263a4f !important;
    background-color: #ffffff !important;
    padding: 8px !important;
}

.form-control option[value=""] {
    color: #999999 !important;
}

.form-control option:checked {
    background-color: #ff7607 !important;
    color: #ffffff !important;
}
</style>
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
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Area (Square Meters)</label>
<input type="number" class="form-control" id="area" placeholder="Enter area in sqm" min="1" step="1" required>
</div>
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Distance to Town Center (km)</label>
<input type="number" class="form-control" id="distance" placeholder="Distance in km" min="0" step="0.1" required>
</div>
</div>

<div class="form-group colum-row row">
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Latitude</label>
<input type="number" class="form-control" id="latitude" placeholder="e.g., 34.0522" step="0.0001" required>
<small style="color: #8d9aa8; margin-top: 5px; display: block;">Right-click on Google Maps to get coordinates</small>
</div>
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Longitude</label>
<input type="number" class="form-control" id="longitude" placeholder="e.g., -118.2437" step="0.0001" required>
<button type="button" class="btn btn-sm btn-secondary" id="get-location-btn" style="margin-top: 5px; padding: 5px 15px; font-size: 13px;">
<i class="fa fa-location-arrow"></i> Use My Location
</button>
<small style="color: #8d9aa8; margin-top: 5px; display: block;">Click this if you're currently at the property</small>
</div>
</div>

<div class="form-group colum-row row">
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Property Type</label>
<select class="form-control" id="property-type" required>
<option value="">Select Type</option>
<option value="Residential">Residential</option>
<option value="Commercial">Commercial</option>
<option value="Industrial">Industrial</option>
<option value="Agricultural">Agricultural</option>
</select>
</div>
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Zoning</label>
<select class="form-control" id="zoning" required>
<option value="">Select Zoning</option>
<option value="R-1 Single Family">R-1 Single Family</option>
<option value="R-2 Multi-Family">R-2 Multi-Family</option>
<option value="R-3 High Density">R-3 High Density</option>
<option value="C-1 Commercial">C-1 Commercial</option>
<option value="C-2 Heavy Commercial">C-2 Heavy Commercial</option>
<option value="I-1 Light Industrial">I-1 Light Industrial</option>
<option value="I-2 Heavy Industrial">I-2 Heavy Industrial</option>
<option value="A-1 Agricultural">A-1 Agricultural</option>
</select>
</div>
</div>

<div class="form-group colum-row row">
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Road Type</label>
<select class="form-control" id="road-type" required>
<option value="">Select Road Type</option>
<option value="Asphalt">Asphalt</option>
<option value="Highway">Highway</option>
<option value="Local Road">Local Road</option>
<option value="Paved">Paved</option>
<option value="Secondary Road">Secondary Road</option>
<option value="Unpaved">Unpaved</option>
</select>
</div>
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Topography</label>
<select class="form-control" id="topography" required>
<option value="">Select Topography</option>
<option value="Flat">Flat</option>
<option value="Gently Sloped">Gently Sloped</option>
<option value="Moderately Sloped">Moderately Sloped</option>
<option value="Hilly">Hilly</option>
<option value="Steeply Sloped">Steeply Sloped</option>
</select>
</div>
</div>

<div class="form-group colum-row row">
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Water Supply</label>
<select class="form-control" id="water" required>
<option value="">Select Water Supply</option>
<option value="Municipal">Municipal</option>
<option value="Well">Well</option>
<option value="Municipal+Well">Municipal + Well</option>
<option value="Tanker">Tanker</option>
<option value="None">Not Available</option>
</select>
</div>
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Electricity</label>
<select class="form-control" id="electricity" required>
<option value="">Select Availability</option>
<option value="Available">Available</option>
<option value="Not Available">Not Available</option>
</select>
</div>
</div>

<div class="form-group colum-row row">
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Road Access</label>
<select class="form-control" id="road-access" required>
<option value="">Select Access</option>
<option value="Yes">Yes</option>
<option value="No">No</option>
</select>
</div>
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Population Density</label>
<select class="form-control" id="population-density" required>
<option value="">Select Density</option>
<option value="Suburban">Suburban</option>
<option value="Urban">Urban</option>
</select>
</div>
</div>

<div class="form-group colum-row row">
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">School District Rating (3-10)</label>
<select class="form-control" id="school-rating" required>
<option value="">Select Rating</option>
<option value="3">3 - Below Average</option>
<option value="4">4</option>
<option value="5">5 - Average</option>
<option value="6">6</option>
<option value="7">7</option>
<option value="8">8 - Good</option>
<option value="9">9</option>
<option value="10">10 - Excellent</option>
</select>
</div>
<div class="col-md-6">
<label style="font-weight: 600; color: #263a4f; margin-bottom: 8px; display: block;">Median Household Income ($)</label>
<input type="number" class="form-control" id="income" placeholder="e.g., 65000" min="0" step="1000" required>
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
<small style="color: #8d9aa8;">Price per Square Meter</small>
<div id="price-per-sqm" style="font-weight: 600; color: #263a4f;">$0</div>
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
<p>Distance to town center, road access, and population density</p>
</div>
</div>
<div class="col-lg-3 col-sm-6 padding-15">
<div class="service-item box-shadow text-center">
<i class="flaticon-gear"></i>
<h3>Property Features</h3>
<p>Size, topography, utilities, and zoning regulations</p>
</div>
</div>
<div class="col-lg-3 col-sm-6 padding-15">
<div class="service-item box-shadow text-center">
<i class="flaticon-control-system"></i>
<h3>AI-Powered</h3>
<p>Machine learning algorithms trained on 300,000+ properties</p>
</div>
</div>
</div>
</div>
</section>

<script>
// Auto-detect location button
document.getElementById('get-location-btn').addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    // Check if geolocation is supported
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }
    
    // Show loading state
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Getting location...';
    btn.disabled = true;
    
    // Get current position
    navigator.geolocation.getCurrentPosition(
        function(position) {
            // Success - fill in the coordinates
            document.getElementById('latitude').value = position.coords.latitude.toFixed(4);
            document.getElementById('longitude').value = position.coords.longitude.toFixed(4);
            
            btn.innerHTML = '<i class="fa fa-check"></i> Location Found!';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        },
        function(error) {
            // Error handling
            let errorMsg = 'Unable to get location. ';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg += 'Please allow location access.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg += 'Location information unavailable.';
                    break;
                case error.TIMEOUT:
                    errorMsg += 'Request timed out.';
                    break;
                default:
                    errorMsg += 'An unknown error occurred.';
            }
            alert(errorMsg);
            btn.innerHTML = originalText;
            btn.disabled = false;
        },
        {
            enableHighAccuracy: true,
            timeout: 30000, // 30 seconds timeout for geolocation
            maximumAge: 0
        }
    );
});

document.getElementById('land-estimator-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData();
    formData.append('latitude', document.getElementById('latitude').value);
    formData.append('longitude', document.getElementById('longitude').value);
    formData.append('area', document.getElementById('area').value);
    formData.append('distance', document.getElementById('distance').value);
    formData.append('road_access', document.getElementById('road-access').value);
    formData.append('road_type', document.getElementById('road-type').value);
    formData.append('electricity', document.getElementById('electricity').value);
    formData.append('water', document.getElementById('water').value);
    formData.append('property_type', document.getElementById('property-type').value);
    formData.append('zoning', document.getElementById('zoning').value);
    formData.append('school_rating', document.getElementById('school-rating').value);
    formData.append('income', document.getElementById('income').value);
    formData.append('population_density', document.getElementById('population-density').value);
    formData.append('topography', document.getElementById('topography').value);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Calculating... This may take up to 30 seconds';
    submitBtn.disabled = true;
    
    // Send request with longer timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 60000); // 60 second timeout
    
    fetch('predict_handler.php', {
        method: 'POST',
        body: formData,
        signal: controller.signal
    })
    .then(response => response.json())
    .then(data => {
        clearTimeout(timeoutId); // Clear timeout on success
        if (data.success) {
            // Display results
            document.getElementById('estimated-price').textContent = 
                '$' + Math.round(data.predicted_price).toLocaleString();
            document.getElementById('price-per-sqm').textContent = 
                '$' + Math.round(data.price_per_sqm).toLocaleString();
            document.getElementById('estimation-results').style.display = 'block';
            
            // Scroll to results
            document.getElementById('estimation-results').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest' 
            });
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        clearTimeout(timeoutId); // Clear timeout on error
        if (error.name === 'AbortError') {
            alert('Request timed out. Please check your internet connection and try again.');
        } else {
            alert('An error occurred: ' + error.message);
        }
        console.error('Error:', error);
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});
</script>

<?php include 'includes/footer.html'; ?>
</body>
</html>