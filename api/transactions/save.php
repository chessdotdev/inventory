<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin','manager','inventory_officer']);

$product_id = intval($_POST['product_id'] ?? 0);
$type       = $_POST['type'] ?? '';
$quantity   = intval($_POST['quantity'] ?? 0);
$unit_price = floatval($_POST['unit_price'] ?? 0);
$notes      = trim($_POST['notes'] ?? '');

if (!$product_id || !in_array($type, ['stock_in','stock_out','adjustment']) || $quantity <= 0) {
    echo json_encode(['success'=>false,'message'=>'Invalid transaction data.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Get current stock
    $inv = $pdo->prepare('SELECT quantity FROM inventory WHERE product_id=? FOR UPDATE');
    $inv->execute([$product_id]);
    $row = $inv->fetch();

    if (!$row) {
        // Create inventory row if missing
        $pdo->prepare('INSERT INTO inventory (product_id, quantity) VALUES (?,0)')->execute([$product_id]);
        $currentQty = 0;
    } else {
        $currentQty = $row['quantity'];
    }

    // Calculate new quantity
    if ($type === 'stock_in') {
        $newQty = $currentQty + $quantity;
    } elseif ($type === 'stock_out') {
        if ($currentQty < $quantity) {
            $pdo->rollBack();
            echo json_encode(['success'=>false,'message'=>"Insufficient stock. Available: $currentQty"]);
            exit;
        }
        $newQty = $currentQty - $quantity;
    } else {
        // adjustment: set absolute value
        $newQty = $quantity;
    }

    // Update inventory
    $pdo->prepare('UPDATE inventory SET quantity=? WHERE product_id=?')->execute([$newQty, $product_id]);

    // Record transaction
    $ref = generateReference(strtoupper(substr($type,0,3)));
    $u   = currentUser();
    $pdo->prepare(
        'INSERT INTO transactions (reference_no, type, product_id, quantity, unit_price, notes, performed_by)
         VALUES (?,?,?,?,?,?,?)'
    )->execute([$ref, $type, $product_id, $quantity, $unit_price, $notes, $u['id']]);

    logAudit($pdo, strtoupper($type), 'inventory', "Product ID $product_id | Qty: $quantity | Ref: $ref");

    $pdo->commit();
    echo json_encode(['success'=>true,'message'=>"Transaction recorded. Ref: $ref"]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false,'message'=>'Database error.']);
}
