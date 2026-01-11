<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'includes/connect.php';

// Include PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$equipment_id = intval($_POST['equipment_id']);
$project_id   = intval($_POST['project_id']);

if ($equipment_id > 0 && $project_id > 0) {

    mysqli_begin_transaction($con);

    try {
        // Get equipment, project, and lead engineer details for the email FIRST
        $detailsQuery = "
            SELECT 
                e.equipment_name,
                e.equipment_type,
                e.model,
                e.serial_number,
                p.project_name,
                land.land_address,
                land.land_number,
                u.email AS lead_email,
                u.full_name AS lead_name
            FROM equipment e
            JOIN uses_project_equipment upe ON e.equipment_id = upe.equipment_id
            JOIN project p ON upe.project_id = p.project_id
            LEFT JOIN includes_project_land ipl ON ipl.project_id = p.project_id
            LEFT JOIN land ON land.land_id = ipl.land_id
            JOIN lead_engineer le ON p.lead_engineer_id = le.lead_engineer_id
            JOIN user u ON le.user_id = u.user_id
            WHERE e.equipment_id = $equipment_id AND p.project_id = $project_id
            LIMIT 1
        ";
        
        $detailsResult = mysqli_query($con, $detailsQuery);
        
        if (!$detailsResult || mysqli_num_rows($detailsResult) == 0) {
            throw new Exception("Could not fetch equipment/project details");
        }
        
        $details = mysqli_fetch_assoc($detailsResult);
        
        // Remove request
        $delete_result = mysqli_query($con, "
            DELETE FROM uses_project_equipment
            WHERE equipment_id = $equipment_id
            AND project_id = $project_id
        ");
        
        if (!$delete_result) {
            throw new Exception("Failed to delete from uses_project_equipment: " . mysqli_error($con));
        }

        // Restore equipment availability
        $update_result = mysqli_query($con, "
            UPDATE equipment
            SET status = 'available'
            WHERE equipment_id = $equipment_id
            AND status = 'requested'
        ");
        
        if (!$update_result) {
            throw new Exception("Failed to update equipment status: " . mysqli_error($con));
        }

        // Send rejection email using PHPMailer
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '';
        $mail->Password   = '';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('', 'GeoManage Admin');
        $mail->addAddress($details['lead_email'], $details['lead_name']);

        // Try to embed the logo image, but don't fail if it doesn't exist
        $logoPath = __DIR__ . '/img/logo-dark.png';
        $logoEmbedded = false;
        if (file_exists($logoPath)) {
            // Use a generic name for the embedded image to avoid "dark mode" confusion
            $mail->addEmbeddedImage($logoPath, 'company_logo', 'logo.png', 'base64', 'image/png');
            $logoEmbedded = true;
        }

        // Format current date
        $rejection_date = date('F j, Y \a\t g:i A');
        
        $equipment_name = htmlspecialchars($details['equipment_name']);
        $equipment_type = htmlspecialchars($details['equipment_type']);
        $model = htmlspecialchars($details['model']);
        $serial_number = htmlspecialchars($details['serial_number']);
        $project_name = htmlspecialchars($details['project_name']);
        $land_address = !empty($details['land_address']) ? htmlspecialchars($details['land_address']) : 'N/A';
        $land_number = !empty($details['land_number']) ? htmlspecialchars($details['land_number']) : 'N/A';
        $lead_name = htmlspecialchars($details['lead_name']);

        // Email content - HTML version
        $mail->isHTML(true);
        $mail->Subject = "Equipment Request Declined - $equipment_name";
        
        // Choose logo display method based on whether embedding worked
        $logoHtml = $logoEmbedded 
            ? "<img src='cid:company_logo' alt='GeoManage Solutions' />"
            : "<h2 style='margin: 0; color: white; font-size: 28px;'>GeoManage Solutions</h2>";
        
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
        .rejection-badge { background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); border: 2px solid #dc3545; border-radius: 10px; padding: 25px; text-align: center; margin: 30px 0; }
        .rejection-badge .icon { font-size: 48px; color: #dc3545; margin-bottom: 10px; }
        .rejection-badge .status { font-size: 24px; color: #721c24; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; }
        .rejection-badge .date { font-size: 13px; color: #721c24; margin-top: 10px; }
        .info-box { background: #f8f9fa; border-left: 4px solid #2a5298; padding: 20px; margin: 25px 0; border-radius: 0 5px 5px 0; }
        .info-box h2 { margin: 0 0 15px 0; color: #1e3c72; font-size: 20px; }
        .detail-row { display: flex; padding: 10px 0; border-bottom: 1px solid #e0e0e0; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: 600; color: #555; min-width: 150px; }
        .detail-value { color: #333; flex: 1; }
        .project-box { background: #f8f9fa; border-left: 4px solid #2a5298; padding: 20px; margin: 25px 0; border-radius: 0 5px 5px 0; }
        .project-box h2 { margin: 0 0 15px 0; color: #1e3c72; font-size: 20px; }
        .land-box { background: #f8f9fa; border-left: 4px solid #2a5298; padding: 20px; margin: 25px 0; border-radius: 0 5px 5px 0; }
        .land-box h2 { margin: 0 0 15px 0; color: #1e3c72; font-size: 20px; }
        .divider { height: 2px; background: linear-gradient(to right, #1e3c72, #2a5298); margin: 30px 0; }
        .notice-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 25px 0; border-radius: 0 5px 5px 0; }
        .notice-box p { margin: 5px 0; color: #856404; font-size: 15px; line-height: 1.6; }
        .thank-you { font-size: 19px; color: #1e3c72; font-weight: 600; margin: 30px 0 20px 0; text-align: center; }
        .footer { background: #f8f9fa; padding: 30px; text-align: center; border-top: 3px solid #2a5298; }
        .footer p { margin: 5px 0; font-size: 14px; color: #666; }
        .footer strong { color: #1e3c72; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <div class='header-logo'>
                $logoHtml
            </div>
            <p class='header-tagline'>Excellence in Geospatial Engineering</p>
        </div>
        
        <div class='content'>
            <p class='greeting'>Dear $lead_name,</p>
            
            <div class='rejection-badge'>
                <div class='icon'>✗</div>
                <div class='status'>Request Declined</div>
                <div class='date'>$rejection_date</div>
            </div>
            
            <p class='message'>
                Your equipment request has been <strong>declined</strong>. 
                The equipment will not be assigned to your project at this time.
            </p>
            
            <div class='info-box'>
                <h2>🔧 Equipment Details</h2>
                <div class='detail-row'>
                    <span class='detail-label'>Equipment Name:</span>
                    <span class='detail-value'><strong>$equipment_name</strong></span>
                </div>
                <div class='detail-row'>
                    <span class='detail-label'>Equipment Type:</span>
                    <span class='detail-value'>$equipment_type</span>
                </div>
                <div class='detail-row'>
                    <span class='detail-label'>Model:</span>
                    <span class='detail-value'>$model</span>
                </div>
                <div class='detail-row'>
                    <span class='detail-label'>Serial Number:</span>
                    <span class='detail-value'>$serial_number</span>
                </div>
            </div>
            
            <div class='project-box'>
                <h2>📋 Project Information</h2>
                <div class='detail-row'>
                    <span class='detail-label'>Project Name:</span>
                    <span class='detail-value'><strong>$project_name</strong></span>
                </div>
            </div>
            
            <div class='land-box'>
                <h2>🗺️ Land Information</h2>
                <div class='detail-row'>
                    <span class='detail-label'>Land Number:</span>
                    <span class='detail-value'>$land_number</span>
                </div>
                <div class='detail-row'>
                    <span class='detail-label'>Land Address:</span>
                    <span class='detail-value'>$land_address</span>
                </div>
            </div>
            
            <div class='divider'></div>
            
            <div class='notice-box'>
                <p><strong>⚠ What happens next?</strong></p>
                <p>
                    The equipment is now available for other projects. If you still require this equipment 
                    or would like to discuss alternative options, please contact the administrator.
                </p>
            </div>
            
            <p class='message'>
                If you have any questions regarding this decision, please feel free to reach out.
            </p>
            
            <p class='thank-you'>Thank you for your understanding!</p>
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

Dear $lead_name,

EQUIPMENT REQUEST DECLINED
===========================

Your equipment request has been declined on $rejection_date.

EQUIPMENT DETAILS
=================
Equipment Name: $equipment_name
Equipment Type: $equipment_type
Model: $model
Serial Number: $serial_number

PROJECT INFORMATION
==================
Project Name: $project_name

LAND INFORMATION
================
Land Number: $land_number
Land Address: $land_address

The equipment is now available for other projects. If you still require this equipment 
or would like to discuss alternative options, please contact the administrator.

If you have any questions, please contact our support team.

GeoManage Solutions
Email: support@geomanage.com
Phone: +961 XX XXX XXX
Web: www.geomanage.com

© " . date('Y') . " GeoManage Solutions. All rights reserved.
";

        // Send email
        $mail->send();

        mysqli_commit($con);
        echo "success";

    } catch (Exception $e) {
        mysqli_rollback($con);
        echo "error: " . $e->getMessage();
    }

} else {
    echo "error: Invalid data";
}

mysqli_close($con);
?>