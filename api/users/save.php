<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin']);

$id       = intval($_POST['id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$role     = $_POST['role'] ?? 'staff';
$active   = intval($_POST['is_active'] ?? 1);
$password = $_POST['password'] ?? '';

if (!$username || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid username and email are required.']);
    exit;
}
if (!in_array($role, ['admin','manager','inventory_officer','staff'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid role.']);
    exit;
}

try {
    if ($id) {
        // Update
        if ($password) {
            if (strlen($password) < 8) { echo json_encode(['success'=>false,'message'=>'Password must be at least 8 characters.']); exit; }
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET username=?, email=?, password=?, role=?, is_active=? WHERE id=?');
            $stmt->execute([$username, $email, $hash, $role, $active, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET username=?, email=?, role=?, is_active=? WHERE id=?');
            $stmt->execute([$username, $email, $role, $active, $id]);
        }
        logAudit($pdo, 'UPDATE_USER', 'users', "Updated user: $username");
        echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
    } else {
        // Create
        if (strlen($password) < 8) { echo json_encode(['success'=>false,'message'=>'Password must be at least 8 characters.']); exit; }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role, is_active) VALUES (?,?,?,?,?)');
        $stmt->execute([$username, $email, $hash, $role, $active]);
        logAudit($pdo, 'CREATE_USER', 'users', "Created user: $username");
        echo json_encode(['success' => true, 'message' => 'User created successfully.']);
    }
} catch (PDOException $e) {
    $msg = str_contains($e->getMessage(), 'Duplicate') ? 'Username or email already exists.' : 'Database error.';
    echo json_encode(['success' => false, 'message' => $msg]);
}
