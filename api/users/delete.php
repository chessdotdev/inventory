<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin']);

$id = intval($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }
if ($id === intval($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Cannot delete your own account.']); exit; }

$stmt = $pdo->prepare('DELETE FROM users WHERE id=?');
$stmt->execute([$id]);
logAudit($pdo, 'DELETE_USER', 'users', "Deleted user ID: $id");
echo json_encode(['success'=>true,'message'=>'User deleted.']);
