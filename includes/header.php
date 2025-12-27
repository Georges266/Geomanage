<?php
session_start(); 
?>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Construction & Building HTML Template">
<meta name="author" content="AlexaTheme">
<title>Indico | Construction & Building HTML Template</title>
<link rel="shortcut icon" type="image/x-icon" href="img/favicon.png">

<link rel="stylesheet" href="css/fontawesome.min.css">

<link rel="stylesheet" href="css/themify-icons.css">

<link rel="stylesheet" href="css/elegant-line-icons.css">

<link rel="stylesheet" href="css/elegant-font-icons.css">

<link rel="stylesheet" href="css/flaticon.css">

<link rel="stylesheet" href="css/animate.min.css">

<link rel="stylesheet" href="css/bootstrap.min.css">

<link rel="stylesheet" href="css/slick.css">

<link rel="stylesheet" href="css/slider.css">

<link rel="stylesheet" href="css/odometer.min.css">

<link rel="stylesheet" href="css/venobox/venobox.css">

<link rel="stylesheet" href="css/owl.carousel.css">

<link rel="stylesheet" href="css/main.css">

<link rel="stylesheet" href="css/responsive.css">
<script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
<script src="js/vendor/jquery-1.12.4.min.js"></script>
</head>

<header class="header">
  <div class="primary-header">
    <div class="container">
      <div class="primary-header-inner">
        <div class="header-logo">
          <a href="index.html"><img src="img/logo-dark.png" alt="Indico"></a>
        </div>
        <div class="header-menu-wrap">
          <ul class="dl-menu">  
            <!-- Always show home for guests & normal user -->
            <?php if (!isset($_SESSION['user_id']) || (isset($_SESSION['role']) && $_SESSION['role'] === "Client")) { ?>
                <li><a href="index.php">Home</a></li>
                <li><a href="projects.php">Projects</a></li>
                <li><a href="land-sale.php">Lands</a></li>
                
            <?php } ?>

            <!-- Guest only -->
            <?php if (!isset($_SESSION['user_id'])) { ?>
                <li><a href="login.php" class="login-link" style="color: #ff0000; font-family: 'Work Sans', sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 0 30px; height: 80px; line-height: 80px; text-decoration: none; transition: all 0.5s;">Login</a></li>
            <?php } ?>

            <!-- Normal User only -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "Client") { ?>
                <li><a href="client-dashboard.php">My Projects</a></li>
                <li><a href="cost-estimation.php">Land Calculator</a></li>
                <li><a href="job.php">Careers</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a style="color: #ff0000; font-family: 'Work Sans', sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 0 30px; height: 80px; line-height: 80px; text-decoration: none; transition: all 0.5s;" href="logout.php">Logout</a></li>
            <?php } ?>

            <!-- Admin only -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "Admin") { ?>
                <li><a href="admin-statistics.php">Statistics</a></li>
                <li><a href="admin-services.php">Services</a></li>
                <li><a href="admin-project.php">Projects</a></li>
                <li><a href="land-sale.php">Land For Sale</a></li>
                <li><a style="color: #ff0000; font-family: 'Work Sans', sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 0 30px; height: 80px; line-height: 80px; text-decoration: none; transition: all 0.5s;" href="logout.php">Logout</a></li>
            <?php } ?>
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "HR") { ?>
            <li><a href="hr-opportunities.php">management</a></li>
            <li><a href="hr-applications.php">applications</a></li>
            <li><a href="hr-schedule.php">schedule</a></li>
            <li><a href="hr-manage-emp.php">Employees</a></li>
            <li><a href="projects.php">Projects</a></li>
            <li><a href="land-sale.php">Lands</a></li>
            <li><a style="color: #ff0000; font-family: 'Work Sans', sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 0 30px; height: 80px; line-height: 80px; text-decoration: none; transition: all 0.5s;" href="logout.php">Logout</a></li>
            <?php } ?>
             <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "LeadEngineer") { ?>
             <li><a href="lead_eng-dashboard.php"> my projects</a></li>
            <li><a href="lead_eng-equipment.php">equipment</a></li>   
            <li><a href="lead_eng-reviewdeliverables.php">deliverables</a></li> 
             <li><a href="projects.php">Projects</a></li>
            <li><a href="land-sale.php">Lands</a></li>
            <li><a style="color: #ff0000; font-family: 'Work Sans', sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 0 30px; height: 80px; line-height: 80px; text-decoration: none; transition: all 0.5s;" href="logout.php">Logout</a></li>
            <?php } ?>
             <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "Maintenance_Technician") { ?>
            <li><a href="maintenance.php">dashboard</a></li> 
            <li><a href="projects.php">Projects</a></li>
            <li><a href="land-sale.php">Lands</a></li>  
            <li><a style="color: #ff0000; font-family: 'Work Sans', sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 0 30px; height: 80px; line-height: 80px; text-decoration: none; transition: all 0.5s;" href="logout.php">Logout</a></li>
            <?php } ?>
             <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "Surveyor") { ?>
              <li><a href="surveyor-deliverables.php">deliverables</a></li>   
            <li><a style="color: #ff0000; font-family: 'Work Sans', sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 0 30px; height: 80px; line-height: 80px; text-decoration: none; transition: all 0.5s;" href="logout.php">Logout</a></li>
              <?php } ?>
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "Sales_Person") { ?>
                 <li><a href="sales_person.php">Lands</a></li>
                 <li><a href="projects.php">Projects</a></li>
                 <li><a href="land-sale.php">Lands</a></li>
                <li><a style="color: #ff0000; font-family: 'Work Sans', sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 0 30px; height: 80px; line-height: 80px; text-decoration: none; transition: all 0.5s;" href="logout.php">Logout</a></li>
              <?php } ?> 
          </ul>
        </div>
        <div class="header-right">
          <div class="mobile-menu-icon">
            <div class="burger-menu">
              <div class="line-menu line-half first-line"></div>
              <div class="line-menu"></div>
              <div class="line-menu line-half last-line"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>