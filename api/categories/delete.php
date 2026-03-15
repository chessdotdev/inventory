<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin','manager']);

$id = intval($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }

$pdo->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);
logAudit($pdo, 'DELETE_CATEGORY', 'categories', "Deleted category ID: $id");
echo json_encode(['success'=>true,'message'=>'Category deleted.']);
