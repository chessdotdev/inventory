<?php
require_once 'config.php';

$messages = [];

// Run schema
$sql = file_get_contents(__DIR__ . '/database/schema.sql');
// Split and run each statement
$statements = array_filter(array_map('trim', explode(';', $sql)));
foreach ($statements as $stmt) {
    if (!empty($stmt)) {
        try { $pdo->exec($stmt); } catch (PDOException $e) { /* ignore duplicate errors */ }
    }
}
$messages[] = ['type' => 'success', 'text' => 'Database tables created.'];

// Create/update admin with proper hash
$hash = password_hash('Admin@1234', PASSWORD_BCRYPT);
$stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE password=?');
$stmt->execute(['admin', 'admin@inventory.com', $hash, 'admin', $hash]);
$messages[] = ['type' => 'success', 'text' => 'Admin user ready. Username: <strong>admin</strong> | Password: <strong>Admin@1234</strong>'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width:600px">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white"><h5 class="mb-0">System Setup</h5></div>
        <div class="card-body">
            <?php foreach ($messages as $m): ?>
            <div class="alert alert-<?= $m['type'] ?>"><?= $m['text'] ?></div>
            <?php endforeach; ?>
            <a href="login.php" class="btn btn-primary">Go to Login</a>
        </div>
    </div>
</div>
</body>
</html>
