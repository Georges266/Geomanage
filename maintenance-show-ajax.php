<?php
session_start();
include 'includes/connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Maintenance_Technician') {
    exit();
}

$action = $_POST['action'] ?? '';
$search = $_POST['search'] ?? '';

/* ================= MAINTENANCE REQUESTS ================= */
if ($action === 'maintenance_requests') {

    $sql = "SELECT 
                m.maintenance_id,
                m.request_date,
                e.equipment_name,
                u.full_name
            FROM maintenance m
            JOIN equipment e ON m.equipment_id = e.equipment_id
            JOIN user u ON m.requested_by = u.user_id
            WHERE (e.equipment_name LIKE '%$search%'
            OR m.maintenance_id LIKE '%$search%')
            AND e.status='maintenance'
            ORDER BY m.request_date DESC";

    $res = mysqli_query($con, $sql);

    if (mysqli_num_rows($res) == 0) {
        echo '<tr><td colspan="5" class="text-center">No maintenance found</td></tr>';
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        echo "
        <tr>
            <td>#MT-{$row['maintenance_id']}</td>
            <td>{$row['equipment_name']}</td>
            <td>{$row['full_name']}</td>
            <td>{$row['request_date']}</td>
            <td>
                 <button class='btn btn-sm btn-primary viewMaintenanceBtn' data-id='{$row['maintenance_id']}'>
                    <i class='fas fa-eye'></i> View
                </button>
            </td>
        </tr>";
    }
    exit();
}

/* ================= EQUIPMENT HISTORY ================= */
if ($action === 'equipment_history') {

    $sql = "SELECT 
                e.equipment_id,
                e.equipment_name,
                e.equipment_type,
                MAX(m.request_date) AS last_date,
                COUNT(m.maintenance_id) AS total
            FROM equipment e
            LEFT JOIN maintenance m ON e.equipment_id = m.equipment_id
            WHERE e.equipment_name LIKE '%$search%' 
            GROUP BY e.equipment_id";

    $res = mysqli_query($con, $sql);

    if (mysqli_num_rows($res) == 0) {
        echo '<tr><td colspan="5" class="text-center">No equipment found</td></tr>';
        exit();
    }

    while ($row = mysqli_fetch_assoc($res)) {
        echo "
        <tr>
            <td>#EQ-{$row['equipment_id']}</td>
            <td>{$row['equipment_name']}</td>
            <td>{$row['equipment_type']}</td>
            <td>".($row['last_date'] ?? 'N/A')."</td>
            <td>{$row['total']}</td>
        </tr>";
    }
    exit();
}
