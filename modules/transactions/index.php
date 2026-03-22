<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin','manager','inventory_officer','staff']);

$pageTitle = 'Transactions';
$pageIcon  = 'exchange-alt';

$type  = $_GET['type'] ?? 'all';
$from  = $_GET['from'] ?? '';
$to    = $_GET['to'] ?? '';
$page  = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$where  = '1=1';
$params = [];
if ($type !== 'all') { $where .= ' AND t.type=?'; $params[] = $type; }
if ($from) { $where .= ' AND DATE(t.created_at) >= ?'; $params[] = $from; }
if ($to)   { $where .= ' AND DATE(t.created_at) <= ?'; $params[] = $to; }

$total = $pdo->prepare("SELECT COUNT(*) FROM transactions t WHERE $where");
$total->execute($params);
$totalRows = $total->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$stmt = $pdo->prepare(
    "SELECT t.*, p.name AS product_name, p.sku, u.username
     FROM transactions t
     JOIN products p ON p.id=t.product_id
     LEFT JOIN users u ON u.id=t.performed_by
     WHERE $where
     ORDER BY t.created_at DESC
     LIMIT $limit OFFSET $offset"
);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$badges = ['stock_in'=>'bg-success','stock_out'=>'bg-danger','adjustment'=>'bg-info'];
$labels = ['stock_in'=>'Stock In','stock_out'=>'Stock Out','adjustment'=>'Adjustment'];

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="type" class="form-select form-select-sm">
            <option value="all" <?= $type==='all'?'selected':'' ?>>All Types</option>
            <option value="stock_in"    <?= $type==='stock_in'?'selected':'' ?>>Stock In</option>
            <option value="stock_out"   <?= $type==='stock_out'?'selected':'' ?>>Stock Out</option>
            <option value="adjustment"  <?= $type==='adjustment'?'selected':'' ?>>Adjustment</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>" placeholder="From">
    </div>
    <div class="col-md-3">
        <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>" placeholder="To">
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-sm btn-primary">Filter</button>
        <a href="?" class="btn btn-sm btn-secondary">Reset</a>
    </div>
</form>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Transaction History</h5>
        <span class="text-muted small"><?= number_format($totalRows) ?> records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Reference</th><th>Type</th><th>Product</th><th>SKU</th><th>Qty</th><th>Unit Price</th><th>Total</th><th>Notes</th><th>By</th><th>Date</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($transactions as $tx): ?>
                <tr>
                    <td><code><?= htmlspecialchars($tx['reference_no']) ?></code></td>
                    <td><span class="badge <?= $badges[$tx['type']] ?>"><?= $labels[$tx['type']] ?></span></td>
                    <td><?= htmlspecialchars($tx['product_name']) ?></td>
                    <td><?= htmlspecialchars($tx['sku']) ?></td>
                    <td><?= number_format($tx['quantity']) ?></td>
                    <td>₱<?= number_format($tx['unit_price'],2) ?></td>
                    <td>₱<?= number_format($tx['quantity'] * $tx['unit_price'],2) ?></td>
                    <td><?= htmlspecialchars($tx['notes'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($tx['username'] ?? '-') ?></td>
                    <td><?= date('M d, Y H:i', strtotime($tx['created_at'])) ?></td>
                    <td>
                        <a href="/inventory_system/modules/transactions/view.php?id=<?= $tx['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$transactions): ?>
                <tr><td colspan="10" class="text-center text-muted py-3">No transactions found.</td></tr>
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
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="?type=<?= $type ?>&from=<?= $from ?>&to=<?= $to ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/layout_bottom.php'; ?>
