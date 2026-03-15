<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireLogin();

$category = intval($_GET['category'] ?? 0);
$status   = $_GET['status'] ?? 'all';

$where  = 'WHERE p.is_active = 1';
$params = [];
if ($category) { $where .= ' AND p.category_id = ?'; $params[] = $category; }
if ($status === 'low') $where .= ' AND i.quantity > 0 AND i.quantity <= p.reorder_level';
if ($status === 'out') $where .= ' AND i.quantity = 0';
if ($status === 'ok')  $where .= ' AND i.quantity > p.reorder_level';

$stmt = $pdo->prepare(
    "SELECT p.sku, p.name, c.name AS category, p.unit, p.price,
            COALESCE(i.quantity,0) AS quantity,
            (COALESCE(i.quantity,0) * p.price) AS value,
            p.reorder_level, i.last_updated
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     LEFT JOIN inventory i ON i.product_id = p.id
     $where ORDER BY p.name"
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="stock_report_' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['SKU', 'Product', 'Category', 'Unit', 'Price', 'Quantity', 'Value', 'Reorder Level', 'Last Updated']);
foreach ($rows as $r) {
    fputcsv($out, [
        $r['sku'], $r['name'], $r['category'] ?? '', $r['unit'],
        $r['price'], $r['quantity'], $r['value'],
        $r['reorder_level'], $r['last_updated'] ?? ''
    ]);
}
fclose($out);
logAudit($pdo, 'EXPORT_STOCK_REPORT', 'reports', 'Exported stock report');
