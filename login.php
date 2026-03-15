<?php
require_once 'config.php';
require_once 'includes/auth.php';
startSecureSession();

if (isLoggedIn()) {
    header('Location: /inventory_system/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare('SELECT id, username, password, role, is_active FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $user['is_active'] && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];  
            $_SESSION['logged_in'] = true;
            logAudit($pdo, 'LOGIN', 'auth', 'User logged in');
            header('Location: /inventory_system/dashboard.php');
            exit;
        }
        $error = 'Invalid username or password.';
    } else {
        $error = 'All fields are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background:#f0f2f5; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .login-box { background:#fff; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,.1); padding:40px; width:100%; max-width:400px; }
        .login-box h2 { font-size:22px; font-weight:700; color:#2c3e50; margin-bottom:6px; }
        .toggle-pw { cursor:pointer; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="text-center mb-4">
        <img src="/inventory_system/assets/image/logo.jpg" alt="Logo" style="width:64px;height:64px;object-fit:contain;border-radius:10px;margin-bottom:10px;">
        <h2><?= APP_NAME ?></h2>
        <p class="text-muted small">Sign in to your account</p>
    </div>
    <?php if ($error): ?>
    <div class="alert alert-danger py-2"><i class="fas fa-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-semibold">Username</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Password</label>
            <div class="input-group">
                <input type="password" name="password" id="pwField" class="form-control" required>
                <span class="input-group-text toggle-pw" onclick="togglePw()"><i class="fas fa-eye" id="pwIcon"></i></span>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>
<script>
function togglePw() {
    const f = document.getElementById('pwField');
    const i = document.getElementById('pwIcon');
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
</body>
</html>
