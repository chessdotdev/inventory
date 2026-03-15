<?php
// ── Data & auth BEFORE any output ──────────────────────────
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin','manager']);

$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 20;
$offset = ($page - 1) * $limit;

$totalRows  = $pdo->query("SELECT COUNT(*) FROM transactions WHERE type='adjustment'")->fetchColumn();
$totalPages = (int) ceil($totalRows / $limit);

$adjStmt = $pdo->prepare(
    "SELECT t.*, p.name AS product_name, p.sku, u.username
     FROM transactions t
     JOIN products p ON p.id = t.product_id
     LEFT JOIN users u ON u.id = t.performed_by
     WHERE t.type = 'adjustment'
     ORDER BY t.created_at DESC
     LIMIT $limit OFFSET $offset"
);
$adjStmt->execute();
$rows = $adjStmt->fetchAll();

$products = $pdo->query(
    'SELECT p.id, p.name, p.sku, p.price, COALESCE(i.quantity,0) AS stock
     FROM products p
     LEFT JOIN inventory i ON i.product_id = p.id
     WHERE p.is_active = 1
     ORDER BY p.name'
)->fetchAll();

// ── Layout (outputs HTML from here) ────────────────────────
$pageTitle = 'Stock Adjustment';
$pageIcon  = 'sliders-h';
require_once __DIR__ . '/../../includes/layout_top.php';
?>

<div class="row g-3">

    <!-- Adjustment Form -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-sliders-h me-2 text-warning"></i>New Adjustment</h5>
            </div>
            <div class="card-body">
                <div id="adjAlert"></div>
                <form id="adjForm">
                    <input type="hidden" name="type" value="adjustment">
                    <input type="hidden" name="unit_price" id="adjUnitPrice" value="0">

                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <select name="product_id" id="adjProduct" class="form-select" required>
                            <option value="">— Select Product —</option>
                            <?php foreach ($products as $pr): ?>
                            <option value="<?= $pr['id'] ?>" data-stock="<?= $pr['stock'] ?>" data-price="<?= $pr['price'] ?>">
                                <?= htmlspecialchars($pr['name']) ?>
                                (<?= htmlspecialchars($pr['sku']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" id="currentStock" class="form-control"
                               readonly placeholder="Select a product first">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            New Quantity
                            <small class="text-muted">(sets absolute value)</small>
                        </label>
                        <input type="number" name="quantity" class="form-control"
                               min="0" required placeholder="Enter new stock quantity">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason / Notes</label>
                        <textarea name="notes" class="form-control" rows="3" required
                                  placeholder="e.g. Physical count correction, damaged goods..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-semibold">
                        <i class="fas fa-check me-2"></i>Apply Adjustment
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Adjustment History -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Adjustment History</h5>
                <span class="badge bg-secondary"><?= number_format($totalRows) ?> records</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Reference</th><th>Product</th>
                                <th>New Qty</th><th>Reason</th><th>By</th><th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($r['reference_no']) ?></code></td>
                            <td>
                                <?= htmlspecialchars($r['product_name']) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($r['sku']) ?></small>
                            </td>
                            <td><span class="badge bg-info"><?= number_format($r['quantity']) ?></span></td>
                            <td><?= htmlspecialchars($r['notes'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($r['username'] ?? '—') ?></td>
                            <td><?= date('M d, Y H:i', strtotime($r['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$rows): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No adjustments recorded yet.
                            </td>
                        </tr>
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
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
$extraScripts = <<<JS
<script>
document.getElementById('adjProduct').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    document.getElementById('currentStock').value = opt.value ? opt.dataset.stock + ' units' : '';
    document.getElementById('adjUnitPrice').value  = opt.value ? opt.dataset.price : '0';
});

document.getElementById('adjForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = this.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Applying...';

    const res  = await fetch('/inventory_system/api/transactions/save.php', { method:'POST', body: new FormData(this) });
    const data = await res.json();

    document.getElementById('adjAlert').innerHTML =
        '<div class="alert alert-' + (data.success ? 'success' : 'danger') + ' alert-dismissible">' +
        '<i class="fas fa-' + (data.success ? 'check' : 'exclamation') + '-circle me-1"></i>' +
        data.message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';

    if (data.success) {
        this.reset();
        document.getElementById('currentStock').value = '';
        setTimeout(function () { location.reload(); }, 1000);
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check me-2"></i>Apply Adjustment';
});
</script>
JS;
require_once __DIR__ . '/../../includes/layout_bottom.php';
