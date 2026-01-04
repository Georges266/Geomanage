<?php
include 'includes/connect.php';

if (isset($_POST['request_id'])) {
    $request_id = (int) $_POST['request_id'];

    $query = "SELECT l.land_number, l.land_address, l.land_area, l.general_description, l.land_type,
              l.coordinates_latitude, l.coordinates_longitude, l.specific_location_notes, 
              l.elevation_avg, l.elevation_min, l.elevation_max, l.slope, l.distance_from_office,
              l.geometry_approved, l.terrain_approved
              FROM land l
              JOIN service_request sr ON sr.land_id = l.land_id
              WHERE l.geometry_approved = 0 AND l.terrain_approved = 0
              AND sr.request_id = $request_id
              LIMIT 1";

    $result = mysqli_query($con, $query);


=======
    if ($result && $row = mysqli_fetch_assoc($result)) {
        // Helper function to format display values
        function formatValue($value, $unit = '') {
            if (empty($value) || $value === null) {
                return '<span class="text-muted">Not specified</span>';
            }
            return htmlspecialchars($value) . ($unit ? ' ' . $unit : '');
        }
        ?>
        <div id='landInfoData' 
             class='land-details-container'
             data-lat='<?php echo htmlspecialchars($row['coordinates_latitude']); ?>' 
             data-lng='<?php echo htmlspecialchars($row['coordinates_longitude']); ?>'>
            
            <!-- Basic Information Section -->
            <div class='detail-section'>
                <h5 class='section-title'><i class='fas fa-info-circle'></i> Basic Information</h5>
                <div class='detail-grid'>
                    <div class='detail-item'>
                        <span class='detail-label'>Land Number:</span>
                        <span class='detail-value'><?php echo formatValue($row['land_number']); ?></span>
                    </div>
                    <div class='detail-item'>
                        <span class='detail-label'>Land Type:</span>
                        <span class='detail-value'><?php echo formatValue($row['land_type']); ?></span>
                    </div>
                    <div class='detail-item'>
                        <span class='detail-label'>Land Area:</span>
                        <span class='detail-value'><?php echo formatValue($row['land_area'], 'm²'); ?></span>
                    </div>
                    <div class='detail-item'>
                        <span class='detail-label'>Distance from Office:</span>
                        <span class='detail-value'><?php echo formatValue($row['distance_from_office'], 'km'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Location Section -->
            <div class='detail-section'>
                <h5 class='section-title'><i class='fas fa-map-marker-alt'></i> Location</h5>
                <div class='detail-grid'>
                    <div class='detail-item full-width'>
                        <span class='detail-label'>Address:</span>
                        <span class='detail-value'><?php echo formatValue($row['land_address']); ?></span>
                    </div>
                    <div class='detail-item'>
                        <span class='detail-label'>Latitude:</span>
                        <span class='detail-value'><?php echo formatValue($row['coordinates_latitude'], '°'); ?></span>
                    </div>
                    <div class='detail-item'>
                        <span class='detail-label'>Longitude:</span>
                        <span class='detail-value'><?php echo formatValue($row['coordinates_longitude'], '°'); ?></span>
                    </div>
                    <?php if (!empty($row['specific_location_notes'])): ?>
                    <div class='detail-item full-width'>
                        <span class='detail-label'>Location Notes:</span>
                        <span class='detail-value'><?php echo formatValue($row['specific_location_notes']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Terrain Information Section -->
            <div class='detail-section'>
                <h5 class='section-title'><i class='fas fa-mountain'></i> Terrain Information</h5>
                <div class='detail-grid'>
                    <div class='detail-item'>
                        <span class='detail-label'>Average Elevation:</span>
                        <span class='detail-value'><?php echo formatValue($row['elevation_avg'], 'm'); ?></span>
                    </div>
                    <div class='detail-item'>
                        <span class='detail-label'>Min Elevation:</span>
                        <span class='detail-value'><?php echo formatValue($row['elevation_min'], 'm'); ?></span>
                    </div>
                    <div class='detail-item'>
                        <span class='detail-label'>Max Elevation:</span>
                        <span class='detail-value'><?php echo formatValue($row['elevation_max'], 'm'); ?></span>
                    </div>
                    <div class='detail-item'>
                        <span class='detail-label'>Slope:</span>
                        <span class='detail-value'><?php echo formatValue($row['slope'], '°'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Description Section -->
            <?php if (!empty($row['general_description'])): ?>
            <div class='detail-section'>
                <h5 class='section-title'><i class='fas fa-file-alt'></i> Description</h5>
                <div class='description-content'>
                    <p><?php echo nl2br(htmlspecialchars($row['general_description'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Status Section -->
            <div class='detail-section'>
                <h5 class='section-title'><i class='fas fa-check-circle'></i> Approval Status</h5>
                <div class='status-badges'>
                    <span class='badge badge-warning'>
                        <i class='fas fa-clock'></i> Geometry Pending
                    </span>
                    <span class='badge badge-warning'>
                        <i class='fas fa-clock'></i> Terrain Pending
                    </span>
                </div>
            </div>
        </div>

        <style>
            .land-details-container {
                padding: 20px;
                max-height: 70vh;
                overflow-y: auto;
            }

            .detail-section {
                margin-bottom: 25px;
                padding-bottom: 20px;
                border-bottom: 1px solid #e9ecef;
            }

            .detail-section:last-child {
                border-bottom: none;
                margin-bottom: 0;
            }

            .section-title {
                color: #2c3e50;
                font-size: 16px;
                font-weight: 600;
                margin-bottom: 15px;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .section-title i {
                color: #3498db;
                font-size: 18px;
            }

            .detail-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
            }

            .detail-item {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .detail-item.full-width {
                grid-column: 1 / -1;
            }

            .detail-label {
                font-size: 13px;
                color: #6c757d;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .detail-value {
                font-size: 15px;
                color: #2c3e50;
                font-weight: 400;
            }

            .text-muted {
                color: #adb5bd !important;
                font-style: italic;
            }

            .description-content {
                background-color: #f8f9fa;
                padding: 15px;
                border-radius: 6px;
                border-left: 3px solid #3498db;
            }

            .description-content p {
                margin: 0;
                line-height: 1.6;
                color: #495057;
            }

            .status-badges {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }

            .badge {
                padding: 8px 15px;
                border-radius: 20px;
                font-size: 13px;
                font-weight: 500;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .badge-warning {
                background-color: #fff3cd;
                color: #856404;
            }

            /* Scrollbar Styling */
            .land-details-container::-webkit-scrollbar {
                width: 8px;
            }

            .land-details-container::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }

            .land-details-container::-webkit-scrollbar-thumb {
                background: #cbd5e0;
                border-radius: 10px;
            }

            .land-details-container::-webkit-scrollbar-thumb:hover {
                background: #a0aec0;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .detail-grid {
                    grid-template-columns: 1fr;
                }
                
                .land-details-container {
                    padding: 15px;
                }
            }
        </style>
        <?php
>>>>>>> af059e9 (Update the project and service details)
    } else {
        ?>
        <div class='alert-container'>
            <div class='alert alert-info'>
                <i class='fas fa-info-circle'></i>
                <span>No land information found for this request.</span>
            </div>
        </div>
        <style>
            .alert-container {
                padding: 20px;
            }
            .alert {
                padding: 15px 20px;
                border-radius: 6px;
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .alert-info {
                background-color: #d1ecf1;
                color: #0c5460;
                border: 1px solid #bee5eb;
            }
            .alert i {
                font-size: 20px;
            }
        </style>
        <?php
    }
} else {
    ?>
    <div class='alert-container'>
        <div class='alert alert-danger'>
            <i class='fas fa-exclamation-triangle'></i>
            <span>Invalid request. Please try again.</span>
        </div>
    </div>
    <style>
        .alert-container {
            padding: 20px;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert i {
            font-size: 20px;
        }
    </style>
    <?php
}
?>
