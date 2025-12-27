<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
include 'includes/connect.php';

// Include PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Get POST data and sanitize
$project_id = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
$project_name = isset($_POST['project_name']) ? mysqli_real_escape_string($con, $_POST['project_name']) : '';
$price = isset($_POST['total_price']) ? mysqli_real_escape_string($con, $_POST['total_price']) : '0';
$client_email = isset($_POST['client_email']) ? mysqli_real_escape_string($con, $_POST['client_email']) : '';
$client_name = isset($_POST['client_name']) ? mysqli_real_escape_string($con, $_POST['client_name']) : 'Valued Client';
$Services = isset($_POST['Services']) ? mysqli_real_escape_string($con, $_POST['Services']) : '';
$Land_Nb = isset($_POST['Land_Nb']) ? mysqli_real_escape_string($con, $_POST['Land_Nb']) : '';
$Land_Address = isset($_POST['Land_Address']) ? mysqli_real_escape_string($con, $_POST['Land_Address']) : '';

// Validate required fields
if ($project_id <= 0 || empty($client_email)) {
    echo "Error: Missing required information.";
    exit;
}

// Check payment status
$paymentQuery = "SELECT payment_status FROM project WHERE project_id = $project_id";
$paymentResult = mysqli_query($con, $paymentQuery);
$paymentData = mysqli_fetch_assoc($paymentResult);
$payment_status = $paymentData['payment_status'] ?? 'pending';
$is_paid = (strtolower($payment_status) === 'paid');

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = '92330378@students.liu.edu.lb';
    $mail->Password   = 'chwg iudv ixdu fkfz';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('92330378@students.liu.edu.lb', 'GeoManage Solutions');
    $mail->addAddress($client_email, $client_name);
    $mail->addReplyTo('support@geomanage.com', 'GeoManage Support');

    // Embed the logo image
    $logoPath = __DIR__ . '/img/logo-dark.png';
    if (file_exists($logoPath)) {
        $mail->addEmbeddedImage($logoPath, 'company_logo', 'logo-dark.png');
    }

    // Format current date
    $completion_date = date('F j, Y');
    
    // Format price
    $formatted_price = number_format((float)$price, 2);

    // Email content - HTML version
    $mail->isHTML(true);
    $mail->Subject = "Project Completion Notification - $project_name";
    
    // HTML Body
    $mail->Body = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
        .header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 30px; text-align: center; }
        .header-logo { margin-bottom: 15px; }
        .header-logo img { max-width: 200px; height: auto; }
        .header-tagline { margin: 10px 0 0 0; font-size: 14px; opacity: 0.9; font-weight: 300; letter-spacing: 1px; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; color: #1e3c72; margin-bottom: 20px; font-weight: 500; }
        .message { font-size: 16px; margin-bottom: 30px; line-height: 1.8; }
        .project-box { background: #f8f9fa; border-left: 4px solid #2a5298; padding: 20px; margin: 25px 0; border-radius: 0 5px 5px 0; }
        .project-box h2 { margin: 0 0 15px 0; color: #1e3c72; font-size: 20px; }
        .detail-row { display: flex; padding: 10px 0; border-bottom: 1px solid #e0e0e0; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: 600; color: #555; min-width: 150px; }
        .detail-value { color: #333; flex: 1; }
        .price-highlight { background: linear-gradient(135deg, #e8f4f8 0%, #d4e9f7 100%); border-radius: 8px; padding: 25px; text-align: center; margin: 30px 0; border: 2px solid #2a5298; }
        .price-highlight .label { font-size: 14px; color: #555; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; }
        .price-highlight .amount { font-size: 42px; color: #1e3c72; font-weight: bold; margin: 15px 0; }
        .price-highlight .note { margin: 10px 0 0 0; font-size: 13px; color: #666; font-style: italic; }
        .services-list { background: #fff; border: 1px solid #e0e0e0; border-radius: 5px; padding: 20px; margin: 20px 0; }
        .services-list h3 { margin: 0 0 15px 0; color: #1e3c72; font-size: 18px; }
        .services-list ul { margin: 0; padding-left: 25px; }
        .services-list li { padding: 8px 0; color: #333; font-size: 15px; }
        .cta-button { display: inline-block; background: #2a5298; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; margin: 25px 0; font-weight: 600; transition: background 0.3s; }
        .cta-button:hover { background: #1e3c72; }
        .cta-button-disabled { display: inline-block; background: #cccccc; color: #666666; padding: 15px 40px; text-decoration: none; border-radius: 5px; margin: 25px 0; font-weight: 600; cursor: not-allowed; }
        .payment-notice { background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 25px 0; border-radius: 0 5px 5px 0; }
        .payment-notice h3 { margin: 0 0 10px 0; color: #856404; font-size: 18px; }
        .payment-notice p { margin: 5px 0; color: #856404; font-size: 14px; line-height: 1.6; }
        .payment-success { background: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin: 25px 0; border-radius: 0 5px 5px 0; }
        .payment-success h3 { margin: 0 0 10px 0; color: #155724; font-size: 18px; }
        .payment-success p { margin: 5px 0; color: #155724; font-size: 14px; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; border-top: 3px solid #2a5298; }
        .footer p { margin: 5px 0; font-size: 14px; color: #666; }
        .footer strong { color: #1e3c72; }
        .divider { height: 2px; background: linear-gradient(to right, #1e3c72, #2a5298); margin: 30px 0; }
        .thank-you { font-size: 19px; color: #1e3c72; font-weight: 600; margin: 30px 0 20px 0; text-align: center; }
        .icon { font-size: 20px; margin-right: 8px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <div class='header-logo'>
                <img src='cid:company_logo' alt='GeoManage Solutions' />
            </div>
            <p class='header-tagline'>Excellence in Geospatial Engineering</p>
        </div>
        
        <div class='content'>
            <p class='greeting'>Dear $client_name,</p>
            
            <p class='message'>
                We are delighted to inform you that your surveying project has been successfully completed. 
                Our team has meticulously executed all required services to the highest professional standards, 
                ensuring accuracy and excellence in every detail.
            </p>
            
            <div class='project-box'>
                <h2>📋 Project Summary</h2>
                <div class='detail-row'>
                    <span class='detail-label'>Project Name:</span>
                    <span class='detail-value'><strong>$project_name</strong></span>
                </div>
                <div class='detail-row'>
                    <span class='detail-label'>Project ID:</span>
                    <span class='detail-value'>#" . str_pad($project_id, 6, '0', STR_PAD_LEFT) . "</span>
                </div>
                <div class='detail-row'>
                    <span class='detail-label'>Completion Date:</span>
                    <span class='detail-value'>$completion_date</span>
                </div>
            </div>
            
            " . (!empty($Land_Nb) || !empty($Land_Address) ? "
            <div class='project-box'>
                <h2>🗺️ Land Information</h2>
                " . (!empty($Land_Nb) ? "<div class='detail-row'>
                    <span class='detail-label'>Land Number:</span>
                    <span class='detail-value'>$Land_Nb</span>
                </div>" : "") . "
                " . (!empty($Land_Address) ? "<div class='detail-row'>
                    <span class='detail-label'>Location:</span>
                    <span class='detail-value'>$Land_Address</span>
                </div>" : "") . "
            </div>
            " : "") . "
            
            " . (!empty($Services) ? "
            <div class='services-list'>
                <h3>✓ Services Delivered</h3>
                <ul>
                    " . implode('', array_map(function($service) {
                        return "<li>" . htmlspecialchars(trim($service)) . "</li>";
                    }, explode(',', $Services))) . "
                </ul>
            </div>
            " : "") . "
            
            <div class='price-highlight'>
                <div class='label'>Total Project Cost</div>
                <div class='amount'>$$formatted_price USD</div>
                <p class='note'>All services and deliverables included</p>
            </div>
            
            <div class='divider'></div>
            ";
    
    // Conditional content based on payment status
    if ($is_paid) {
        $mail->Body .= "
            <div class='payment-success'>
                <h3>✓ Payment Confirmed</h3>
                <p>Your payment has been successfully processed. Thank you!</p>
            </div>
            
            <p class='message'>
                All project deliverables, including survey reports, maps, technical documentation, and analysis, 
                are now available for immediate download through your client portal.
            </p>
            
            <center>
                <a href='https://geomanage.com/client-portal' class='cta-button'>Access Project Files</a>
            </center>
            
            <p class='thank-you'>Thank you for choosing GeoManage Solutions!</p>
            
            <p class='message' style='font-size: 15px; text-align: center;'>
                Your trust in our services is greatly appreciated. Should you have any questions or require 
                additional assistance, our dedicated support team is always ready to help you.
            </p>
        ";
    } else {
        $mail->Body .= "
            <div class='payment-notice'>
                <h3>⚠ Payment Required for Deliverable Access</h3>
                <p>Your project has been completed and is ready for delivery. However, we kindly request that the outstanding balance be settled before the deliverables can be released.</p>
                <p style='margin-top: 15px;'><strong>Outstanding Amount:</strong> $$formatted_price USD</p>
                <p>Once payment is received and confirmed, you will gain immediate access to all project files, reports, and documentation through your client portal.</p>
            </div>
            
            <p class='message'>
                We understand the importance of timely project delivery. To expedite the process, please proceed with the payment at your earliest convenience. Our team will notify you immediately upon payment confirmation, and your deliverables will be made available within minutes.
            </p>
            
            <center>
                <span class='cta-button-disabled'>🔒 Access Project Files (Payment Required)</span>
            </center>
            
            <p class='message' style='font-size: 15px; text-align: center; margin-top: 30px;'>
                For payment arrangements or if you have any questions regarding the invoice, please don't hesitate to contact our billing department. We're here to assist you with any concerns.
            </p>
            
            <p class='thank-you'>Thank you for your understanding and cooperation!</p>
        ";
    }
    
    $mail->Body .= "
        </div>
        
        <div class='footer'>
            <p><strong>GeoManage Solutions</strong></p>
            <p>📧 support@geomanage.com | 📞 +961 XX XXX XXX</p>
            <p>🌐 www.geomanage.com</p>
            <p style='margin-top: 15px; font-size: 12px; color: #999;'>
                © " . date('Y') . " GeoManage Solutions. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
";

    // Plain text alternative
    $mail->AltBody = "
GEOMANAGE SOLUTIONS
Excellence in Geospatial Engineering

Dear $client_name,

PROJECT COMPLETION NOTIFICATION

We are delighted to inform you that your surveying project has been successfully completed.

PROJECT SUMMARY
================
Project Name: $project_name
Project ID: #" . str_pad($project_id, 6, '0', STR_PAD_LEFT) . "
Completion Date: $completion_date

" . (!empty($Land_Nb) || !empty($Land_Address) ? "
LAND INFORMATION
================
" . (!empty($Land_Nb) ? "Land Number: $Land_Nb\n" : "") . "
" . (!empty($Land_Address) ? "Location: $Land_Address\n" : "") : "") . "

" . (!empty($Services) ? "
SERVICES DELIVERED
==================
$Services
" : "") . "

TOTAL PROJECT COST
==================
$$formatted_price USD (All services and deliverables included)

";

    // Conditional plain text based on payment
    if ($is_paid) {
        $mail->AltBody .= "
PAYMENT STATUS: CONFIRMED
=========================

Your payment has been successfully processed. Thank you!

All project deliverables are now available for immediate download through your client portal.

Access your files: https://geomanage.com/client-portal

Thank you for choosing GeoManage Solutions!
";
    } else {
        $mail->AltBody .= "
PAYMENT REQUIRED
================

Your project has been completed and is ready for delivery. However, we kindly request that the outstanding balance of $$formatted_price USD be settled before the deliverables can be released.

Once payment is received and confirmed, you will gain immediate access to all project files through your client portal.

For payment arrangements, please contact our billing department.

Thank you for your understanding and cooperation!
";
    }

    $mail->AltBody .= "

Should you have any questions, please contact our support team.

GeoManage Solutions
Email: support@geomanage.com
Phone: +961 XX XXX XXX
Web: www.geomanage.com

© " . date('Y') . " GeoManage Solutions. All rights reserved.
";

    // Send email first
    $mail->send();

    // Update project status to 'done' 
    $updateQuery = "UPDATE project SET status = 'done' WHERE project_id = $project_id";
    $updateResult = mysqli_query($con, $updateQuery);
    
    if (!$updateResult) {
        echo "Email sent successfully, but status update failed: " . mysqli_error($con);
    } else {
        echo 'Success! Professional project completion email sent and project marked as done.';
    }
    
} catch (Exception $e) {
    echo "Email delivery failed. Error: {$mail->ErrorInfo}";
}

mysqli_close($con);
?>