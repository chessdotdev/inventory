<?php
require_once 'config.php';
require_once 'includes/auth.php';
startSecureSession();
if (isLoggedIn()) {
    logAudit($pdo, 'LOGOUT', 'auth', 'User logged out');
}
session_destroy();
header('Location: /inventory_system/login.php');
exit;
