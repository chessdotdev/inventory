<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireRole(['admin','manager','inventory_officer']);

$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? 'all';

$where  = 'WHERE p.is_active=1';
$params = [];
if ($search) {
    $where   .= ' AND (p.name LIKE ? OR p.sku LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filter === 'low') $where .= ' AND i.quantity > 0 AND i.quantity <= p.reorder_level';
if ($filter === 'out') $where .= ' AND i.quantity = 0';
if ($filter === 'ok')  $where .= ' AND i.quantity > p.reorder_level';

$stmt = $pdo->prepare(
    "SELECT p.id, p.sku, p.name, p.unit, p.price, p.reorder_level,
            c.name AS category, COALESCE(i.quantity,0) AS quantity, i.last_updated
     FROM products p
     LEFT JOIN categories c ON c.id=p.category_id
     LEFT JOIN inventory i ON i.product_id=p.id
     $where ORDER BY p.name"
);
$stmt->execute($params);
$items = $stmt->fetchAll();

$pageTitle = 'Inventory';
$pageIcon  = 'warehouse';
require_once __DIR__ . '/../../includes/layout_top.php';
?>

<!-- Toolbar -->
<div class="row g-2 align-items-center mb-3">
    <div class="col-md-5">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search by name or SKU..." value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
            <button class="btn btn-sm btn-primary">Search</button>
            <?php if ($search): ?>
            <a href="?filter=<?= htmlspecialchars($filter) ?>" class="btn btn-sm btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="col-md-4">
        <div class="btn-group btn-group-sm w-100">
            <a href="?filter=all" class="btn btn-outline-secondary <?= $filter==='all'?'active':'' ?>">All</a>
            <a href="?filter=ok"  class="btn btn-outline-success  <?= $filter==='ok' ?'active':'' ?>">In Stock</a>
            <a href="?filter=low" class="btn btn-outline-warning  <?= $filter==='low'?'active':'' ?>">Low Stock</a>
            <a href="?filter=out" class="btn btn-outline-danger   <?= $filter==='out'?'active':'' ?>">Out of Stock</a>
        </div>
    </div>
    <?php if (in_array($user['role'], ['admin','manager'])): ?>
    <div class="col-md-3 text-md-end">
        <a href="/inventory_system/modules/inventory/adjust.php" class="btn btn-warning btn-sm w-100">
            <i class="fas fa-sliders-h me-1"></i>Stock Adjustment
        </a>
    </div>
    <?php endif; ?>
</div>

<div id="alertBox"></div>

<!-- Stock Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-warehouse me-2 text-primary"></i>Stock Levels</h5>
        <span class="text-muted small"><?= number_format(count($items)) ?> products</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>SKU</th><th>Product</th><th>Category</th><th>Unit</th>
                        <th>Price</th><th>Stock</th><th>Reorder</th><th>Last Updated</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                <?php $sc = $item['quantity'] == 0 ? 'bg-danger' : ($item['quantity'] <= $item['reorder_level'] ? 'bg-warning text-dark' : 'bg-success'); ?>
                <tr>
                    <td><code><?= htmlspecialchars($item['sku']) ?></code></td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['category'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($item['unit']) ?></td>
                    <td>₱<?= number_format($item['price'], 2) ?></td>
                    <td><span class="badge <?= $sc ?>"><?= $item['quantity'] ?></span></td>
                    <td><?= $item['reorder_level'] ?></td>
                    <td><?= $item['last_updated'] ? date('M d, Y', strtotime($item['last_updated'])) : '-' ?></td>
                    <td>
                        <button class="btn btn-sm btn-success"
                                onclick="openTx(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>', 'stock_in', <?= $item['price'] ?>)">
                            <i class="fas fa-plus"></i> In
                        </button>
                        <button class="btn btn-sm btn-danger"
                                onclick="openTx(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>', 'stock_out', <?= $item['price'] ?>)">
                            <i class="fas fa-minus"></i> Out
                        </button>
                        <?php if (in_array($user['role'], ['admin','manager'])): ?>
                        <button class="btn btn-sm btn-warning"
                                onclick="openTx(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['name'])) ?>', 'adjustment', <?= $item['price'] ?>)">
                            <i class="fas fa-sliders-h"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$items): ?>
                <tr><td colspan="9" class="text-center text-muted py-3">No items found.</td></tr>
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
                    <input type="hidden" name="type" id="txType">
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
                        <input type="number" name="unit_price" id="txUnitPrice" class="form-control" step="0.01" min="0">
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
const typeLabels = {stock_in:'Stock In', stock_out:'Stock Out', adjustment:'Adjustment'};

function openTx(id, name, type, price) {
    document.getElementById('txProductId').value  = id;
    document.getElementById('txType').value       = type;
    document.getElementById('txProductName').value = name;
    document.getElementById('txUnitPrice').value  = price;
    document.getElementById('txModalTitle').textContent = typeLabels[type] + ' — ' + name;
    document.getElementById('txForm').querySelector('[name=quantity]').value = '';
    txModal.show();
}

document.getElementById('txForm').addEventListener('submit', async e => {
    e.preventDefault();
    const res  = await fetch('/inventory_system/api/transactions/save.php', {method:'POST', body: new FormData(e.target)});
    const data = await res.json();
    showAlert(data.message, data.success ? 'success' : 'danger');
    if (data.success) { txModal.hide(); setTimeout(() => location.reload(), 800); }
});

function showAlert(msg, type) {
    document.getElementById('alertBox').innerHTML =
        `<div class="alert alert-\${type} alert-dismissible"><i class="fas fa-info-circle me-1"></i>\${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
}
</script>
JS;
require_once __DIR__ . '/../../includes/layout_bottom.php';
