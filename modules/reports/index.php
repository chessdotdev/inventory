<?php
$pageTitle = 'Reports';
$pageIcon  = 'chart-bar';
require_once __DIR__ . '/../../includes/layout_top.php';

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

// Summary stats
$summary = $pdo->query(
    'SELECT
        COUNT(DISTINCT p.id) AS total_products,
        COALESCE(SUM(i.quantity),0) AS total_qty,
        COALESCE(SUM(i.quantity * p.price),0) AS total_value,
        COUNT(CASE WHEN i.quantity=0 THEN 1 END) AS out_of_stock,
        COUNT(CASE WHEN i.quantity>0 AND i.quantity<=p.reorder_level THEN 1 END) AS low_stock
     FROM products p LEFT JOIN inventory i ON i.product_id=p.id WHERE p.is_active=1'
)->fetch();

// Transaction summary for date range
$txSummary = $pdo->prepare(
    'SELECT type, COUNT(*) AS count, SUM(quantity) AS total_qty, SUM(quantity*unit_price) AS total_value
     FROM transactions WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY type'
);
$txSummary->execute([$from, $to]);
$txStats = [];
foreach ($txSummary->fetchAll() as $r) $txStats[$r['type']] = $r;

// Top products by stock value
$topProducts = $pdo->query(
    'SELECT p.name, p.sku, c.name AS category, i.quantity, p.price, (i.quantity*p.price) AS value
     FROM products p
     LEFT JOIN categories c ON c.id=p.category_id
     LEFT JOIN inventory i ON i.product_id=p.id
     WHERE p.is_active=1
     ORDER BY value DESC LIMIT 10'
)->fetchAll();

// Monthly transaction trend (last 6 months)
$trend = $pdo->query(
    "SELECT DATE_FORMAT(created_at,'%Y-%m') AS month,
            SUM(CASE WHEN type='stock_in' THEN quantity ELSE 0 END) AS stock_in,
            SUM(CASE WHEN type='stock_out' THEN quantity ELSE 0 END) AS stock_out
     FROM transactions
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY month ORDER BY month"
)->fetchAll();
?>

<!-- Date Filter -->
<form method="GET" class="row g-2 mb-4">
    <div class="col-md-3">
        <label class="form-label small">From</label>
        <input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label small">To</label>
        <input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>">
    </div>
    <div class="col-md-2 d-flex align-items-end gap-2">
        <button class="btn btn-sm btn-primary w-100">Apply</button>
    </div>
    <div class="col-md-2 d-flex align-items-end gap-2">
        <a href="/inventory_system/api/reports/export.php?from=<?= $from ?>&to=<?= $to ?>" class="btn btn-sm btn-success w-100">
            <i class="fas fa-download me-1"></i>Export CSV
        </a>
    </div>
    <div class="col-md-2 d-flex align-items-end gap-2">
        <a href="/inventory_system/modules/reports/stock_report.php" class="btn btn-sm btn-outline-primary w-100">
            <i class="fas fa-boxes me-1"></i>Stock Report
        </a>
    </div>
    <div class="col-md-2 d-flex align-items-end gap-2">
        <a href="/inventory_system/modules/reports/transaction_report.php" class="btn btn-sm btn-outline-info w-100">
            <i class="fas fa-chart-line me-1"></i>Tx Report
        </a>
    </div>
</form>

<!-- KPI Summary -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="info-card"><div class="info-card-icon"><i class="fas fa-box"></i></div>
            <div class="info-card-label">Total Products</div>
            <div class="info-card-value"><?= number_format($summary['total_products']) ?></div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="info-card"><div class="info-card-icon"><i class="fas fa-cubes" style="color:#9b59b6"></i></div>
            <div class="info-card-label">Total Quantity</div>
            <div class="info-card-value"><?= number_format($summary['total_qty']) ?></div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="info-card"><div class="info-card-icon"><i class="fas fa-peso-sign" style="color:#27ae60"></i></div>
            <div class="info-card-label">Inventory Value</div>
            <div class="info-card-value" style="font-size:20px">₱<?= number_format($summary['total_value'],2) ?></div></div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="info-card"><div class="info-card-icon"><i class="fas fa-times-circle" style="color:#e74c3c"></i></div>
            <div class="info-card-label">Out of Stock</div>
            <div class="info-card-value"><?= $summary['out_of_stock'] ?></div></div>
    </div>
</div>

<!-- Transaction Stats for Period -->
<div class="row g-3 mb-4">
    <?php
    $txTypes = ['stock_in'=>['Stock In','bg-success'], 'stock_out'=>['Stock Out','bg-danger'], 'adjustment'=>['Adjustments','bg-info']];
    foreach ($txTypes as $k => [$label, $bg]):
        $s = $txStats[$k] ?? ['count'=>0,'total_qty'=>0,'total_value'=>0];
    ?>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted"><?= $label ?> <small>(<?= $from ?> to <?= $to ?>)</small></h6>
                <div class="d-flex justify-content-between mt-2">
                    <div><div class="small text-muted">Transactions</div><strong><?= $s['count'] ?></strong></div>
                    <div><div class="small text-muted">Total Qty</div><strong><?= number_format($s['total_qty']) ?></strong></div>
                    <div><div class="small text-muted">Total Value</div><strong>₱<?= number_format($s['total_value'],2) ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
    <!-- Trend Chart -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5><i class="fas fa-chart-line text-primary me-2"></i>6-Month Stock Movement</h5></div>
            <div class="card-body"><canvas id="trendChart" height="120"></canvas></div>
        </div>
    </div>
    <!-- Top Products -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5><i class="fas fa-trophy text-warning me-2"></i>Top Products by Value</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Product</th><th>Qty</th><th>Value</th></tr></thead>
                    <tbody>
                    <?php foreach ($topProducts as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?><br><small class="text-muted"><?= htmlspecialchars($p['sku']) ?></small></td>
                        <td><?= number_format($p['quantity']) ?></td>
                        <td>₱<?= number_format($p['value'],2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$trendLabels = json_encode(array_column($trend, 'month'));
$trendIn     = json_encode(array_map('intval', array_column($trend, 'stock_in')));
$trendOut    = json_encode(array_map('intval', array_column($trend, 'stock_out')));
$extraScripts = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: $trendLabels,
        datasets: [
            { label: 'Stock In',  data: $trendIn,  borderColor:'#2ecc71', backgroundColor:'rgba(46,204,113,.1)', fill:true, tension:.3 },
            { label: 'Stock Out', data: $trendOut, borderColor:'#e74c3c', backgroundColor:'rgba(231,76,60,.1)',  fill:true, tension:.3 }
        ]
    },
    options: { responsive:true, scales:{ y:{ beginAtZero:true } } }
});
</script>
JS;
require_once __DIR__ . '/../../includes/layout_bottom.php';
