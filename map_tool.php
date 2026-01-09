<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Land Mapping Tool</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      overflow: hidden;
      background: #f8f9fa;
    }

    #root {
      width: 100vw;
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* Loading Spinner */
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .spinner {
      width: 48px;
      height: 48px;
      border: 3px solid #f3f3f3;
      border-top: 3px solid #0066cc;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto;
    }

    /* Sidebar Scrollbar */
    .sidebar-content::-webkit-scrollbar {
      width: 8px;
    }

    .sidebar-content::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }

    .sidebar-content::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 4px;
    }

    .sidebar-content::-webkit-scrollbar-thumb:hover {
      background: #555;
    }

    /* Card Styles */
    .info-card {
      padding: 14px;
      border-radius: 8px;
      margin-bottom: 12px;
    }

    .card-primary {
      background: #e7f3ff;
      border: 2px solid #0066cc;
    }

    .card-secondary {
      background: #f8f9fa;
      border: 2px solid #dee2e6;
    }

    .card-success {
      background: #d4edda;
      border: 2px solid #28a745;
    }

    .card-warning {
      background: #fff3cd;
      border: 2px solid #ffc107;
    }

    .card-info {
      background: #d1ecf1;
      border: 2px solid #17a2b8;
    }

    /* Button Styles */
    .btn {
      padding: 12px 20px;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .btn-primary {
      background: #0066cc;
      color: white;
    }

    .btn-primary:hover:not(:disabled) {
      background: #0052a3;
    }

    .btn-success {
      background: #28a745;
      color: white;
    }

    .btn-success:hover:not(:disabled) {
      background: #218838;
    }

    .btn-secondary {
      background: #6c757d;
      color: white;
    }

    .btn-secondary:hover:not(:disabled) {
      background: #5a6268;
    }

    /* Badge */
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      margin-left: 8px;
    }

    .badge-success {
      background: #28a745;
      color: white;
    }

    /* Search Results Dropdown */
    .search-dropdown {
      position: absolute;
      top: 100%;
      left: 0;
      right: 120px;
      margin-top: 4px;
      background: white;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      max-height: 300px;
      overflow-y: auto;
      z-index: 1000;
    }

    .search-result-item {
      padding: 12px 14px;
      cursor: pointer;
      border-bottom: 1px solid #f1f1f1;
      transition: background 0.2s;
    }

    .search-result-item:last-child {
      border-bottom: none;
    }

    .search-result-item:hover {
      background: #f8f9fa;
    }

    /* Sticky Footer */
    .sticky-footer {
      position: sticky;
      bottom: 0;
      background: white;
      padding: 16px;
      border-top: 2px solid #e9ecef;
      box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.05);
    }
  </style>
</head>

