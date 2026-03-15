<?php
$pageTitle = 'My Profile';
$pageIcon  = 'user-circle';
require_once __DIR__ . '/../../includes/layout_top.php';

$uid  = $user['id'];
$me   = $pdo->prepare('SELECT id, username, email, role, is_active, created_at FROM users WHERE id = ?');
$me->execute([$uid]);
$me   = $me->fetch();

// Own recent activity
$activity = $pdo->prepare(
    'SELECT action, module, description, ip_address, created_at
     FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 15'
);
$activity->execute([$uid]);
$logs = $activity->fetchAll();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current  = $_POST['current_password'] ?? '';
    $new      = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $dbUser = $pdo->prepare('SELECT password FROM users WHERE id = ?');
    $dbUser->execute([$uid]);
    $dbUser = $dbUser->fetch();

    if (!password_verify($current, $dbUser['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $uid]);
        logAudit($pdo, 'CHANGE_PASSWORD', 'users', 'User changed their own password');
        $success = 'Password changed successfully.';
    }
}

$roleColors = ['admin' => 'danger', 'manager' => 'warning', 'staff' => 'secondary'];
?>

<div class="row g-3">
    <!-- Profile Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5><i class="fas fa-id-card me-2 text-primary"></i>Account Info</h5></div>
            <div class="card-body text-center">
                <div class="user-avatar mx-auto mb-3" style="width:64px;height:64px;font-size:26px;">
                    <?= strtoupper(substr($me['username'], 0, 1)) ?>
                </div>
                <h5 class="mb-1"><?= htmlspecialchars($me['username']) ?></h5>
                <p class="text-muted small mb-2"><?= htmlspecialchars($me['email']) ?></p>
                <span class="badge bg-<?= $roleColors[$me['role']] ?> mb-3"><?= ucfirst($me['role']) ?></span>
                <hr>
                <div class="text-start">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted small">Status</span>
                        <span class="badge <?= $me['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $me['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted small">Member Since</span>
                        <span class="small"><?= date('M d, Y', strtotime($me['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-header"><h5><i class="fas fa-lock me-2 text-warning"></i>Change Password</h5></div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                <div class="alert alert-success py-2 small"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                        <div class="form-text">Minimum 8 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-key me-2"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Activity Log -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>My Recent Activity</h5>
                <span class="badge bg-secondary"><?= count($logs) ?> records</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Date/Time</th><th>Module</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
                        <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-nowrap"><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($log['module']) ?></span></td>
                            <td><code><?= htmlspecialchars($log['action']) ?></code></td>
                            <td><?= htmlspecialchars($log['description'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$logs): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No activity recorded yet.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/layout_bottom.php'; ?>
