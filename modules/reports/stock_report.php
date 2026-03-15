<?php
$pageTitle = 'Stock Report';
$pageIcon  = 'boxes';
require_once __DIR__ . '/../../includes/layout_top.php';

$category = intval($_GET['category'] ?? 0);
$status   = $_GET['status'] ?? 'all';

$where  = 'WHERE p.is_active = 1';
$params = [];
if ($category) { $where .= ' AND p.category_id = ?'; $params[] = $category; }
if ($status === 'low')  $where .= ' AND i.quantity > 0 AND i.quantity <= p.reorder_level';
if ($status === 'out')  $where .= ' AND i.quantity = 0';
if ($status === 'ok')   $where .= ' AND i.quantity > p.reorder_level';

$stmt = $pdo->prepare(
    "SELECT p.id, p.sku, p.name, p.unit, p.price, p.reorder_level,
            c.name AS category, COALESCE(i.quantity,0) AS quantity,
            (COALESCE(i.quantity,0) * p.price) AS value,
            i.last_updated
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     LEFT JOIN inventory i ON i.product_id = p.id
     $where
     ORDER BY value DESC"
);
$stmt->execute($params);
$items = $stmt->fetchAll();

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

// Summary totals
$totalQty   = array_sum(array_column($items, 'quantity'));
$totalValue = array_sum(array_column($items, 'value'));
$outCount   = count(array_filter($items, fn($r) => $r['quantity'] == 0));
$lowCount   = count(array_filter($items, fn($r) => $r['quantity'] > 0 && $r['quantity'] <= $r['reorder_level']));

// Category breakdown
$catBreakdown = $pdo->query(
    'SELECT c.name, COUNT(p.id) AS products,
            COALESCE(SUM(i.quantity),0) AS qty,
            COALESCE(SUM(i.quantity * p.price),0) AS value
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
     LEFT JOIN inventory i ON i.product_id = p.id
     GROUP BY c.id, c.name
     ORDER BY value DESC'
)->fetchAll();
?>

<!-- Filters -->
<form method="GET" class="row g-2 mb-4">
    <div class="col-md-3">
        <select name="category" class="form-select form-select-sm">
            <option value="">All Categories</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $category == $c['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Status</option>
            <option value="ok"  <?= $status === 'ok'  ? 'selected' : '' ?>>In Stock</option>
            <option value="low" <?= $status === 'low' ? 'selected' : '' ?>>Low Stock</option>
            <option value="out" <?= $status === 'out' ? 'selected' : '' ?>>Out of Stock</option>
        </select>
    </div>
    <div class="col-md-4 d-flex gap-2">
        <button class="btn btn-sm btn-primary">Apply</button>
        <a href="?" class="btn btn-sm btn-secondary">Reset</a>
        <a href="/inventory_system/api/reports/stock_export.php?category=<?= $category ?>&status=<?= $status ?>"
           class="btn btn-sm btn-success">
            <i class="fas fa-download me-1"></i>Export CSV
        </a>
    </div>
</form>

<!-- KPI Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-box"></i></div>
            <div class="info-card-label">Products</div>
            <div class="info-card-value"><?= count($items) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-cubes" style="color:#9b59b6"></i></div>
            <div class="info-card-label">Total Units</div>
            <div class="info-card-value"><?= number_format($totalQty) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-peso-sign" style="color:#27ae60"></i></div>
            <div class="info-card-label">Total Value</div>
            <div class="info-card-value" style="font-size:18px">₱<?= number_format($totalValue, 2) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-exclamation-triangle" style="color:#e74c3c"></i></div>
            <div class="info-card-label">Low / Out</div>
            <div class="info-card-value"><?= $lowCount ?> / <?= $outCount ?></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Category Breakdown -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><h5><i class="fas fa-tags me-2 text-primary"></i>By Category</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Category</th><th>Products</th><th>Units</th><th>Value</th></tr></thead>
                    <tbody>
                    <?php foreach ($catBreakdown as $cb): ?>
                    <tr>
                        <td><?= htmlspecialchars($cb['name']) ?></td>
                        <td><?= $cb['products'] ?></td>
                        <td><?= number_format($cb['qty']) ?></td>
                        <td>₱<?= number_format($cb['value'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Chart -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><h5><i class="fas fa-chart-pie me-2 text-primary"></i>Value Distribution</h5></div>
            <div class="card-body"><canvas id="catChart" height="160"></canvas></div>
        </div>
    </div>
</div>

<!-- Full Stock Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>Stock Details</h5>
        <span class="text-muted small"><?= count($items) ?> items</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>SKU</th><th>Product</th><th>Category</th><th>Unit</th><th>Price</th><th>Stock</th><th>Value</th><th>Status</th><th>Last Updated</th></tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item):
                    $sc = $item['quantity'] == 0 ? 'bg-danger' : ($item['quantity'] <= $item['reorder_level'] ? 'bg-warning text-dark' : 'bg-success');
                    $sl = $item['quantity'] == 0 ? 'Out of Stock' : ($item['quantity'] <= $item['reorder_level'] ? 'Low Stock' : 'In Stock');
                ?>
                <tr>
                    <td><code><?= htmlspecialchars($item['sku']) ?></code></td>
                    <td>
                        <a href="/inventory_system/modules/products/view.php?id=<?= $item['id'] ?>" class="text-decoration-none">
                            <?= htmlspecialchars($item['name']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($item['category'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($item['unit']) ?></td>
                    <td>₱<?= number_format($item['price'], 2) ?></td>
                    <td><?= number_format($item['quantity']) ?></td>
                    <td>₱<?= number_format($item['value'], 2) ?></td>
                    <td><span class="badge <?= $sc ?>"><?= $sl ?></span></td>
                    <td><?= $item['last_updated'] ? date('M d, Y', strtotime($item['last_updated'])) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$items): ?>
                <tr><td colspan="9" class="text-center text-muted py-4">No items found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$catNames  = json_encode(array_column($catBreakdown, 'name'));
$catValues = json_encode(array_map(fn($r) => round($r['value'], 2), $catBreakdown));
$extraScripts = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('catChart'), {
    type: 'doughnut',
    data: {
        labels: $catNames,
        datasets: [{
            data: $catValues,
            backgroundColor: ['#3498db','#9b59b6','#e74c3c','#f1c40f','#2ecc71','#1abc9c','#e67e22','#34495e']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'right' },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.label + ': ₱' + ctx.raw.toLocaleString('en-PH', {minimumFractionDigits:2})
                }
            }
        }
    }
});
</script>
JS;
require_once __DIR__ . '/../../includes/layout_bottom.php';
