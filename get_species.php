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

$stmt = $pdo->query("
    SELECT DISTINCT `Scientific Name` AS species
    FROM carbon_stock__all_trees_combine_
    WHERE `Scientific Name` IS NOT NULL
");

$speciesList = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['species']) {
        $speciesList[] = $row['species'];
    }
}

echo json_encode($speciesList);