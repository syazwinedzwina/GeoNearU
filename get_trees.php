<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Add CORS header
header("Access-Control-Allow-Methods: GET, POST"); 

try {
    $pdo = new PDO("mysql:host=localhost;dbname=carbon_tree;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get parameters
$lat = $_GET['lat'] ?? null;
$lng = $_GET['lng'] ?? null;
$radius = $_GET['radius'] ?? 2;
$species = $_GET['species'] ?? '';

if (!$lat || !$lng) {
    echo json_encode(['error' => 'Missing location']);
    exit;
}

// Build query
$query = "
    SELECT 
        `Tree ID` AS id,
        `Scientific Name` AS species,
        `DBH age of tree (cm)` AS dbh_cm,
        `Height (m)` AS height_m,
        AGB AS biomass_kg,
        `Carbon Storage` AS carbon_stock_kg,
        Latitude,
        Longitude
    FROM carbon_stock__all_trees_combine_
    WHERE Latitude IS NOT NULL AND Longitude IS NOT NULL";

if ($species) {
    $query .= " AND `Scientific Name` = :species";
}

$stmt = $pdo->prepare($query);
if ($species) {
    $stmt->bindParam(':species', $species);
}
$stmt->execute();

$trees = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $distance = calculateDistance($lat, $lng, $row['Latitude'], $row['Longitude']);
    if ($distance <= $radius) {
        $trees[] = [
            'id' => $row['id'],
            'species' => $row['species'],
            'dbh_cm' => (float)$row['dbh_cm'],
            'height_m' => (float)$row['height_m'],
            'biomass_kg' => (float)$row['biomass_kg'],
            'carbon_stock_kg' => (float)$row['carbon_stock_kg'],
            'coordinates' => ['lat' => (float)$row['Latitude'], 'lng' => (float)$row['Longitude']],
            'distance' => $distance
        ];
    }
}

echo json_encode($trees);

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}