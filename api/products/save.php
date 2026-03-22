<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin','manager','inventory_officer','staff']);

$id          = intval($_POST['id'] ?? 0);
$name        = trim($_POST['name'] ?? '');
$category_id = intval($_POST['category_id'] ?? 0) ?: null;
$unit        = trim($_POST['unit'] ?? 'pcs');
$price       = floatval($_POST['price'] ?? 0);
$reorder     = intval($_POST['reorder_level'] ?? 10);
$desc        = trim($_POST['description'] ?? '');

// Staff can only create, not edit
if ($id && $_SESSION['role'] === 'staff') {
    echo json_encode(['success'=>false,'message'=>'Access denied.']);
    exit;
}

if (!$name || $price < 0) {
    echo json_encode(['success'=>false,'message'=>'Name and valid price are required.']);
    exit;
}

function generateProductSku(int $id = 0): string {
    return 'SKU-' . strtoupper(bin2hex(random_bytes(4))) . ($id ? '-' . $id : '');
}

try {
    $u = currentUser();
    if ($id) {
        $sku = $pdo->query("SELECT sku FROM products WHERE id=$id")->fetchColumn();
        $pdo->prepare('UPDATE products SET name=?,category_id=?,unit=?,price=?,reorder_level=?,description=? WHERE id=?')
            ->execute([$name,$category_id,$unit,$price,$reorder,$desc,$id]);
        logAudit($pdo,'UPDATE_PRODUCT','products',"Updated: $name");
        echo json_encode(['success'=>true,'message'=>'Product updated.']);
    } else {
        // Generate SKU without ID first, update after insert
        $sku = generateProductSku();
        $pdo->prepare('INSERT INTO products (sku,name,category_id,unit,price,reorder_level,description,created_by) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([$sku,$name,$category_id,$unit,$price,$reorder,$desc,$u['id']]);
        $pid = $pdo->lastInsertId();
        // Update SKU to include the product ID
        $sku = generateProductSku($pid);
        $pdo->prepare('UPDATE products SET sku=? WHERE id=?')->execute([$sku, $pid]);
        // Initialize inventory row
        $pdo->prepare('INSERT INTO inventory (product_id, quantity) VALUES (?,0)')->execute([$pid]);
        logAudit($pdo,'CREATE_PRODUCT','products',"Created: $name (SKU: $sku)");
        echo json_encode(['success'=>true,'message'=>'Product created.']);
    }
} catch (PDOException $e) {
    $msg = str_contains($e->getMessage(),'Duplicate') ? 'SKU already exists.' : 'Database error.';
    echo json_encode(['success'=>false,'message'=>$msg]);
}
