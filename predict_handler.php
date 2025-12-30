<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get form data
        $latitude = floatval($_POST['latitude']);
        $longitude = floatval($_POST['longitude']);
        $area = floatval($_POST['area']);
        $distance = floatval($_POST['distance']);
        $roadAccess = $_POST['road_access'];
        $roadType = $_POST['road_type'];
        $electricity = $_POST['electricity'];
        $water = $_POST['water'];
        $propertyType = $_POST['property_type'];
        $zoning = $_POST['zoning'];
        $schoolRating = intval($_POST['school_rating']);
        $income = floatval($_POST['income']);
        $populationDensity = $_POST['population_density'];
        $topography = $_POST['topography'];
        
        // Prepare data for Python script (matching your dataset columns exactly)
        $inputData = [
            'Latitude' => $latitude,
            'Longitude' => $longitude,
            'Area (sqm)' => $area,
            'Distance To Town Center (km)' => $distance,
            'Road Access' => $roadAccess,
            'Road Type' => $roadType,
            'Electricity' => $electricity,
            'Water' => $water,
            'Property Type' => $propertyType,
            'Zoning' => $zoning,
            'School District Rating' => $schoolRating,
            'Median Household Income' => $income,
            'Population Density' => $populationDensity,
            'Topography' => $topography
        ];
        
        // Write data to temporary file (avoids Windows quote escaping issues)
        $tempFile = __DIR__ . '/temp_input_' . uniqid() . '.json';
        file_put_contents($tempFile, json_encode($inputData));
        
        // Call Python script with file path instead of JSON string
        $pythonPath = 'python'; // Change to 'python3' on Linux/Mac
        $scriptPath = __DIR__ . '/predict_from_file.py';
        $command = "$pythonPath \"$scriptPath\" \"$tempFile\" 2>&1";
        
        $output = shell_exec($command);
        
        // Clean up temp file
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        
        // Parse Python output
        $result = json_decode($output, true);
        
        if ($result && isset($result['predicted_price'])) {
            echo json_encode([
                'success' => true,
                'predicted_price' => $result['predicted_price'],
                'price_per_sqm' => $result['predicted_price'] / $area
            ]);
        } else {
            throw new Exception('Invalid prediction result. Python output: ' . $output);
        }
        
    } catch (Exception $e) {
        // Clean up temp file if error occurs
        if (isset($tempFile) && file_exists($tempFile)) {
            unlink($tempFile);
        }
        
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}
?>