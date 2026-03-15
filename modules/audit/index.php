<?php
$pageTitle = 'Audit Logs';
$pageIcon  = 'shield-alt';
require_once __DIR__ . '/../../includes/layout_top.php';
requireRole(['admin']);

$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;
$module = $_GET['module'] ?? '';
$search = trim($_GET['search'] ?? '');

$where  = '1=1';
$params = [];
if ($module) { $where .= ' AND module=?'; $params[] = $module; }
if ($search) { $where .= ' AND (username LIKE ? OR action LIKE ? OR description LIKE ?)'; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }

$total = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE $where");
$total->execute($params);
$totalRows  = $total->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$stmt = $pdo->prepare("SELECT * FROM audit_logs WHERE $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$logs = $stmt->fetchAll();

$modules = $pdo->query('SELECT DISTINCT module FROM audit_logs ORDER BY module')->fetchAll(PDO::FETCH_COLUMN);
?>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="module" class="form-select form-select-sm">
            <option value="">All Modules</option>
            <?php foreach ($modules as $m): ?>
            <option value="<?= htmlspecialchars($m) ?>" <?= $module===$m?'selected':'' ?>><?= ucfirst($m) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-5">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search user, action, description..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-sm btn-primary">Filter</button>
        <a href="?" class="btn btn-sm btn-secondary">Reset</a>
    </div>
</form>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Activity Log</h5>
        <span class="text-muted small"><?= number_format($totalRows) ?> entries</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead><tr><th>Date/Time</th><th>User</th><th>Module</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
                <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="text-nowrap"><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></td>
                    <td><?= htmlspecialchars($log['username'] ?? '-') ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($log['module']) ?></span></td>
                    <td><code><?= htmlspecialchars($log['action']) ?></code></td>
                    <td><?= htmlspecialchars($log['description'] ?? '') ?></td>
                    <td><?= htmlspecialchars($log['ip_address'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$logs): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">No logs found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?></small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="?module=<?= urlencode($module) ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/layout_bottom.php'; ?>
