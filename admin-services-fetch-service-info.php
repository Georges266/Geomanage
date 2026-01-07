<?php
include 'includes/connect.php';

$service_id = (int)$_POST['service_id'];

// Using MySQLi
$query = "SELECT * FROM service WHERE service_id = $service_id";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo "<p class='text-danger'>Service not found.</p>";
    exit;
}
?>

<input type="hidden" id="edit_service_id" value="<?php echo $row['service_id']; ?>">

<div class="mb-3">
    <label>Service Name</label>
    <input type="text" id="edit_service_name" class="form-control" value="<?php echo htmlspecialchars($row['service_name']); ?>">
</div>

<div class="mb-3">
    <label>Service Status</label>
    <select id="edit_service_status" class="form-control">
        <option value="active" <?php echo ($row['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
        <option value="Inactive" <?php echo ($row['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
    </select>
</div>

<div class="mb-3">
    <label>Min Price</label>
    <input type="number" id="edit_min_price" class="form-control" value="<?php echo $row['min_price']; ?>" step="0.01">
</div>

 

<div class="mb-3">
    <label>Description</label>
    <textarea id="edit_description" class="form-control" rows="4"><?php echo htmlspecialchars($row['description']); ?></textarea>
</div>

<button type="button" class="btn btn-primary" id="saveServiceBtn">Save Changes</button>