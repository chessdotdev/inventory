<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireLogin();

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

$stmt = $pdo->prepare(
    'SELECT t.reference_no, t.type, p.sku, p.name AS product, t.quantity, t.unit_price,
            (t.quantity * t.unit_price) AS total, t.notes, u.username, t.created_at
     FROM transactions t
     JOIN products p ON p.id=t.product_id
     LEFT JOIN users u ON u.id=t.performed_by
     WHERE DATE(t.created_at) BETWEEN ? AND ?
     ORDER BY t.created_at DESC'
);
$stmt->execute([$from, $to]);
$rows = $stmt->fetchAll();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="transactions_' . $from . '_to_' . $to . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Reference', 'Type', 'SKU', 'Product', 'Quantity', 'Unit Price', 'Total', 'Notes', 'By', 'Date']);
foreach ($rows as $r) {
    fputcsv($out, [
        $r['reference_no'], $r['type'], $r['sku'], $r['product'],
        $r['quantity'], $r['unit_price'], $r['total'],
        $r['notes'], $r['username'], $r['created_at']
    ]);
}
fclose($out);
logAudit($pdo, 'EXPORT_REPORT', 'reports', "Exported transactions $from to $to");
