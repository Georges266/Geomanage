<?php
include 'includes/connect.php';

if (isset($_POST['request_id'])) {
     
    $request_id = (int) $_POST['request_id'];

    $query = "SELECT l.land_number, l.land_address, l.land_area, l.general_description, l.land_type,
                     l.coordinates_latitude, l.coordinates_longitude, l.specific_location_notes
              FROM land l
              JOIN service_request sr ON sr.land_id = l.land_id
              WHERE sr.request_id = $request_id
              LIMIT 1";

    $result = mysqli_query($con, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        echo "<p><strong>Land Number:</strong> {$row['land_number']}</p>
              <p><strong>Address:</strong> {$row['land_address']}</p>
              <p><strong>Land Area:</strong> {$row['land_area']}</p>
              <p><strong>Land Type:</strong> {$row['land_type']}</p>
              <p><strong>Coordinates Latitude:</strong> {$row['coordinates_latitude']}</p>
              <p><strong>Coordinates Longitude:</strong> {$row['coordinates_longitude']}</p>
              <p><strong>Specific Location Notes:</strong> {$row['specific_location_notes']}</p>
              <p><strong>Description:</strong> {$row['general_description']}</p>";
    } else {
        echo "<p>No land info found.</p>";
    }
} else {
    echo "<p>Invalid request.</p>";
}
?>
