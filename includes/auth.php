<?php
require_once __DIR__ . '/../config.php';

function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    startSecureSession();
    return isset($_SESSION['user_id'], $_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /inventory_system/login.php');
        exit;
    }
}

function requireRole(array $roles) {
    requireLogin();
    if (!in_array($_SESSION['role'] ?? '', $roles)) {
        http_response_code(403);
        die('Access Denied.');
    }
}

function currentUser(): array {
    return [
        'id'       => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? '',
        'role'     => $_SESSION['role'] ?? '',
    ];
}

function logAudit(PDO $pdo, string $action, string $module, string $description = '') {
    $user = currentUser();
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $pdo->prepare(
        'INSERT INTO audit_logs (user_id, username, action, module, description, ip_address)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$user['id'], $user['username'], $action, $module, $description, $ip]);
}

function generateReference(string $prefix = 'TXN'): string {
    return $prefix . '-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');
}
