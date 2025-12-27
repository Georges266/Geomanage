<?php
include 'includes/header.php';
if (isset($_SESSION['role']) && $_SESSION['role'] !== "Client") {
    exit();
}
?>
<!doctype html>
<html class="no-js" lang="en"> 

<body>
<div class="site-preloader-wrap">
<div class="spinner"></div>
</div>

<div id="main-slider" class="dl-slider">
<div class="single-slide">
<div class="bg-img kenburns-top-right" style="background-image: url(img/survey-team-working.jpg);"></div>
<div class="overlay"></div>
<div class="slider-content-wrap d-flex align-items-center text-left">
<div class="container">
<div class="slider-content">
<div class="dl-caption medium"><div class="inner-layer"><div data-animation="fade-in-right" data-delay="1s">Professional Land Surveyors</div></div></div>
<div class="dl-caption big"><div class="inner-layer"><div data-animation="fade-in-left" data-delay="2s">Accurate Mapping</div></div></div>
<div class="dl-caption big"><div class="inner-layer"><div data-animation="fade-in-left" data-delay="2.5s">and Boundary Solutions</div></div></div>
<div class="dl-caption small"><div class="inner-layer"><div data-animation="fade-in-left" data-delay="3s">
We deliver precision in every survey — from boundary identification and topographic mapping to construction layout and geodetic analysis.
</div></div></div>
<div class="dl-btn-group">
<div class="inner-layer">
<a href="services.php" class="dl-btn" data-animation="fade-in-left" data-delay="3.5s">Our Services <i class="arrow_right"></i></a>
</div>
</div>
</div>
</div>
</div>
</div>

<div class="single-slide">
<div class="bg-img kenburns-top-right" style="background-image: url(img/topo-mapping.jpg);"></div>
<div class="overlay"></div>
<div class="slider-content-wrap d-flex align-items-center text-center">
<div class="container">
<div class="slider-content">
<div class="dl-caption medium"><div class="inner-layer"><div data-animation="fade-in-top" data-delay="1s">Cutting-Edge Equipment</div></div></div>
<div class="dl-caption big"><div class="inner-layer"><div data-animation="fade-in-bottom" data-delay="2s">Topographic & GPS Surveys</div></div></div>
<div class="dl-caption big"><div class="inner-layer"><div data-animation="fade-in-bottom" data-delay="2.5s">For All Terrains</div></div></div>
<div class="dl-caption small"><div class="inner-layer"><div data-animation="fade-in-bottom" data-delay="3s">
Using GNSS, drones, and total stations to produce accurate digital models and land data for engineering, design, and planning.
</div></div></div>
<div class="dl-btn-group">
<div class="inner-layer">
<a href="land-calculator.php" class="dl-btn" data-animation="fade-in-bottom" data-delay="3.5s">Land Calculator <i class="arrow_right"></i></a>
</div>
</div>
</div>
</div>
</div>
</div>

<div class="single-slide">
<div class="bg-img kenburns-top-right" style="background-image: url(img/construction-staking.jpg);"></div>
<div class="overlay"></div>
<div class="slider-content-wrap d-flex align-items-center text-right">
<div class="container">
<div class="slider-content">
<div class="dl-caption medium"><div class="inner-layer"><div data-animation="fade-in-left" data-delay="1s">Trusted Expertise</div></div></div>
<div class="dl-caption big"><div class="inner-layer"><div data-animation="fade-in-right" data-delay="2s">Delivering Precise Results</div></div></div>
<div class="dl-caption big"><div class="inner-layer"><div data-animation="fade-in-right" data-delay="2.5s">Since 1984</div></div></div>
<div class="dl-caption small"><div class="inner-layer"><div data-animation="fade-in-right" data-delay="3s">
Decades of experience serving engineers, developers, and government institutions with reliable land surveying solutions.
</div></div></div>
<div class="dl-btn-group">
<div class="inner-layer">
<a href="contact.html" class="dl-btn" data-animation="fade-in-right" data-delay="3.5s">Request a Quote <i class="arrow_right"></i></a>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>