<body>
  <div id="root"></div>

  <!-- Leaflet Libraries -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
  <script src="https://unpkg.com/leaflet-geometryutil@0.10.1/src/leaflet.geometryutil.js"></script>
  
  <!-- React -->
  <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

  <script type="text/babel">
    const { useState, useEffect, useRef } = React;

    const LandMappingTool = () => {
      // State Management
      const [map, setMap] = useState(null);
      const [drawnItems, setDrawnItems] = useState(null);
      const [measurements, setMeasurements] = useState(null);
      const [isLoading, setIsLoading] = useState(false);
      const [useSatellite, setUseSatellite] = useState(false);
      const [searchQuery, setSearchQuery] = useState('');
      const [searchResults, setSearchResults] = useState([]);
      const [isSearching, setIsSearching] = useState(false);
      
      // Refs
      const mapRef = useRef(null);
      const satelliteLayerRef = useRef(null);
      const streetLayerRef = useRef(null);

      // Check if opened from land listing page (photo mode)
      const isPhotoMode = window.location.search.includes('source=listing');

      // Office location for distance calculations (only in service mode)
      const OFFICE_LOCATION = {
        lat: 34.56677094701796, 
        lng: 36.086523881335964,
        name: "Our Office Location"
      };

      // Initialize map on mount
      useEffect(() => {
        if (window.L && mapRef.current) {
          initializeMap();
        }
      }, []);

      /**
       * Initialize Leaflet map with drawing controls
       */
      const initializeMap = () => {
        const L = window.L;
        
        // Create map centered on Lebanon
        const mapInstance = L.map(mapRef.current).setView([33.8547, 35.8623], 9);

        // Street map layer
        const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap contributors',
          maxZoom: 19
        }).addTo(mapInstance);

        // Satellite layer (not added by default)
        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
          attribution: 'Tiles &copy; Esri',
          maxZoom: 19
        });

        streetLayerRef.current = streetLayer;
        satelliteLayerRef.current = satelliteLayer;

        // Add office marker (only in service mode)
        if (!isPhotoMode) {
          const officeIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
          });

          L.marker([OFFICE_LOCATION.lat, OFFICE_LOCATION.lng], { icon: officeIcon })
            .addTo(mapInstance)
            .bindPopup(`<b>${OFFICE_LOCATION.name}</b><br>Starting point for distance calculations`)
            .openPopup();
        }

        // Drawing layer
        const drawnItemsLayer = new L.FeatureGroup();
        mapInstance.addLayer(drawnItemsLayer);

        // Drawing controls
        const drawControl = new L.Control.Draw({
          draw: {
            polygon: {
              allowIntersection: false,
              drawError: {
                color: '#e74c3c',
                message: '<strong>Error:</strong> Shape edges cannot cross!'
              },
              shapeOptions: {
                color: '#3498db',
                weight: 3
              }
            },
            polyline: false,
            circle: false,
            rectangle: true,
            marker: true,
            circlemarker: false
          },
          edit: {
            featureGroup: drawnItemsLayer,
            remove: true
          }
        });
        mapInstance.addControl(drawControl);

        // Event handlers
        mapInstance.on(L.Draw.Event.CREATED, (e) => {
          drawnItemsLayer.clearLayers();
          drawnItemsLayer.addLayer(e.layer);
          analyzeLand(e.layer, mapInstance);
        });

        mapInstance.on(L.Draw.Event.EDITED, (e) => {
          e.layers.eachLayer((layer) => {
            analyzeLand(layer, mapInstance);
          });
        });

        mapInstance.on(L.Draw.Event.DELETED, () => {
          setMeasurements(null);
        });

        setMap(mapInstance);
        setDrawnItems(drawnItemsLayer);
      };

      /**
       * Toggle between satellite and street view
       */
      const toggleMapView = () => {
        if (!map) return;

        if (useSatellite) {
          map.removeLayer(satelliteLayerRef.current);
          map.addLayer(streetLayerRef.current);
        } else {
          map.removeLayer(streetLayerRef.current);
          map.addLayer(satelliteLayerRef.current);
        }
        setUseSatellite(!useSatellite);
      };

      /**
       * Search for locations using Photon API
       */
      const handleSearch = async () => {
        if (!searchQuery.trim() || !map) return;

        setIsSearching(true);
        setSearchResults([]);

        try {
          const response = await fetch(
            `https://photon.komoot.io/api/?q=${encodeURIComponent(searchQuery)}&limit=5&lang=en`
          );

          if (!response.ok) throw new Error('Search failed');
          const data = await response.json();
          
          // Filter for Lebanon or nearby
          const filtered = data.features.filter(f => 
            !f.properties.country || 
            f.properties.country === 'Lebanon' || 
            f.properties.country === 'لبنان' ||
            (f.geometry.coordinates[1] > 33 && f.geometry.coordinates[1] < 35 &&
             f.geometry.coordinates[0] > 35 && f.geometry.coordinates[0] < 37)
          );

          setSearchResults(filtered.length > 0 ? filtered : data.features.slice(0, 5));
        } catch (error) {
          console.error('Search error:', error);
          alert('Search failed. Please try again.');
        } finally {
          setIsSearching(false);
        }
      };

      /**
       * Select search result and place marker
       */
      const selectSearchResult = (result) => {
        if (!map || !drawnItems) return;

        const [lon, lat] = result.geometry.coordinates;
        const L = window.L;
        
        drawnItems.clearLayers();
        const marker = L.marker([lat, lon]);
        drawnItems.addLayer(marker);
        map.setView([lat, lon], 14);
        analyzeLand(marker, map);
        
        setSearchResults([]);
        setSearchQuery('');
      };

      /**
       * Get display name for search result
       */
      const getDisplayName = (result) => {
        const props = result.properties;
        const parts = [];
        
        if (props.name) parts.push(props.name);
        if (props.city) parts.push(props.city);
        else if (props.county) parts.push(props.county);
        if (props.state && props.state !== props.city) parts.push(props.state);
        if (props.country) parts.push(props.country);
        
        return parts.join(', ') || 'Unknown location';
      };

      /**
       * Analyze land - extract coordinates, calculate area, get elevation
       */
      const analyzeLand = async (layer, mapInstance) => {
        setIsLoading(true);
        const L = window.L;
        
        try {
          let coordinates = [];
          let area = 0;
          let isMarker = false;
          let center;

          // Extract data based on shape type
          if (layer instanceof L.Polygon || layer instanceof L.Rectangle) {
            coordinates = layer.getLatLngs()[0].map(ll => [ll.lat, ll.lng]);
            area = L.GeometryUtil.geodesicArea(layer.getLatLngs()[0]);
            center = layer.getBounds().getCenter();
            isMarker = false;
          } else if (layer instanceof L.Marker) {
            const ll = layer.getLatLng();
            coordinates = [[ll.lat, ll.lng]];
            center = ll;
            area = 0;
            isMarker = true;
          }

          // Calculate distance from office (only in service mode)
          let distance = 0;
          if (!isPhotoMode) {
            distance = calculateDistance(
              OFFICE_LOCATION.lat, 
              OFFICE_LOCATION.lng, 
              center.lat, 
              center.lng
            );
          }

          // Get elevation data (only in service mode)
          let elevationData = null;
          if (!isPhotoMode) {
            elevationData = await getElevationData(coordinates, isMarker);
          }

          // Get driving route (only in service mode)
          let route = null;
          if (!isPhotoMode) {
            try {
              route = await getRoute(OFFICE_LOCATION, center, mapInstance);
            } catch (e) {
              console.log('Route not available');
            }
          }

          // Store measurements
          setMeasurements({
            coordinates,
            area: (area / 10000).toFixed(2), // hectares
            areaM2: area.toFixed(2), // square meters
            center: [center.lat, center.lng],
            elevation: elevationData,
            distance: distance.toFixed(2), // km
            distanceMeters: (distance * 1000).toFixed(2), // meters
            route,
            officeLocation: OFFICE_LOCATION,
            isMarker: isMarker
          });

          setIsLoading(false);

        } catch (error) {
          console.error('Error analyzing land:', error);
          setIsLoading(false);
          alert('An error occurred: ' + error.message);
        }
      };

      /**
       * Get elevation data from Open-Meteo API
       */
      const getElevationData = async (coordinates, isMarker) => {
        let samplePoints = coordinates;
        if (!isMarker && coordinates.length > 10) {
          const step = Math.floor(coordinates.length / 10);
          samplePoints = coordinates.filter((_, index) => index % step === 0).slice(0, 10);
        }

        try {
          const elevations = [];
          
          for (const point of samplePoints) {
            const response = await fetch(
              `https://api.open-meteo.com/v1/elevation?latitude=${point[0]}&longitude=${point[1]}`,
              { 
                signal: AbortSignal.timeout(6000),
                headers: { 'Accept': 'application/json' }
              }
            );

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const data = await response.json();
            if (data.elevation && Array.isArray(data.elevation) && data.elevation.length > 0) {
              elevations.push(data.elevation[0]);
            }
          }

          if (elevations.length === 0) throw new Error('No elevation data');

          return processElevations(elevations, samplePoints, isMarker);

        } catch (error) {
          console.error('Elevation API failed:', error);
          throw error;
        }
      };

      /**
       * Process elevation data and calculate slope
       */
      const processElevations = (elevations, points, isMarker) => {
        const avgElevation = elevations.reduce((a, b) => a + b, 0) / elevations.length;
        const maxElevation = Math.max(...elevations);
        const minElevation = Math.min(...elevations);

        if (isMarker) {
          return {
            average: avgElevation.toFixed(2),
            max: avgElevation.toFixed(2),
            min: avgElevation.toFixed(2),
            slope: '0.00',
            terrainFactor: 'Flat',
            source: 'Open-Meteo',
            isReal: true
          };
        }

        const elevationDiff = maxElevation - minElevation;

        let maxDistance = 0;
        for (let i = 0; i < points.length; i++) {
          for (let j = i + 1; j < points.length; j++) {
            const dist = calculateDistance(
              points[i][0], points[i][1],
              points[j][0], points[j][1]
            ) * 1000;
            maxDistance = Math.max(maxDistance, dist);
          }
        }

        const slope = maxDistance > 0 ? (elevationDiff / maxDistance) * 100 : 0;

        let terrainFactor;
        if (slope < 5) terrainFactor = 1.0;
        else if (slope < 15) terrainFactor = 1.2;
        else if (slope < 30) terrainFactor = 1.5;
        else terrainFactor = 2.0;

        return {
          average: avgElevation.toFixed(2),
          max: maxElevation.toFixed(2),
          min: minElevation.toFixed(2),
          slope: slope.toFixed(2),
          terrainFactor: terrainFactor,
          source: 'Open-Meteo',
          isReal: true
        };
      };

      /**
       * Get driving route using OSRM API
       */
      const getRoute = async (from, to, mapInstance) => {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000);

        try {
          const response = await fetch(
            `https://router.project-osrm.org/route/v1/driving/${from.lng},${from.lat};${to.lng},${to.lat}?overview=full&geometries=geojson`,
            { signal: controller.signal }
          );

          clearTimeout(timeoutId);

          if (!response.ok) throw new Error('Route API failed');
          const data = await response.json();

          if (data.routes && data.routes.length > 0) {
            const route = data.routes[0];
            
            if (mapInstance) {
              const L = window.L;
              const routeCoords = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
              L.polyline(routeCoords, {
                color: '#e74c3c',
                weight: 4,
                opacity: 0.7
              }).addTo(mapInstance);
            }
            
            return {
              distance: (route.distance / 1000).toFixed(2),
              duration: (route.duration / 60).toFixed(0)
            };
          }
          return null;
        } catch (error) {
          clearTimeout(timeoutId);
          throw error;
        }
      };

      /**
       * Calculate distance using Haversine formula
       */
      const calculateDistance = (lat1, lon1, lat2, lon2) => {
        const R = 6371;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a =
          Math.sin(dLat / 2) * Math.sin(dLat / 2) +
          Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
          Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
      };

      const toRad = (degrees) => degrees * (Math.PI / 180);

      /**
       * Clear all drawings
       */
      const clearAll = () => {
        if (drawnItems) drawnItems.clearLayers();
        setMeasurements(null);
        
        if (map) {
          map.eachLayer((layer) => {
            if (layer instanceof window.L.Polyline && !(layer instanceof window.L.Polygon)) {
              map.removeLayer(layer);
            }
          });
        }
      };

      /**
       * Send data to parent window (service mode only)
       */
      const saveToForm = () => {
        if (measurements && window.opener) {
          window.opener.postMessage({
            type: 'MAP_DATA',
            data: {
              latitude: measurements.center[0],
              longitude: measurements.center[1],
              area: measurements.isMarker ? 0 : measurements.areaM2,
              elevation_avg: measurements.elevation.average,
              elevation_max: measurements.elevation.max,
              elevation_min: measurements.elevation.min,
              slope: measurements.elevation.slope,
              terrain_factor: parseFloat(measurements.elevation.terrainFactor) || 1.0,
              distance_from_office: measurements.route ? measurements.route.distance : measurements.distance
            }
          }, '*');
          window.close();
        }
      };

      // Render
      return (
        <div style={{ display: 'flex', flexDirection: 'column', height: '100vh' }}>
          {/* Header */}
          <header style={{ background: 'white', padding: '16px', borderBottom: '1px solid #dee2e6', boxShadow: '0 2px 4px rgba(0,0,0,0.1)' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
              <div>
                <h1 style={{ fontSize: '24px', fontWeight: 'bold', color: '#212529', margin: 0, display: 'flex', alignItems: 'center', gap: '8px' }}>
                  📍 Land Mapping Tool
                  {isPhotoMode && <span className="badge badge-success">Photo Mode</span>}
                </h1>
                <p style={{ fontSize: '14px', color: '#6c757d', margin: '4px 0 0 0' }}>
                  {isPhotoMode 
                    ? 'Draw your land boundaries and take a screenshot' 
                    : 'Draw land boundaries to analyze area, elevation, and distance'}
                </p>
              </div>
              
              <button 
                onClick={toggleMapView}
                className="btn btn-secondary"
              >
                🛰️ {useSatellite ? 'Satellite View' : 'Street View'}
              </button>
            </div>

            {/* Search Bar */}
            <div style={{ display: 'flex', gap: '8px', position: 'relative' }}>
              <input 
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                onKeyPress={(e) => e.key === 'Enter' && handleSearch()}
                placeholder="Search for villages, cities, or locations..."
                style={{
                  flex: 1,
                  padding: '10px 14px',
                  border: '1px solid #dee2e6',
                  borderRadius: '6px',
                  fontSize: '14px',
                  outline: 'none'
                }}
              />
              <button 
                onClick={handleSearch}
                disabled={isSearching || !searchQuery.trim()}
                className="btn btn-primary"
              >
                {isSearching ? '🔍 Searching...' : '🔍 Search'}
              </button>

              {/* Search Results */}
              {searchResults.length > 0 && (
                <div className="search-dropdown">
                  {searchResults.map((result, index) => (
                    <div
                      key={index}
                      onClick={() => selectSearchResult(result)}
                      className="search-result-item"
                    >
                      <div style={{ fontWeight: '600', fontSize: '14px', color: '#212529' }}>
                        {result.properties.name || 'Unnamed'}
                      </div>
                      <div style={{ fontSize: '12px', color: '#6c757d', marginTop: '2px' }}>
                        {getDisplayName(result)}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </header>

          {/* Main Content */}
          <div style={{ flex: 1, display: 'flex' }}>
            {/* Map */}
            <div ref={mapRef} style={{ flex: 1, height: '100%' }} />

            {/* Sidebar */}
            <aside style={{ width: '384px', background: 'white', boxShadow: '-2px 0 8px rgba(0,0,0,0.1)', display: 'flex', flexDirection: 'column' }}>
              {/* Sidebar Header */}
              <div style={{ padding: '16px', borderBottom: '1px solid #e9ecef' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <h2 style={{ fontSize: '18px', fontWeight: '600', color: '#212529', margin: 0 }}>
                    {isPhotoMode ? 'Screenshot Guide' : 'Land Analysis'}
                  </h2>
                  {measurements && (
                    <button onClick={clearAll} className="btn" style={{ padding: '8px', background: 'transparent' }}>
                      ✕
                    </button>
                  )}
                </div>
              </div>

              {/* Sidebar Content */}
              <div className="sidebar-content" style={{ flex: 1, padding: '16px', overflowY: 'auto' }}>
                {/* Empty State */}
                {!measurements && !isLoading && (
                  <div style={{ textAlign: 'center', padding: '32px 0', color: '#6c757d' }}>
                    <div style={{ fontSize: '48px', margin: '0 auto 16px', opacity: 0.5 }}>📍</div>
                    <p style={{ fontSize: '14px', marginBottom: '8px' }}>Use the drawing tools to mark the land</p>
                    {!isPhotoMode && <p style={{ fontSize: '12px' }}>Red marker shows office location</p>}
                    {isPhotoMode && <p style={{ fontSize: '12px' }}>Follow screenshot instructions below</p>}
                  </div>
                )}

                {/* Loading State */}
                {isLoading && (
                  <div style={{ textAlign: 'center', padding: '32px 0' }}>
                    <div className="spinner" />
                    <p style={{ fontSize: '14px', color: '#6c757d', marginTop: '16px' }}>
                      {isPhotoMode ? 'Preparing map...' : 'Analyzing land data...'}
                    </p>
                  </div>
                )}

                {/* SERVICE MODE - Show Analysis */}
                {!isLoading && measurements && !isPhotoMode && (
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                    {/* Area */}
                    <div className="info-card card-primary">
                      <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                        <span style={{ fontSize: '20px' }}>📏</span>
                        <h3 style={{ fontWeight: '600', color: '#212529', margin: 0, fontSize: '16px' }}>Area</h3>
                      </div>
                      {measurements.isMarker ? (
                        <p style={{ fontSize: '14px', color: '#6c757d', margin: 0 }}>
                          Single point (no area)
                        </p>
                      ) : (
                        <>
                          <p style={{ fontSize: '24px', fontWeight: 'bold', color: '#0066cc', margin: '4px 0' }}>
                            {measurements.area} hectares
                          </p>
                          <p style={{ fontSize: '14px', color: '#6c757d', margin: 0 }}>
                            {measurements.areaM2} m²
                          </p>
                        </>
                      )}
                    </div>

                    {/* Coordinates */}
                    <div className="info-card card-secondary">
                      <h3 style={{ fontWeight: '600', color: '#212529', marginBottom: '8px', fontSize: '16px' }}>
                        {measurements.isMarker ? 'Point Coordinates' : 'Center Coordinates'}
                      </h3>
                      <p style={{ fontSize: '14px', margin: '4px 0' }}>
                        <strong>Latitude:</strong> {measurements.center[0].toFixed(6)}
                      </p>
                      <p style={{ fontSize: '14px', margin: '4px 0' }}>
                        <strong>Longitude:</strong> {measurements.center[1].toFixed(6)}
                      </p>
                    </div>
                  </div>
                )}

                {/* PHOTO MODE - Show Screenshot Instructions */}
                {!isLoading && measurements && isPhotoMode && (
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                    {/* Simple Screenshot Message */}
                    <div className="info-card card-success">
                      <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                        <span style={{ fontSize: '32px' }}>✅</span>
                        <h3 style={{ fontWeight: '600', margin: 0, fontSize: '18px', color: '#155724' }}>
                          Map Ready!
                        </h3>
                      </div>
                      <p style={{ fontSize: '15px', margin: 0, lineHeight: '1.6', color: '#155724' }}>
                        Take a screenshot of your land drawing and upload it in the listing form.
                      </p>
                    </div>
                  </div>
                )}
              </div>

              {/* Sticky Footer Button */}
              {!isLoading && measurements && (
                <div className="sticky-footer">
                  {/* Service Mode */}
                  {!isPhotoMode && (
                    <button onClick={saveToForm} className="btn btn-primary" style={{ width: '100%' }}>
                      💾 Use This Data
                    </button>
                  )}
                  
                  {/* Photo Mode */}
                  {isPhotoMode && (
                    <div className="info-card card-info" style={{ margin: 0 }}>
                      <p style={{ margin: 0, fontSize: '14px', fontWeight: '600', textAlign: 'center', color: '#0c5460' }}>
                        📸 Take a screenshot and upload it in your listing
                      </p>
                    </div>
                  )}
                </div>
              )}
            </aside>
          </div>

          {/* Footer */}
          <footer style={{ background: 'white', borderTop: '1px solid #dee2e6', padding: '12px 16px', fontSize: '14px', color: '#6c757d' }}>
            <p style={{ margin: 0 }}>
              <strong>Instructions:</strong> 
              {isPhotoMode ? (
                <> Draw your land boundaries, then take a screenshot and upload it when listing your land.</>
              ) : (
                <> Draw land boundaries to analyze area, elevation, slope, and distance from office.</>
              )}
            </p>
          </footer>
        </div>
      );
    };

    // Render App
    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<LandMappingTool />);
  </script>
</body>
</html>