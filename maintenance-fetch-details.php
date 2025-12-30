<?php
session_start();
include 'includes/connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Maintenance_Technician') {
    echo "Unauthorized access";
    exit();
}

if (!isset($_POST['maintenance_id'])) {
    echo '<p class="text-danger">Invalid maintenance ID.</p>';
    exit();
}

$maintenance_id = (int)$_POST['maintenance_id'];

// Fetch maintenance details
$sql = "SELECT 
            m.maintenance_id,
            m.request_date,
            m.maintenance_date,
            m.maintenance_type,
            m.description,
            m.total_cost,
            m.bill_file_path,
            e.equipment_id,
            e.equipment_name,
            e.equipment_type,
            e.serial_number,
            e.model,
            u.full_name
        FROM maintenance m
        JOIN equipment e ON m.equipment_id = e.equipment_id
        JOIN user u ON m.requested_by = u.user_id
        WHERE m.maintenance_id = $maintenance_id";

$result = mysqli_query($con, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    echo '<p class="text-danger">Maintenance request not found.</p>';
    exit();
}

$data = mysqli_fetch_assoc($result);
$isCompleted = !empty($data['maintenance_date']);
?>

<div style="font-size: 14px; color: #333;">
    <div class="info-row">
        <strong><i class="fas fa-hashtag"></i> Request ID:</strong>
        <span>#MT-<?php echo $data['maintenance_id']; ?></span>
    </div>
    
    <div class="info-row">
        <strong><i class="fas fa-tools"></i> Equipment Name:</strong>
        <span><?php echo htmlspecialchars($data['equipment_name']); ?></span>
    </div>
    
    <div class="info-row">
        <strong><i class="fas fa-tag"></i> Equipment Type:</strong>
        <span><?php echo htmlspecialchars($data['equipment_type']); ?></span>
    </div>
    
    <div class="info-row">
        <strong><i class="fas fa-barcode"></i> Serial Number:</strong>
        <span><?php echo htmlspecialchars($data['serial_number'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="info-row">
        <strong><i class="fas fa-cube"></i> Model:</strong>
        <span><?php echo htmlspecialchars($data['model'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="info-row">
        <strong><i class="fas fa-user"></i> Requested By:</strong>
        <span><?php echo htmlspecialchars($data['full_name']); ?></span>
    </div>
    
    <div class="info-row">
        <strong><i class="fas fa-calendar"></i> Request Date:</strong>
        <span><?php echo date("M d, Y", strtotime($data['request_date'])); ?></span>
    </div>
    
    <div class="info-row">
        <strong><i class="fas fa-wrench"></i> Maintenance Type:</strong>
        <span><?php echo htmlspecialchars($data['maintenance_type'] ?? 'N/A'); ?></span>
    </div>
    
    <div class="info-row">
        <strong><i class="fas fa-comment"></i> Description:</strong>
        <div style="margin-top: 8px; padding: 12px; background: white; border-left: 3px solid #ff7607; border-radius: 4px; white-space: pre-wrap;">
            <?php echo htmlspecialchars($data['description'] ?? 'No description provided'); ?>
        </div>
    </div>

    <?php if ($isCompleted): ?>
        <!-- Show completed maintenance details -->
        <div class="info-row" style="background: #d4edda; border-left: 3px solid #28a745;">
            <strong><i class="fas fa-check-circle" style="color: #28a745;"></i> Status:</strong>
            <span style="color: #155724; font-weight: 600;">Completed</span>
        </div>

        <div class="info-row">
            <strong><i class="fas fa-calendar-check"></i> Maintenance Date (Fixed):</strong>
            <span><?php echo date("M d, Y", strtotime($data['maintenance_date'])); ?></span>
        </div>

        <?php if ($data['total_cost']): ?>
        <div class="info-row">
            <strong><i class="fas fa-dollar-sign"></i> Total Cost:</strong>
            <span style="font-size: 16px; color: #28a745; font-weight: 600;">
                $<?php echo number_format($data['total_cost'], 2); ?>
            </span>
        </div>
        <?php endif; ?>

        <?php if ($data['bill_file_path']): ?>
        <div class="info-row">
            <strong><i class="fas fa-file-invoice"></i> Bill/Receipt:</strong>
            <div style="margin-top: 10px;">
                <a href="uploads/bills/<?php echo htmlspecialchars($data['bill_file_path']); ?>" 
                   target="_blank" 
                   class="btn btn-sm btn-info">
                    <i class="fas fa-download"></i> View Bill
                </a>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Maintenance completion form -->
        <div style="margin-top: 25px; padding: 20px; background: #f0f8ff; border-radius: 8px; border: 1px solid #cce7ff;">
            <h5 style="margin-bottom: 15px; color: #263a4f;">
                <i class="fas fa-clipboard-check"></i> Fix Equipment
            </h5>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 600; display: block; margin-bottom: 5px; color: #263a4f;">
                    <i class="fas fa-dollar-sign"></i> Total Cost (Optional)
                </label>
                <input type="number" 
                       id="totalCost" 
                       class="form-control" 
                       step="0.01" 
                       min="0" 
                       placeholder="0.00"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <small style="color: #666; font-size: 12px;">Enter the total cost for parts, labor, or services if any</small>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 600; display: block; margin-bottom: 5px; color: #263a4f;">
                    <i class="fas fa-file-upload"></i> Upload Bill/Receipt (Optional)
                </label>
                <input type="file" 
                       id="billUpload" 
                       class="form-control" 
                       accept="image/*,.pdf"
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <small style="color: #666; font-size: 12px;">Supported formats: JPG, PNG, PDF (Max 5MB)</small>
                
                <!-- Image Preview -->
                <div id="imagePreview" style="margin-top: 10px; display: none;">
                    <img id="previewImg" src="" alt="Preview" style="max-width: 200px; border-radius: 5px; border: 1px solid #ddd;">
                    <button type="button" onclick="clearImagePreview()" style="display: block; margin-top: 5px; padding: 3px 8px; font-size: 11px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer;">
                        <i class="fas fa-times"></i> Remove
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.info-row {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 5px;
    margin-bottom: 10px;
}

.info-row strong {
    color: #263a4f;
    display: block;
    margin-bottom: 5px;
    font-size: 13px;
}

.info-row span {
    color: #555;
}

.info-row i {
    color: #ff7607;
    margin-right: 5px;
}

.form-control {
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #ff7607 !important;
    box-shadow: 0 0 0 3px rgba(255, 118, 7, 0.1);
}
</style>

<script>
// Use jQuery and event delegation to handle dynamically loaded content
$(document).ready(function() {
    // Image preview functionality - using event delegation
    $(document).on('change', '#billUpload', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                $(this).val('');
                return;
            }

            // Show preview for images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImg').attr('src', e.target.result);
                    $('#imagePreview').show();
                };
                reader.readAsDataURL(file);
            } else {
                $('#imagePreview').hide();
            }
        }
    });
});

// Clear image preview function
function clearImagePreview() {
    $('#billUpload').val('');
    $('#imagePreview').hide();
    $('#previewImg').attr('src', '');
}
</script>