<section class="promo-section padding">
<div class="container">
<div class="row promo-wrap">
<div class="col-lg-3 col-sm-6 sm-padding">
<div class="promo-item box-shadow text-center wow fadeInUp" data-wow-delay="100ms">
<i class="flaticon-factory"></i>
<h3>Licensed Professionals</h3>
<p>Our certified surveyors ensure every project meets legal and technical standards with unmatched precision.</p>
</div>
</div>
<div class="col-lg-3 col-sm-6 sm-padding">
<div class="promo-item box-shadow text-center wow fadeInUp" data-wow-delay="300ms">
<i class="flaticon-worker"></i>
<h3>Client-Centered Approach</h3>
<p>We prioritize communication, transparency, and accuracy to deliver results that exceed expectations.</p>
</div>
</div>
<div class="col-lg-3 col-sm-6 sm-padding">
<div class="promo-item box-shadow text-center wow fadeInUp" data-wow-delay="400ms">
<i class="flaticon-gear"></i>
<h3>Advanced Technology</h3>
<p>We use total stations, GPS, and drones to capture detailed spatial data efficiently and safely.</p>
</div>
</div>
<div class="col-lg-3 col-sm-6 sm-padding">
<div class="promo-item box-shadow text-center wow fadeInUp" data-wow-delay="500ms">
<i class="flaticon-worker-1"></i>
<h3>Reliable Results</h3>
<p>Accuracy and professionalism define every survey we deliver — on time and on budget.</p>
</div>
</div>
</div>
</div>
</section>

<section class="service-section section-2 bg-grey padding">
<div class="dots"></div>
<div class="container">
<div class="row d-flex align-items-center">
<div class="col-lg-6 sm-padding">
<div class="service-content wow fadeInLeft">
<span>Explore Our Expertise</span>
<h2>Comprehensive Land Surveying Solutions</h2>
<p>GeoManage provides an integrated range of surveying services, from boundary demarcation and topographic mapping to subdivision design and construction layout. Our goal is precision, reliability, and efficiency in every project.</p>
<p>We serve residential, commercial, and governmental clients with the latest surveying technology and experienced professionals.</p>
<a href="#" class="default-btn">View All Services</a>
</div>
</div>
<div class="col-lg-6 sm-padding">
<div class="row services-list">
<div class="col-md-6 padding-15">
<div class="service-item box-shadow wow fadeInUp" data-wow-delay="100ms">
<i class="flaticon-loader"></i>
<h3>Topographic Surveys</h3>
<p>Detailed elevation and contour mapping to support construction, design, and land development.</p>
</div>
</div>
<div class="col-md-6 padding-15 offset-top">
<div class="service-item box-shadow wow fadeInUp" data-wow-delay="300ms">
<i class="flaticon-tanks"></i>
<h3>Boundary Surveys</h3>
<p>Accurate identification and verification of property lines for ownership and legal documentation.</p>
</div>
</div>
<div class="col-md-6 padding-15">
<div class="service-item box-shadow wow fadeInUp" data-wow-delay="400ms">
<i class="flaticon-refinery"></i>
<h3>Construction Staking</h3>
<p>Precise layout services to guide engineers and contractors during construction and development phases.</p>
</div>
</div>
<div class="col-md-6 padding-15 offset-top">
<div class="service-item box-shadow wow fadeInUp" data-wow-delay="500ms">
<i class="flaticon-control-system"></i>
<h3>GPS & Drone Mapping</h3>
<p>High-resolution aerial mapping and GNSS data collection for large-scale land analysis and planning.</p>
</div>
</div>
</div>
</div>
</div>
</div>
</section>

<section class="counter-section padding">
<div class="container">
<div class="row counter-wrap">
<div class="col-lg-3 col-sm-6 padding-15">
<div class="counter-content wow fadeInUp" data-wow-delay="100ms">
<div class="counter"><span class="odometer" data-count="150">00</span></div>
<h4>Clients Served</h4>
</div>
</div>
<div class="col-lg-3 col-sm-6 padding-15">
<div class="counter-content wow fadeInUp" data-wow-delay="200ms">
<div class="counter"><span class="odometer" data-count="500">00</span></div>
<h4>Surveys Completed</h4>
</div>
</div>
<div class="col-lg-3 col-sm-6 padding-15">
<div class="counter-content wow fadeInUp" data-wow-delay="300ms">
<div class="counter"><span class="odometer" data-count="10">00</span></div>
<h4>Years of Experience</h4>
</div>
</div>
<div class="col-lg-3 col-sm-6 padding-15">
<div class="counter-content wow fadeInUp" data-wow-delay="400ms">
<div class="counter"><span class="odometer" data-count="20">00</span></div>
<h4>Expert Team Members</h4>
</div>
</div>
</div>
</div>
</section>

