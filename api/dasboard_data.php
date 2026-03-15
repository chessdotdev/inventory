<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
startSecureSession();
requireLogin();

try {
    $totalProducts = $pdo->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn();
    $totalValue    = $pdo->query('SELECT COALESCE(SUM(i.quantity*p.price),0) FROM inventory i JOIN products p ON p.id=i.product_id')->fetchColumn();
    $lowStock      = $pdo->query('SELECT COUNT(*) FROM inventory i JOIN products p ON p.id=i.product_id WHERE i.quantity<=p.reorder_level AND i.quantity>0')->fetchColumn();
    $outOfStock    = $pdo->query('SELECT COUNT(*) FROM inventory i WHERE i.quantity=0')->fetchColumn();

    $recentItems = $pdo->query(
        'SELECT p.name, p.sku, c.name AS category, i.quantity, p.price, p.created_at
         FROM products p LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN inventory i ON i.product_id=p.id
         WHERE p.is_active=1 ORDER BY p.created_at DESC LIMIT 5'
    )->fetchAll();

    $categories = $pdo->query(
        'SELECT c.name AS category, COALESCE(SUM(i.quantity),0) AS count
         FROM categories c LEFT JOIN products p ON p.category_id=c.id LEFT JOIN inventory i ON i.product_id=p.id
         GROUP BY c.id, c.name'
    )->fetchAll();

    echo json_encode([
        'success'      => true,
        'totalItems'   => $totalProducts,
        'totalQuantity'=> $pdo->query('SELECT COALESCE(SUM(quantity),0) FROM inventory')->fetchColumn(),
        'totalValue'   => $totalValue,
        'lowStock'     => $lowStock,
        'outOfStock'   => $outOfStock,
        'recentItems'  => $recentItems,
        'categories'   => $categories,
    ]);
} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Database error.']);
}
