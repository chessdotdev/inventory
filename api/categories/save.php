<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin','manager']);

$id   = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$desc = trim($_POST['description'] ?? '');

if (!$name) { echo json_encode(['success'=>false,'message'=>'Name is required.']); exit; }

try {
    if ($id) {
        $pdo->prepare('UPDATE categories SET name=?, description=? WHERE id=?')->execute([$name, $desc, $id]);
        logAudit($pdo, 'UPDATE_CATEGORY', 'categories', "Updated: $name");
        echo json_encode(['success'=>true,'message'=>'Category updated.']);
    } else {
        $pdo->prepare('INSERT INTO categories (name, description) VALUES (?,?)')->execute([$name, $desc]);
        logAudit($pdo, 'CREATE_CATEGORY', 'categories', "Created: $name");
        echo json_encode(['success'=>true,'message'=>'Category created.']);
    }
} catch (PDOException $e) {
    $msg = str_contains($e->getMessage(), 'Duplicate') ? 'Category name already exists.' : 'Database error.';
    echo json_encode(['success'=>false,'message'=>$msg]);
}
