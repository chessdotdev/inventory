<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin','manager']);

$id          = intval($_POST['id'] ?? 0);
$sku         = trim($_POST['sku'] ?? '');
$name        = trim($_POST['name'] ?? '');
$category_id = intval($_POST['category_id'] ?? 0) ?: null;
$unit        = trim($_POST['unit'] ?? 'pcs');
$price       = floatval($_POST['price'] ?? 0);
$reorder     = intval($_POST['reorder_level'] ?? 10);
$desc        = trim($_POST['description'] ?? '');

if (!$sku || !$name || $price < 0) {
    echo json_encode(['success'=>false,'message'=>'SKU, name, and valid price are required.']);
    exit;
}

try {
    $u = currentUser();
    if ($id) {
        $pdo->prepare('UPDATE products SET sku=?,name=?,category_id=?,unit=?,price=?,reorder_level=?,description=? WHERE id=?')
            ->execute([$sku,$name,$category_id,$unit,$price,$reorder,$desc,$id]);
        logAudit($pdo,'UPDATE_PRODUCT','products',"Updated: $name");
        echo json_encode(['success'=>true,'message'=>'Product updated.']);
    } else {
        $pdo->prepare('INSERT INTO products (sku,name,category_id,unit,price,reorder_level,description,created_by) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([$sku,$name,$category_id,$unit,$price,$reorder,$desc,$u['id']]);
        $pid = $pdo->lastInsertId();
        // Initialize inventory row
        $pdo->prepare('INSERT INTO inventory (product_id, quantity) VALUES (?,0)')->execute([$pid]);
        logAudit($pdo,'CREATE_PRODUCT','products',"Created: $name (SKU: $sku)");
        echo json_encode(['success'=>true,'message'=>'Product created.']);
    }
} catch (PDOException $e) {
    $msg = str_contains($e->getMessage(),'Duplicate') ? 'SKU already exists.' : 'Database error.';
    echo json_encode(['success'=>false,'message'=>$msg]);
}
