<?php
require_once 'includes/auth.php';
startSecureSession();
if (isLoggedIn()) {
    header('Location: /inventory_system/dashboard.php');
} else {
    header('Location: /inventory_system/login.php');
}
exit;
