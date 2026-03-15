<?php
header('Content-Type: application/json');
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $price = $_POST['price'] ?? '';

    if (empty($name) || empty($quantity) || empty($price)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO items (name,category, quantity, price) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $category, (int)$quantity, (float)$price]);
        
        echo json_encode(['success' => true, 'message' => 'Item added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>