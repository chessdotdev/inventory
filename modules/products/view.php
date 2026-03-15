<?php
// ── Data & auth BEFORE any output ──────────────────────────
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin','manager']);

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /inventory_system/modules/products/index.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name,
            COALESCE(i.quantity, 0) AS stock,
            i.last_updated,
            u.username AS created_by_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     LEFT JOIN inventory i ON i.product_id = p.id
     LEFT JOIN users u ON u.id = p.created_by
     WHERE p.id = ? AND p.is_active = 1'
);
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    header('Location: /inventory_system/modules/products/index.php');
    exit;
}

$histStmt = $pdo->prepare(
    'SELECT t.id, t.reference_no, t.type, t.quantity, t.unit_price, t.notes, t.created_at,
            u.username
     FROM transactions t
     LEFT JOIN users u ON u.id = t.performed_by
     WHERE t.product_id = ?
     ORDER BY t.created_at DESC
     LIMIT 20'
);
$histStmt->execute([$id]);
$txHistory = $histStmt->fetchAll();

$stockClass = $p['stock'] == 0 ? 'danger' : ($p['stock'] <= $p['reorder_level'] ? 'warning' : 'success');
$stockLabel = $p['stock'] == 0 ? 'Out of Stock' : ($p['stock'] <= $p['reorder_level'] ? 'Low Stock' : 'In Stock');
$badges     = ['stock_in' => 'bg-success', 'stock_out' => 'bg-danger', 'adjustment' => 'bg-info'];
$txLabels   = ['stock_in' => 'Stock In',   'stock_out' => 'Stock Out', 'adjustment' => 'Adjustment'];

//Layout (outputs HTML from here) 
$pageTitle = 'Product — ' . htmlspecialchars($p['name']);
$pageIcon  = 'box';
require_once __DIR__ . '/../../includes/layout_top.php';
?>

<div class="mb-3">
    <a href="/inventory_system/modules/products/index.php" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Products
    </a>
</div>

<div class="row g-3 mb-4">

    <!-- Product Info -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="fas fa-box me-2 text-primary"></i>Product Info</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0 px-2">
                    <tr><th class="text-muted ps-3" style="width:40%">SKU</th>
                        <td><code><?= htmlspecialchars($p['sku']) ?></code></td></tr>
                    <tr><th class="text-muted ps-3">Name</th>
                        <td><?= htmlspecialchars($p['name']) ?></td></tr>
                    <tr><th class="text-muted ps-3">Category</th>
                        <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td></tr>
                    <tr><th class="text-muted ps-3">Unit</th>
                        <td><?= htmlspecialchars($p['unit']) ?></td></tr>
                    <tr><th class="text-muted ps-3">Price</th>
                        <td><strong>₱<?= number_format($p['price'], 2) ?></strong></td></tr>
                    <tr><th class="text-muted ps-3">Reorder At</th>
                        <td><?= $p['reorder_level'] ?> units</td></tr>
                    <tr><th class="text-muted ps-3">Created By</th>
                        <td><?= htmlspecialchars($p['created_by_name'] ?? '—') ?></td></tr>
                    <?php if ($p['description']): ?>
                    <tr><th class="text-muted ps-3">Description</th>
                        <td><?= htmlspecialchars($p['description']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Stock Status -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="fas fa-warehouse me-2 text-primary"></i>Stock Status</h5>
            </div>
            <div class="card-body text-center d-flex flex-column justify-content-center gap-3">
                <div>
                    <div class="info-card-label">Current Stock</div>
                    <div style="font-size:52px;font-weight:700;color:var(--bs-<?= $stockClass ?>);">
                        <?= number_format($p['stock']) ?>
                    </div>
                    <span class="badge bg-<?= $stockClass ?> fs-6"><?= $stockLabel ?></span>
                </div>
                <hr class="my-1">
                <div class="d-flex justify-content-around">
                    <div>
                        <div class="info-card-label">Stock Value</div>
                        <strong>₱<?= number_format($p['stock'] * $p['price'], 2) ?></strong>
                    </div>
                    <div>
                        <div class="info-card-label">Last Updated</div>
                        <strong><?= $p['last_updated'] ? date('M d, Y', strtotime($p['last_updated'])) : '—' ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="fas fa-bolt me-2 text-primary"></i>Quick Actions</h5>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <button class="btn btn-success"
                        onclick="openTx(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>', 'stock_in')">
                    <i class="fas fa-plus-circle me-2"></i>Stock In
                </button>
                <button class="btn btn-danger"
                        onclick="openTx(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>', 'stock_out')">
                    <i class="fas fa-minus-circle me-2"></i>Stock Out
                </button>
                <button class="btn btn-warning"
                        onclick="openTx(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>', 'adjustment')">
                    <i class="fas fa-sliders-h me-2"></i>Adjust Stock
                </button>
                <hr class="my-1">
                <a href="/inventory_system/modules/products/index.php" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-2"></i>Edit Product
                </a>
            </div>
        </div>
    </div>

</div>

<!-- Transaction History -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Transaction History</h5>
        <span class="badge bg-secondary"><?= count($txHistory) ?> recent records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Reference</th><th>Type</th><th>Qty</th>
                        <th>Unit Price</th><th>Total</th><th>Notes</th><th>By</th><th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($txHistory as $tx): ?>
                <tr>
                    <td><code><?= htmlspecialchars($tx['reference_no']) ?></code></td>
                    <td><span class="badge <?= $badges[$tx['type']] ?>"><?= $txLabels[$tx['type']] ?></span></td>
                    <td><?= number_format($tx['quantity']) ?></td>
                    <td>₱<?= number_format($tx['unit_price'], 2) ?></td>
                    <td>₱<?= number_format($tx['quantity'] * $tx['unit_price'], 2) ?></td>
                    <td><?= htmlspecialchars($tx['notes'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($tx['username'] ?? '—') ?></td>
                    <td><?= date('M d, Y H:i', strtotime($tx['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$txHistory): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No transactions recorded yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Transaction Modal -->
<div class="modal fade" id="txModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="txModalTitle">Stock Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="txForm">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="txProductId">
                    <input type="hidden" name="type"       id="txType">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" id="txProductName" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Price (₱)</label>
                        <input type="number" name="unit_price" class="form-control"
                               step="0.01" min="0" value="<?= $p['price'] ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<JS
<script>
const txModal    = new bootstrap.Modal(document.getElementById('txModal'));
const typeLabels = { stock_in:'Stock In', stock_out:'Stock Out', adjustment:'Adjust Stock' };

function openTx(id, name, type) {
    document.getElementById('txProductId').value = id;
    document.getElementById('txType').value      = type;
    document.getElementById('txProductName').value = name;
    document.getElementById('txModalTitle').textContent = typeLabels[type] + ' — ' + name;
    document.getElementById('txForm').querySelector('[name=quantity]').value = '';
    txModal.show();
}

document.getElementById('txForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const res  = await fetch('/inventory_system/api/transactions/save.php', { method:'POST', body: new FormData(this) });
    const data = await res.json();
    if (data.success) { txModal.hide(); location.reload(); }
    else alert(data.message);
});
</script>
JS;
require_once __DIR__ . '/../../includes/layout_bottom.php';
