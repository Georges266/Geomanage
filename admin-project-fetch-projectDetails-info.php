<?php
include 'includes/connect.php';

if (isset($_POST['project_id'])) {
    $project_id = (int) $_POST['project_id'];

    // Fetch land information - Changed AND to OR to show land even if not fully approved
    $land_query = "SELECT 
                l.land_id,
                l.land_number,
                l.land_address,
                l.land_area,
                l.general_description,
                l.land_type,
                l.coordinates_latitude,
                l.coordinates_longitude,
                l.specific_location_notes,
                l.elevation_avg,
                l.elevation_min,
                l.elevation_max,
                l.slope,
                l.terrain_factor,
                l.distance_from_office,
                l.geometry_approved,
                l.terrain_approved,
                project.project_id
                FROM land l
                JOIN includes_project_land ON includes_project_land.land_id = l.land_id
                JOIN project ON project.project_id = includes_project_land.project_id
                WHERE project.project_id = $project_id
              LIMIT 1";

    $land_result = mysqli_query($con, $land_query);

    if ($land_result && $land_row = mysqli_fetch_assoc($land_result)) {
        $land_id = $land_row['land_id'];
        $geometry_approved = (int)$land_row['geometry_approved'];
        $terrain_approved = (int)$land_row['terrain_approved'];
        
        // Fetch documents for this land
        $docs_query = "SELECT 
                       submitted_document.document_id,
                       submitted_document.file_name,
                       submitted_document.file_path,
                       submitted_document.upload_date,
                       submitted_document.document_type_id,
                       document_type.type_name
                      FROM submitted_document
                      JOIN document_type ON document_type.document_type_id = submitted_document.document_type_id
                      JOIN has_servicerequest_submitteddocument ON has_servicerequest_submitteddocument.submitted_document_id = submitted_document.document_id
                      JOIN service_request ON service_request.request_id = has_servicerequest_submitteddocument.service_request_id
                      JOIN land ON land.land_id = service_request.land_id
                      WHERE land.land_id = $land_id 
                      ORDER BY submitted_document.upload_date DESC";
        
        $docs_result = mysqli_query($con, $docs_query);
        $document_count = $docs_result ? mysqli_num_rows($docs_result) : 0;
        
        // Helper function to format display values
        function formatValue($value, $unit = '') {
            if (empty($value) || $value === null) {
                return '<span class="text-muted">Not specified</span>';
            }
            return htmlspecialchars($value) . ($unit ? ' ' . $unit : '');
        }
        
        // Helper function to get document icon based on file extension
        function getDocumentIcon($filename) {
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $icons = [
                'pdf' => 'fa-file-pdf',
                'doc' => 'fa-file-word',
                'docx' => 'fa-file-word',
                'xls' => 'fa-file-excel',
                'xlsx' => 'fa-file-excel',
                'jpg' => 'fa-file-image',
                'jpeg' => 'fa-file-image',
                'png' => 'fa-file-image',
                'gif' => 'fa-file-image',
                'bmp' => 'fa-file-image',
                'svg' => 'fa-file-image',
                'zip' => 'fa-file-archive',
                'rar' => 'fa-file-archive',
                '7z' => 'fa-file-archive',
                'txt' => 'fa-file-alt',
                'csv' => 'fa-file-csv',
                'ppt' => 'fa-file-powerpoint',
                'pptx' => 'fa-file-powerpoint',
            ];
            return isset($icons[$extension]) ? $icons[$extension] : 'fa-file';
        }
        ?>
        
        <div class='project-details-container'>
            
            <!-- Navigation Tabs -->
            <div class='tabs-navigation'>
                <button class='tab-btn active' data-tab='land-info'>
                    <i class='fas fa-map-marked-alt'></i>
                    <span>Land Information</span>
                </button>
                <button class='tab-btn' data-tab='documents'>
                    <i class='fas fa-folder-open'></i>
                    <span>Documents</span>
                    <?php if ($document_count > 0): ?>
                    <span class='badge-count'><?php echo $document_count; ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- Tab Content Container -->
            <div class='tabs-content'>
                
                <!-- Land Information Tab -->
                <div id='land-info' class='tab-content active' 
                     data-lat='<?php echo htmlspecialchars($land_row['coordinates_latitude']); ?>' 
                     data-lng='<?php echo htmlspecialchars($land_row['coordinates_longitude']); ?>'>
                    
                    <!-- Basic Information Section -->
                    <div class='detail-section'>
                        <h5 class='section-title'>
                            <i class='fas fa-info-circle'></i> Basic Information
                        </h5>
                        <div class='detail-grid'>
                            <div class='detail-item'>
                                <span class='detail-label'>Land Number</span>
                                <span class='detail-value'><?php echo formatValue($land_row['land_number']); ?></span>
                            </div>
                            <div class='detail-item'>
                                <span class='detail-label'>Land Type</span>
                                <span class='detail-value'><?php echo formatValue($land_row['land_type']); ?></span>
                            </div>
                            <div class='detail-item'>
                                <span class='detail-label'>Distance from Office</span>
                                <span class='detail-value'><?php echo formatValue($land_row['distance_from_office'], 'km'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Location Section with Data Source Indicator -->
                    <div class='detail-section'>
                        <h5 class='section-title'>
                            <i class='fas fa-map-marker-alt'></i> Location Details
                        </h5>
                        
                        <!-- Data Source Badge -->
                        <?php if ($geometry_approved == 1): ?>
                            <div class='data-source-badge surveyor'>
                                <i class='fas fa-user-check'></i>
                                <div>
                                    <span>Surveyor-Verified Data</span>
                                    <small>Location coordinates and area measurements have been professionally surveyed and verified</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class='data-source-badge client'>
                                <i class='fas fa-user-edit'></i>
                                <div>
                                    <span>Client-Provided Data</span>
                                    <small>Location coordinates and area are based on client information, pending surveyor verification</small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class='detail-grid'>
                            <div class='detail-item full-width'>
                                <span class='detail-label'>Address</span>
                                <span class='detail-value'><?php echo formatValue($land_row['land_address']); ?></span>
                            </div>
                            <div class='detail-item'>
                                <span class='detail-label'>Land Area</span>
                                <span class='detail-value'><?php echo formatValue($land_row['land_area'], 'm²'); ?></span>
                            </div>
                            <div class='detail-item'>
                                <span class='detail-label'>Latitude</span>
                                <span class='detail-value'><?php echo formatValue($land_row['coordinates_latitude'], '°'); ?></span>
                            </div>
                            <div class='detail-item'>
                                <span class='detail-label'>Longitude</span>
                                <span class='detail-value'><?php echo formatValue($land_row['coordinates_longitude'], '°'); ?></span>
                            </div>
                            <?php if (!empty($land_row['specific_location_notes'])): ?>
                            <div class='detail-item full-width'>
                                <span class='detail-label'>Location Notes</span>
                                <span class='detail-value'><?php echo formatValue($land_row['specific_location_notes']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Terrain Information Section with Data Source Indicator -->
                    <div class='detail-section'>
                        <h5 class='section-title'>
                            <i class='fas fa-mountain'></i> Terrain Information
                        </h5>
                        
                        <!-- Data Source Badge -->
                        <?php if ($terrain_approved == 1): ?>
                            <div class='data-source-badge surveyor'>
                                <i class='fas fa-user-check'></i>
                                <div>
                                    <span>Surveyor-Verified Data</span>
                                    <small>Elevation measurements and slope analysis have been professionally surveyed and verified</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class='data-source-badge client'>
                                <i class='fas fa-user-edit'></i>
                                <div>
                                    <span>Client-Provided Data</span>
                                    <small>Terrain information is based on client estimates, pending surveyor verification</small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class='detail-grid terrain-grid'>
                            <div class='detail-item'>
                                <span class='detail-label'>Average Elevation</span>
                                <span class='detail-value highlight'><?php echo formatValue($land_row['elevation_avg'], 'm'); ?></span>
                            </div>
                            <div class='detail-item'>
                                <span class='detail-label'>Min Elevation</span>
                                <span class='detail-value'><?php echo formatValue($land_row['elevation_min'], 'm'); ?></span>
                            </div>
                            <div class='detail-item'>
                                <span class='detail-label'>Max Elevation</span>
                                <span class='detail-value'><?php echo formatValue($land_row['elevation_max'], 'm'); ?></span>
                            </div>
                            <div class='detail-item'>
                                <span class='detail-label'>Slope</span>
                                <span class='detail-value'><?php echo formatValue($land_row['slope'], '°'); ?></span>
                            </div>
                            <div class='detail-item'>
                                <span class='detail-label'>Terrain Factor:</span>
                                <span class='detail-value'><?php echo formatValue($land_row['terrain_factor'], '°'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <?php if (!empty($land_row['general_description'])): ?>
                    <div class='detail-section'>
                        <h5 class='section-title'>
                            <i class='fas fa-file-alt'></i> General Description
                        </h5>
                        <div class='description-content'>
                            <p><?php echo nl2br(htmlspecialchars($land_row['general_description'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Status Section -->
                    <div class='detail-section status-section'>
                        <h5 class='section-title'>
                            <i class='fas fa-check-circle'></i> Verification Status
                        </h5>
                        <div class='status-badges'>
                            <?php if ($geometry_approved == 1): ?>
                                <span class='badge badge-success'>
                                    <i class='fas fa-check-circle'></i> Geometry Verified
                                </span>
                            <?php else: ?>
                                <span class='badge badge-pending'>
                                    <i class='fas fa-clock'></i> Geometry Pending Verification
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($terrain_approved == 1): ?>
                                <span class='badge badge-success'>
                                    <i class='fas fa-check-circle'></i> Terrain Verified
                                </span>
                            <?php else: ?>
                                <span class='badge badge-pending'>
                                    <i class='fas fa-clock'></i> Terrain Pending Verification
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Documents Tab -->
                <div id='documents' class='tab-content'>
                    <div class='documents-section'>
                        <?php if ($document_count > 0): ?>
                            <div class='documents-header'>
                                <h6 class='documents-title'>
                                    <i class='fas fa-file-alt'></i>
                                    All Documents (<?php echo $document_count; ?>)
                                </h6>
                            </div>
                            <div class='documents-grid'>
                                <?php while ($doc = mysqli_fetch_assoc($docs_result)): ?>
                                <div class='document-card'>
                                    <div class='document-icon'>
                                        <i class='fas <?php echo getDocumentIcon($doc['file_name']); ?>'></i>
                                    </div>
                                    <div class='document-info'>
                                        <h6 class='document-name' title='<?php echo htmlspecialchars($doc['file_name']); ?>'>
                                            <?php echo htmlspecialchars($doc['file_name']); ?>
                                        </h6>
                                        <div class='document-meta'>
                                            <span class='meta-item'>
                                                <i class='fas fa-tag'></i>
                                                <?php echo htmlspecialchars($doc['type_name']); ?>
                                            </span>
                                            <span class='meta-item'>
                                                <i class='fas fa-calendar'></i>
                                                <?php echo date('M d, Y', strtotime($doc['upload_date'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class='document-actions'>
                                        <a href='<?php echo htmlspecialchars($doc['file_path']); ?>' 
                                           class='btn-action btn-view' 
                                           target='_blank' 
                                           title='View Document'>
                                            <i class='fas fa-eye'></i>
                                        </a>
                                        <a href='<?php echo htmlspecialchars($doc['file_path']); ?>' 
                                           class='btn-action btn-download' 
                                           download 
                                           title='Download Document'>
                                            <i class='fas fa-download'></i>
                                        </a>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class='empty-state'>
                                <div class='empty-icon'>
                                    <i class='fas fa-folder-open'></i>
                                </div>
                                <h5 class='empty-title'>No Documents Available</h5>
                                <p class='empty-description'>There are currently no documents uploaded for this project. Documents such as plans, permits, and reports will appear here once uploaded.</p>
                                <div class='empty-suggestion'>
                                    <i class='fas fa-lightbulb'></i>
                                    <span>Contact the project manager to upload necessary documents</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

        <style>
            /* Scope all styles to the modal container */
            .project-details-container * {
                box-sizing: border-box;
            }

            .project-details-container {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                max-height: 75vh;
                display: flex;
                flex-direction: column;
                background: #ffffff;
            }

            /* Tabs Navigation - Scoped to modal */
            .project-details-container .tabs-navigation {
                display: flex;
                background: #f8fafc;
                border-bottom: 2px solid #e5e7eb;
                padding: 0;
                position: sticky;
                top: 0;
                z-index: 10;
            }

            .project-details-container .tab-btn {
                flex: 1;
                padding: 16px 20px;
                background: transparent;
                border: none;
                border-bottom: 3px solid transparent;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                font-size: 14px;
                font-weight: 600;
                color: #6b7280;
                transition: all 0.3s ease;
                position: relative;
            }

            .project-details-container .tab-btn:hover {
                background: #f1f5f9;
                color: #3b82f6;
            }

            .project-details-container .tab-btn.active {
                color: #3b82f6;
                background: #ffffff;
                border-bottom-color: #3b82f6;
            }

            .project-details-container .tab-btn i {
                font-size: 16px;
            }

            .project-details-container .badge-count {
                background: #3b82f6;
                color: white;
                padding: 2px 8px;
                border-radius: 12px;
                font-size: 11px;
                font-weight: 700;
                min-width: 20px;
                text-align: center;
            }

            .project-details-container .tab-btn.active .badge-count {
                background: #2563eb;
            }

            /* Tab Content */
            .project-details-container .tabs-content {
                flex: 1;
                overflow-y: auto;
            }

            .project-details-container .tab-content {
                display: none;
            }

            .project-details-container .tab-content.active {
                display: block;
            }

            /* Data Source Badge */
            .project-details-container .data-source-badge {
                padding: 12px 16px;
                border-radius: 8px;
                margin-bottom: 20px;
                display: flex;
                align-items: flex-start;
                gap: 12px;
                border-left: 4px solid;
            }

            .project-details-container .data-source-badge.surveyor {
                background-color: #d1fae5;
                border-left-color: #10b981;
                color: #065f46;
            }

            .project-details-container .data-source-badge.client {
                background-color: #fef3c7;
                border-left-color: #f59e0b;
                color: #92400e;
            }

            .project-details-container .data-source-badge i {
                font-size: 20px;
                margin-top: 2px;
                flex-shrink: 0;
            }

            .project-details-container .data-source-badge.surveyor i {
                color: #10b981;
            }

            .project-details-container .data-source-badge.client i {
                color: #f59e0b;
            }

            .project-details-container .data-source-badge > div {
                flex: 1;
            }

            .project-details-container .data-source-badge span {
                display: block;
                font-weight: 600;
                font-size: 13px;
                margin-bottom: 4px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .project-details-container .data-source-badge small {
                display: block;
                font-size: 12px;
                line-height: 1.5;
                opacity: 0.9;
            }

            /* Land Information Styles */
            .project-details-container .detail-section {
                margin-bottom: 0;
                padding: 24px;
                border-bottom: 1px solid #e5e7eb;
                background: #ffffff;
                transition: background-color 0.2s ease;
            }

            .detail-section:hover {
                background-color: #f9fafb;
            }

            .detail-section:last-child {
                border-bottom: none;
            }

            .status-section {
                background: #f8fafc;
            }

            .section-title {
                color: #1f2937;
                font-size: 15px;
                font-weight: 600;
                margin: 0 0 18px 0;
                display: flex;
                align-items: center;
                gap: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .section-title i {
                color: #3b82f6;
                font-size: 16px;
            }

            .detail-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }

            .terrain-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .detail-item {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }

            .detail-item.full-width {
                grid-column: 1 / -1;
            }

            .detail-label {
                font-size: 12px;
                color: #6b7280;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .detail-value {
                font-size: 15px;
                color: #111827;
                font-weight: 500;
                line-height: 1.5;
            }

            .detail-value.highlight {
                color: #2563eb;
                font-weight: 600;
            }

            .text-muted {
                color: #9ca3af !important;
                font-style: italic;
                font-weight: 400 !important;
            }

            .description-content {
                background-color: #f9fafb;
                padding: 16px 18px;
                border-radius: 8px;
                border-left: 4px solid #3b82f6;
            }

            .description-content p {
                margin: 0;
                line-height: 1.7;
                color: #374151;
                font-size: 14px;
            }

            .status-badges {
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
            }

            .badge {
                padding: 10px 18px;
                border-radius: 6px;
                font-size: 13px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                text-transform: uppercase;
                letter-spacing: 0.3px;
            }

            .badge-success {
                background-color: #d1fae5;
                color: #065f46;
                border: 1px solid #a7f3d0;
            }

            .badge-pending {
                background-color: #fef3c7;
                color: #92400e;
                border: 1px solid #fde68a;
            }

            .badge i {
                font-size: 14px;
            }

            /* Documents Section */
            .documents-section {
                padding: 24px;
            }

            .documents-header {
                margin-bottom: 20px;
                padding-bottom: 16px;
                border-bottom: 2px solid #e5e7eb;
            }

            .documents-title {
                font-size: 16px;
                font-weight: 600;
                color: #1f2937;
                margin: 0;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .documents-title i {
                color: #3b82f6;
                font-size: 18px;
            }

            .documents-grid {
                display: grid;
                gap: 16px;
            }

            .document-card {
                display: flex;
                align-items: center;
                gap: 16px;
                padding: 16px;
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                transition: all 0.3s ease;
            }

            .document-card:hover {
                background: #ffffff;
                border-color: #3b82f6;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                transform: translateY(-2px);
            }

            .document-icon {
                width: 48px;
                height: 48px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #dbeafe;
                border-radius: 8px;
                flex-shrink: 0;
            }

            .document-icon i {
                font-size: 24px;
                color: #3b82f6;
            }

            .document-info {
                flex: 1;
                min-width: 0;
            }

            .document-name {
                font-size: 15px;
                font-weight: 600;
                color: #1f2937;
                margin: 0 0 8px 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .document-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-bottom: 6px;
            }

            .meta-item {
                font-size: 12px;
                color: #6b7280;
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .meta-item i {
                font-size: 11px;
            }

            .document-actions {
                display: flex;
                gap: 8px;
                flex-shrink: 0;
            }

            .btn-action {
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 6px;
                border: none;
                cursor: pointer;
                transition: all 0.2s ease;
                text-decoration: none;
            }

            .btn-view {
                background: #dbeafe;
                color: #2563eb;
            }

            .btn-view:hover {
                background: #3b82f6;
                color: white;
            }

            .btn-download {
                background: #d1fae5;
                color: #059669;
            }

            .btn-download:hover {
                background: #10b981;
                color: white;
            }

            .btn-action i {
                font-size: 14px;
            }

            /* Empty State */
            .empty-state {
                text-align: center;
                padding: 80px 24px;
                color: #6b7280;
            }

            .empty-icon {
                width: 120px;
                height: 120px;
                margin: 0 auto 24px;
                background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
            }

            .empty-icon::before {
                content: '';
                position: absolute;
                width: 100%;
                height: 100%;
                border-radius: 50%;
                border: 2px dashed #bfdbfe;
                animation: rotate 20s linear infinite;
            }

            @keyframes rotate {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }

            .empty-state i {
                font-size: 48px;
                color: #60a5fa;
            }

            .empty-title {
                font-size: 20px;
                font-weight: 600;
                color: #1f2937;
                margin: 0 0 12px 0;
            }

            .empty-description {
                font-size: 15px;
                color: #6b7280;
                margin: 0 auto 24px;
                max-width: 450px;
                line-height: 1.6;
            }

            .empty-suggestion {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 12px 20px;
                background: #fef3c7;
                border: 1px solid #fde68a;
                border-radius: 8px;
                color: #92400e;
                font-size: 14px;
                font-weight: 500;
            }

            .empty-suggestion i {
                font-size: 16px;
                color: #f59e0b;
            }

            /* Scrollbar Styling */
            .tabs-content::-webkit-scrollbar {
                width: 6px;
            }

            .tabs-content::-webkit-scrollbar-track {
                background: #f3f4f6;
            }

            .tabs-content::-webkit-scrollbar-thumb {
                background: #d1d5db;
                border-radius: 10px;
            }

            .tabs-content::-webkit-scrollbar-thumb:hover {
                background: #9ca3af;
            }

            /* Responsive Design */
            @media (max-width: 992px) {
                .terrain-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media (max-width: 768px) {
                .tab-btn span:not(.badge-count) {
                    display: none;
                }

                .detail-grid,
                .terrain-grid {
                    grid-template-columns: 1fr;
                }

                .detail-section,
                .documents-section {
                    padding: 20px 16px;
                }

                .document-card {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .document-actions {
                    width: 100%;
                    justify-content: flex-end;
                }

                .section-title {
                    font-size: 14px;
                }

                .detail-value {
                    font-size: 14px;
                }

                .empty-state {
                    padding: 60px 16px;
                }

                .empty-icon {
                    width: 100px;
                    height: 100px;
                }

                .empty-state i {
                    font-size: 40px;
                }

                .empty-title {
                    font-size: 18px;
                }

                .empty-description {
                    font-size: 14px;
                }

                .empty-suggestion {
                    font-size: 13px;
                    padding: 10px 16px;
                }
            }
        </style>

        <script>
            // Tab switching functionality
            document.querySelectorAll('.tab-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and contents
                    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked button and corresponding content
                    this.classList.add('active');
                    document.getElementById(targetTab).classList.add('active');
                });
            });
        </script>
        <?php
    } else {
        ?>
        <div class='alert-container'>
            <div class='alert alert-info'>
                <i class='fas fa-info-circle'></i>
                <div class='alert-content'>
                    <strong>No Project Data Found</strong>
                    <p>No project information found for this request. The project may not have been set up yet, or the land may not be associated with this project.</p>
                </div>
            </div>
        </div>
        <style>
            .alert-container {
                padding: 32px 24px;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 300px;
            }
            
            .alert {
                padding: 20px 24px;
                border-radius: 8px;
                display: flex;
                align-items: flex-start;
                gap: 16px;
                max-width: 500px;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            }
            
            .alert-info {
                background-color: #dbeafe;
                color: #1e40af;
                border: 1px solid #93c5fd;
            }
            
            .alert i {
                font-size: 24px;
                margin-top: 2px;
                flex-shrink: 0;
            }

            .alert-content {
                flex: 1;
            }

            .alert-content strong {
                display: block;
                font-size: 15px;
                margin-bottom: 6px;
                font-weight: 600;
            }

            .alert-content p {
                margin: 0;
                font-size: 14px;
                line-height: 1.5;
                opacity: 0.9;
            }
        </style>
        <?php
    }
} else {
    ?>
    <div class='alert-container'>
        <div class='alert alert-danger'>
            <i class='fas fa-exclamation-triangle'></i>
            <div class='alert-content'>
                <strong>Invalid Request</strong>
                <p>The request could not be processed. Please provide a valid project ID or contact support if the issue persists.</p>
            </div>
        </div>
    </div>
    <style>
        .alert-container {
            padding: 32px 24px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 300px;
        }
        
        .alert {
            padding: 20px 24px;
            border-radius: 8px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            max-width: 500px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert i {
            font-size: 24px;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .alert-content {
            flex: 1;
        }

        .alert-content strong {
            display: block;
            font-size: 15px;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .alert-content p {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
            opacity: 0.9;
        }
    </style>
    <?php
}

mysqli_close($con);
?>