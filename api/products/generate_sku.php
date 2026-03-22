<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin','manager']);

function generateProductSku(): string {
    return 'SKU-' . strtoupper(bin2hex(random_bytes(4)));
}

echo json_encode(['sku' => generateProductSku()]);
