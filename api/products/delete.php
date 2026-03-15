<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin','manager']);

$id = intval($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }

$pdo->prepare('UPDATE products SET is_active=0 WHERE id=?')->execute([$id]);
logAudit($pdo,'DELETE_PRODUCT','products',"Soft-deleted product ID: $id");
echo json_encode(['success'=>true,'message'=>'Product removed.']);
