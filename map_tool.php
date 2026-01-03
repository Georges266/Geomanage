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
    }

    #root {
      width: 100vw;
      height: 100vh;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
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

    .sidebar-content {
      max-height: calc(100vh - 120px);
      overflow-y: auto;
      overflow-x: hidden;
      padding-bottom: 80px;
    }

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

    .save-button-container {
      position: sticky;
      bottom: 0;
      background: white;
      padding: 16px;
      border-top: 2px solid #e9ecef;
      margin: 0 -16px -16px -16px;
      box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.05);
    }

    .api-badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 600;
      margin-left: 8px;
    }

    .api-badge.real {
      background: #d4edda;
      color: #155724;
    }
  </style>
</head>

<body>
  <div id="root"></div>

  <!-- Leaflet mapping libraries -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
  <script src="https://unpkg.com/leaflet-geometryutil@0.10.1/src/leaflet.geometryutil.js"></script>
  
  <!-- React libraries -->
  <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
  <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

  <script type="text/babel">
    const { useState, useEffect, useRef } = React;

    const LandMappingTool = () => {
      // State management
      const [map, setMap] = useState(null);
      const [drawnItems, setDrawnItems] = useState(null);
      const [measurements, setMeasurements] = useState(null);
      const [isLoading, setIsLoading] = useState(false);
      const [useSatellite, setUseSatellite] = useState(false);
      const [searchQuery, setSearchQuery] = useState('');
      const [searchResults, setSearchResults] = useState([]);
      const [isSearching, setIsSearching] = useState(false);
      const mapRef = useRef(null);
      const satelliteLayerRef = useRef(null);
      const streetLayerRef = useRef(null);

      // Office location - used as reference point for distance calculations
      const OFFICE_LOCATION = {
        lat: 34.56677094701796, 
        lng: 36.086523881335964,
        name: "Our Office Location"
      };

      // Initialize map when component mounts
      useEffect(() => {
        if (window.L && mapRef.current) {
          initializeMap();
        }
      }, []);

      /**
       * Initialize the Leaflet map with drawing controls
       */
      const initializeMap = () => {
        const L = window.L;
        
        // Create map centered on Lebanon
        const mapInstance = L.map(mapRef.current).setView([33.8547, 35.8623], 9);

        // Add OpenStreetMap tile layer (default)
        const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap contributors',
          maxZoom: 19
        }).addTo(mapInstance);

        // Add Esri World Imagery satellite layer (not added by default)
        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
          attribution: 'Tiles &copy; Esri',
          maxZoom: 19
        });

        // Store layer references
        streetLayerRef.current = streetLayer;
        satelliteLayerRef.current = satelliteLayer;

        // Create custom red marker icon for office location
        const officeIcon = L.icon({
          iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
          shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
          iconSize: [25, 41],
          iconAnchor: [12, 41],
          popupAnchor: [1, -34],
          shadowSize: [41, 41]
        });

        // Add office marker to map
        L.marker([OFFICE_LOCATION.lat, OFFICE_LOCATION.lng], { icon: officeIcon })
          .addTo(mapInstance)
          .bindPopup(`<b>${OFFICE_LOCATION.name}</b><br>Starting point for distance calculations`)
          .openPopup();

        // Create feature group to store drawn shapes
        const drawnItemsLayer = new L.FeatureGroup();
        mapInstance.addLayer(drawnItemsLayer);

        // Add drawing controls to map
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

        // Handle when a shape is created
        mapInstance.on(L.Draw.Event.CREATED, (e) => {
          // Clear any previous shapes (only one shape at a time)
          drawnItemsLayer.clearLayers();
          const layer = e.layer;
          drawnItemsLayer.addLayer(layer);
          
          // Analyze the drawn land
          analyzeLand(layer, mapInstance);
        });

        // Handle when a shape is edited
        mapInstance.on(L.Draw.Event.EDITED, (e) => {
          const layers = e.layers;
          layers.eachLayer((layer) => {
            analyzeLand(layer, mapInstance);
          });
        });

        // Handle when a shape is deleted
        mapInstance.on(L.Draw.Event.DELETED, () => {
          setMeasurements(null);
        });

        setMap(mapInstance);
        setDrawnItems(drawnItemsLayer);
      };

      /**
       * Toggle between satellite and street view
       */
      const toggleSatellite = () => {
        if (!map || !satelliteLayerRef.current || !streetLayerRef.current) return;

        if (useSatellite) {
          // Switch to street view
          map.removeLayer(satelliteLayerRef.current);
          map.addLayer(streetLayerRef.current);
        } else {
          // Switch to satellite view
          map.removeLayer(streetLayerRef.current);
          map.addLayer(satelliteLayerRef.current);
        }
        setUseSatellite(!useSatellite);
      };

      /**
       * Search for locations using Photon API (CORS-friendly alternative)
       */
      const handleSearch = async () => {
        if (!searchQuery.trim() || !map) return;

        setIsSearching(true);
        setSearchResults([]);

        try {
          // Use Photon API which is CORS-friendly
          const response = await fetch(
            `https://photon.komoot.io/api/?q=${encodeURIComponent(searchQuery)}&limit=5&lang=en`,
            {
              headers: {
                'Accept': 'application/json'
              }
            }
          );

          if (!response.ok) throw new Error('Search failed');
          const data = await response.json();
          
          // Filter results for Lebanon or nearby
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
 * Navigate to selected search result and place a marker
 */
const selectSearchResult = (result) => {
  if (!map || !drawnItems) return;

  const [lon, lat] = result.geometry.coordinates;
  const L = window.L;
  
  // Clear any existing shapes
  drawnItems.clearLayers();
  
  // Create and add marker at the search result location
  const marker = L.marker([lat, lon]);
  drawnItems.addLayer(marker);
  
  // Center map on the location
  map.setView([lat, lon], 14);
  
  // Analyze the land at this location
  analyzeLand(marker, map);
  
  // Clear search results
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
       * Main analysis function - extracts data from drawn shape and calculates metrics
       */
      const analyzeLand = async (layer, mapInstance) => {
        setIsLoading(true);
        const L = window.L;
        
        try {
          let coordinates = [];
          let area = 0;
          let isMarker = false;
          let center;

          // Extract coordinates and calculate area based on shape type
          if (layer instanceof L.Polygon || layer instanceof L.Rectangle) {
            // For polygons/rectangles
            coordinates = layer.getLatLngs()[0].map(ll => [ll.lat, ll.lng]);
            area = L.GeometryUtil.geodesicArea(layer.getLatLngs()[0]); // Area in square meters
            const bounds = layer.getBounds();
            center = bounds.getCenter();
            isMarker = false;
          } else if (layer instanceof L.Marker) {
            // For single point markers
            const ll = layer.getLatLng();
            coordinates = [[ll.lat, ll.lng]];
            center = ll;
            area = 0;
            isMarker = true;
          }

          // Calculate straight-line distance from office to land center
          const distance = calculateDistance(
            OFFICE_LOCATION.lat, 
            OFFICE_LOCATION.lng, 
            center.lat, 
            center.lng
          );

          // Get elevation data from API
          const elevationData = await getElevationData(coordinates, isMarker);

          // Try to get driving route from office to land
          let route = null;
          try {
            route = await getRoute(OFFICE_LOCATION, center, mapInstance);
          } catch (e) {
            console.log('Route not available');
          }

          // Store all measurements
          setMeasurements({
            coordinates,
            area: (area / 10000).toFixed(2), // Convert to hectares
            areaM2: area.toFixed(2), // Keep in square meters
            center: [center.lat, center.lng],
            elevation: elevationData,
            distance: distance.toFixed(2), // in kilometers
            distanceMeters: (distance * 1000).toFixed(2), // in meters
            route,
            officeLocation: OFFICE_LOCATION,
            isMarker: isMarker
          });

          setIsLoading(false);

        } catch (error) {
          console.error('Error in analyzeLand:', error);
          setIsLoading(false);
          alert('An unexpected error occurred: ' + error.message);
        }
      };

      /**
       * Get elevation data from Open-Meteo API
       * This API provides global elevation data from satellite measurements
       */
      const getElevationData = async (coordinates, isMarker) => {
        // For polygons, sample up to 10 points for better accuracy
        let samplePoints = coordinates;
        if (!isMarker && coordinates.length > 10) {
          const step = Math.floor(coordinates.length / 10);
          samplePoints = coordinates.filter((_, index) => index % step === 0).slice(0, 10);
        }

        try {
          const elevations = [];
          
          // Fetch elevation for each sample point
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

          // Process the elevation data
          return processElevations(elevations, samplePoints, isMarker);

        } catch (error) {
          console.error('Elevation API failed:', error);
          throw error;
        }
      };

      /**
       * Process elevation data and calculate slope + terrain factor
       */
      const processElevations = (elevations, points, isMarker) => {
        // Calculate statistics
        const avgElevation = elevations.reduce((a, b) => a + b, 0) / elevations.length;
        const maxElevation = Math.max(...elevations);
        const minElevation = Math.min(...elevations);

        // For single markers, no slope calculation needed
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

        // For polygons, calculate slope
        const elevationDiff = maxElevation - minElevation;

        // Find the maximum distance between any two points in the polygon
        let maxDistance = 0;
        for (let i = 0; i < points.length; i++) {
          for (let j = i + 1; j < points.length; j++) {
            const dist = calculateDistance(
              points[i][0], points[i][1],
              points[j][0], points[j][1]
            ) * 1000; // Convert to meters
            maxDistance = Math.max(maxDistance, dist);
          }
        }

        // Calculate slope percentage: (rise / run) * 100
        const slope = maxDistance > 0 ? (elevationDiff / maxDistance) * 100 : 0;

        // Determine terrain difficulty factor based on slope
        let terrainFactor;
        if (slope < 5) {
          terrainFactor = 1.0; // Flat terrain
        } else if (slope < 15) {
          terrainFactor = 1.2; // Gentle slope
        } else if (slope < 30) {
          terrainFactor = 1.5; // Moderate slope
        } else {
          terrainFactor = 2.0; // Steep slope
        }

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
       * Get driving route from office to land using OSRM API
       * Displays the route on the map and returns distance/duration
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
            
            // Draw route on map
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
              distance: (route.distance / 1000).toFixed(2), // Convert to km
              duration: (route.duration / 60).toFixed(0) // Convert to minutes
            };
          }
          return null;
        } catch (error) {
          clearTimeout(timeoutId);
          throw error;
        }
      };

      /**
       * Calculate straight-line distance between two geographic points
       * Uses Haversine formula
       */
      const calculateDistance = (lat1, lon1, lat2, lon2) => {
        const R = 6371; // Earth's radius in kilometers
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a =
          Math.sin(dLat / 2) * Math.sin(dLat / 2) +
          Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
          Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c; // Distance in kilometers
      };

      /**
       * Convert degrees to radians
       */
      const toRad = (degrees) => degrees * (Math.PI / 180);

      /**
       * Clear all drawn shapes and measurements from the map
       */
      const clearAll = () => {
        if (drawnItems) drawnItems.clearLayers();
        setMeasurements(null);
        
        // Remove route lines from map
        if (map) {
          map.eachLayer((layer) => {
            if (layer instanceof window.L.Polyline && !(layer instanceof window.L.Polygon)) {
              map.removeLayer(layer);
            }
          });
        }
      };

      /**
       * Send measurement data back to parent window (form)
       * Used when this tool is opened as a popup
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
              // Send route distance (actual driving distance), fallback to straight-line if unavailable
              distance_from_office: measurements.route ? measurements.route.distance : measurements.distance
            }
          }, '*');
          window.close();
        }
      };

      // Render the UI
      return (
        <div style={{ display: 'flex', flexDirection: 'column', height: '100vh', background: '#f8f9fa' }}>
          {/* Header */}
          <div style={{ background: 'white', padding: '16px', borderBottom: '1px solid #dee2e6', boxShadow: '0 2px 4px rgba(0,0,0,0.1)' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
              <div>
                <h1 style={{ fontSize: '24px', fontWeight: 'bold', color: '#212529', display: 'flex', alignItems: 'center', gap: '8px', margin: 0 }}>
                  📍 Land Mapping Tool
                </h1>
                <p style={{ fontSize: '14px', color: '#6c757d', margin: '4px 0 0 0' }}>
                  Draw a polygon or place a marker to mark the land boundaries
                </p>
              </div>
              
              {/* Satellite toggle button */}
              <button 
                onClick={toggleSatellite}
                style={{ 
                  padding: '10px 16px', 
                  background: useSatellite ? '#0066cc' : '#f8f9fa',
                  color: useSatellite ? 'white' : '#212529',
                  border: '1px solid #dee2e6', 
                  borderRadius: '6px', 
                  cursor: 'pointer',
                  fontWeight: '600',
                  fontSize: '14px',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '6px',
                  transition: 'all 0.3s'
                }}
                onMouseOver={(e) => {
                  if (!useSatellite) {
                    e.target.style.background = '#e9ecef';
                  }
                }}
                onMouseOut={(e) => {
                  if (!useSatellite) {
                    e.target.style.background = '#f8f9fa';
                  }
                }}
              >
                🛰️ {useSatellite ? 'Satellite View' : 'Street View'}
              </button>
            </div>

            {/* Search bar */}
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
                style={{
                  padding: '10px 20px',
                  background: '#0066cc',
                  color: 'white',
                  border: 'none',
                  borderRadius: '6px',
                  cursor: isSearching || !searchQuery.trim() ? 'not-allowed' : 'pointer',
                  fontWeight: '600',
                  fontSize: '14px',
                  opacity: isSearching || !searchQuery.trim() ? 0.6 : 1,
                  transition: 'all 0.3s'
                }}
                onMouseOver={(e) => {
                  if (!isSearching && searchQuery.trim()) {
                    e.target.style.background = '#0052a3';
                  }
                }}
                onMouseOut={(e) => {
                  e.target.style.background = '#0066cc';
                }}
              >
                {isSearching ? '🔍 Searching...' : '🔍 Search'}
              </button>

              {/* Search results dropdown */}
              {searchResults.length > 0 && (
                <div style={{
                  position: 'absolute',
                  top: '100%',
                  left: 0,
                  right: '120px',
                  marginTop: '4px',
                  background: 'white',
                  border: '1px solid #dee2e6',
                  borderRadius: '6px',
                  boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
                  maxHeight: '300px',
                  overflowY: 'auto',
                  zIndex: 1000
                }}>
                  {searchResults.map((result, index) => (
                    <div
                      key={index}
                      onClick={() => selectSearchResult(result)}
                      style={{
                        padding: '12px 14px',
                        cursor: 'pointer',
                        borderBottom: index < searchResults.length - 1 ? '1px solid #f1f1f1' : 'none',
                        transition: 'background 0.2s'
                      }}
                      onMouseOver={(e) => e.currentTarget.style.background = '#f8f9fa'}
                      onMouseOut={(e) => e.currentTarget.style.background = 'white'}
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
          </div>

          {/* Main content area */}
          <div style={{ flex: 1, display: 'flex' }}>
            {/* Map container */}
            <div ref={mapRef} style={{ flex: 1, height: '100%' }} />

            {/* Sidebar */}
            <div style={{ width: '384px', background: 'white', boxShadow: '-2px 0 8px rgba(0,0,0,0.1)', display: 'flex', flexDirection: 'column' }}>
              {/* Sidebar header */}
              <div style={{ padding: '16px', borderBottom: '1px solid #e9ecef' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <h2 style={{ fontSize: '18px', fontWeight: '600', color: '#212529', margin: 0 }}>Land Analysis</h2>
                  {measurements && (
                    <button onClick={clearAll} style={{ padding: '8px', background: 'transparent', border: 'none', borderRadius: '4px', cursor: 'pointer', transition: 'background 0.3s' }}
                      onMouseOver={(e) => e.target.style.background = '#f8f9fa'}
                      onMouseOut={(e) => e.target.style.background = 'transparent'}>
                      ✕
                    </button>
                  )}
                </div>
              </div>

              {/* Sidebar content */}
              <div className="sidebar-content" style={{ flex: 1, padding: '16px', overflowY: 'auto' }}>
                {/* Empty state */}
                {!measurements && !isLoading && (
                  <div style={{ textAlign: 'center', padding: '32px 0', color: '#6c757d' }}>
                    <div style={{ fontSize: '48px', margin: '0 auto 16px', opacity: 0.5 }}>📍</div>
                    <p style={{ fontSize: '14px' }}>Use the drawing tools on the map to mark the land</p>
                    <p style={{ fontSize: '12px', marginTop: '8px' }}>The red marker shows our office location</p>
                  </div>
                )}

                {/* Loading state */}
                {isLoading && (
                  <div style={{ textAlign: 'center', padding: '32px 0' }}>
                    <div className="spinner" />
                    <p style={{ fontSize: '14px', color: '#6c757d', marginTop: '16px' }}>Analyzing land data...</p>
                    <p style={{ fontSize: '12px', color: '#6c757d', marginTop: '8px' }}>Fetching real elevation data from satellite...</p>
                  </div>
                )}

                {/* Measurement results */}
                {!isLoading && measurements && (
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                    {/* Area card */}
                    <div style={{ background: '#e7f3ff', padding: '12px', borderRadius: '8px' }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                        <span style={{ fontSize: '20px' }}>📏</span>
                        <h3 style={{ fontWeight: '600', color: '#212529', margin: 0 }}>Area</h3>
                      </div>
                      {measurements.isMarker ? (
                        <p style={{ fontSize: '16px', color: '#6c757d', margin: 0 }}>
                          No area calculated (single point marker)
                        </p>
                      ) : (
                        <>
                          <p style={{ fontSize: '24px', fontWeight: 'bold', color: '#0066cc', margin: '4px 0' }}>{measurements.area} hectares</p>
                          <p style={{ fontSize: '14px', color: '#6c757d', margin: 0 }}>{measurements.areaM2} m²</p>
                        </>
                      )}
                    </div>

                    {/* Coordinates card */}
                    <div style={{ background: '#f8f9fa', padding: '12px', borderRadius: '8px' }}>
                      <h3 style={{ fontWeight: '600', color: '#212529', marginBottom: '8px' }}>
                        {measurements.isMarker ? 'Point Coordinates' : 'Center Coordinates'}
                      </h3>
                      <div style={{ fontSize: '14px', display: 'flex', flexDirection: 'column', gap: '4px' }}>
                        <p style={{ margin: 0 }}><span style={{ fontWeight: '600' }}>Latitude:</span> {measurements.center[0].toFixed(6)}</p>
                        <p style={{ margin: 0 }}><span style={{ fontWeight: '600' }}>Longitude:</span> {measurements.center[1].toFixed(6)}</p>
                      </div>
                    </div>
                  </div>
                )}
              </div>

              {/* Sticky save button */}
              {!isLoading && measurements && (
                <div className="save-button-container">
                  <button onClick={saveToForm} style={{ width: '100%', background: '#0066cc', color: 'white', fontWeight: '600', padding: '12px 16px', borderRadius: '8px', border: 'none', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px', transition: 'background 0.3s' }}
                    onMouseOver={(e) => e.target.style.background = '#0052a3'}
                    onMouseOut={(e) => e.target.style.background = '#0066cc'}>
                    💾 Use This Data
                  </button>
                </div>
              )}
            </div>
          </div>

          {/* Footer instructions */}
          <div style={{ background: 'white', borderTop: '1px solid #dee2e6', padding: '12px 16px', fontSize: '14px', color: '#6c757d' }}>
            <p style={{ margin: 0 }}><strong>Instructions:</strong> The red marker shows our office location. Draw the land boundaries to calculate area, elevation, slope, and distance from office.</p>
          </div>
        </div>
      );
    };

    // Render the application
    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<LandMappingTool />);
  </script>
</body>

</html>