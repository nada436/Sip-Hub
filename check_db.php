<?php
$host = 'localhost';
$dbName = 'php_project';
$dbUser = 'root';
$dbPass = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully\n\n";
    
    // Check categories table structure
    $stmt = $pdo->query("DESCRIBE categories");
    echo "Categories table columns:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
    echo "\nSample data:\n";
    $stmt = $pdo->query("SELECT * FROM categories LIMIT 3");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "  No data found\n";
    } else {
        foreach ($rows as $row) {
            print_r($row);
        }
    }
    
    // Check products table structure
    echo "\n\nProducts table columns:\n";
    $stmt = $pdo->query("DESCRIBE products");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