<section class="projects-section padding">
<div class="container">
<div class="row d-flex align-items-center">
<div class="col-lg-8 col-md-6 sm-padding">
<div class="section-heading mb-40">
<span>Projects</span>
<h2>Recent Land Surveying Projects</h2>
</div>
</div>
<div class="col-lg-4 col-md-6 sm-padding text-right">
<a href="#" class="default-btn">View All Projects</a>
</div>
</div>
<div id="projects-carousel" class="projects-carousel box-shadow owl-carousel">
<div class="project-item">
<img src="img/project-1.jpg" alt="projects">
<div class="overlay"></div>
<a href="img/project-1.jpg" class="view-icon img-popup" data-gall="project"> <i class="fas fa-expand"></i></a>
<div class="projects-content">
<a href="#" class="category">Topographic Survey</a>
<h3><a href="#" class="tittle">Residential Terrain Mapping</a></h3>
</div>
</div>
<div class="project-item">
<img src="img/project-2.jpg" alt="projects">
<div class="overlay"></div>
<a href="img/project-2.jpg" class="view-icon img-popup" data-gall="project"> <i class="fas fa-expand"></i></a>
<div class="projects-content">
<a href="#" class="category">Boundary Survey</a>
<h3><a href="#" class="tittle">Subdivision Demarcation Project</a></h3>
</div>
</div>
<div class="project-item">
<img src="img/project-3.jpg" alt="projects">
<div class="overlay"></div>
<a href="img/project-3.jpg" class="view-icon img-popup" data-gall="project"> <i class="fas fa-expand"></i></a>
<div class="projects-content">
<a href="#" class="category">Drone Mapping</a>
<h3><a href="#" class="tittle">Aerial Mapping for Construction Site</a></h3>
</div>
</div>
<div class="project-item">
<img src="img/project-4.jpg" alt="projects">
<div class="overlay"></div>
<a href="img/project-4.jpg" class="view-icon img-popup" data-gall="project"> <i class="fas fa-expand"></i></a>
<div class="projects-content">
<a href="#" class="category">Engineering Survey</a>
<h3><a href="#" class="tittle">Bridge Alignment Layout</a></h3>
</div>
</div>
<div class="project-item">
<img src="img/project-5.jpg" alt="projects">
<div class="overlay"></div>
<a href="img/project-5.jpg" class="view-icon img-popup" data-gall="project"> <i class="fas fa-expand"></i></a>
<div class="projects-content">
<a href="#" class="category">Construction Staking</a>
<h3><a href="#" class="tittle">Urban Development Layout</a></h3>
</div>
</div>
</div>
</div>
</section>

<div class="mapouter">
<div class="gmap_canvas">
<iframe width="100%" height="350" id="gmap_canvas" src="https://maps.google.com/maps?q=GeoManage%20Lebanon&amp;t=&amp;z=11&amp;ie=UTF8&amp;iwloc=&amp;output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
</div>
</div>

<div class="sponsor-section bg-grey">
<div></div>
<div class="container">
<div id="sponsor-carousel" class="sponsor-carousel owl-carousel">
<div class="sponsor-item"><img src="img/sponsor1.png" alt="sponsor"></div>
<div class="sponsor-item"><img src="img/sponsor2.png" alt="sponsor"></div>
<div class="sponsor-item"><img src="img/sponsor3.png" alt="sponsor"></div>
<div class="sponsor-item"><img src="img/sponsor4.png" alt="sponsor"></div>
<div class="sponsor-item"><img src="img/sponsor5.png" alt="sponsor"></div>
<div class="sponsor-item"><img src="img/sponsor6.png" alt="sponsor"></div>
<div class="sponsor-item"><img src="img/sponsor7.png" alt="sponsor"></div>
<div class="sponsor-item"><img src="img/sponsor8.png" alt="sponsor"></div>
</div>
</div>
</div>



<?php include 'includes/footer.html'; ?>
</body>
</html>
