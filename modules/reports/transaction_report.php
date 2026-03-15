<?php
$pageTitle = 'Transaction Report';
$pageIcon  = 'chart-line';
require_once __DIR__ . '/../../includes/layout_top.php';

$from    = $_GET['from'] ?? date('Y-m-01');
$to      = $_GET['to']   ?? date('Y-m-d');
$type    = $_GET['type']  ?? 'all';
$groupBy = $_GET['group'] ?? 'day';

$where  = 'WHERE DATE(t.created_at) BETWEEN ? AND ?';
$params = [$from, $to];
if ($type !== 'all') { $where .= ' AND t.type = ?'; $params[] = $type; }

// Summary totals
$summary = $pdo->prepare(
    "SELECT
        COUNT(*) AS total_tx,
        SUM(quantity) AS total_qty,
        SUM(quantity * unit_price) AS total_value,
        SUM(CASE WHEN type='stock_in'    THEN quantity ELSE 0 END) AS in_qty,
        SUM(CASE WHEN type='stock_out'   THEN quantity ELSE 0 END) AS out_qty,
        SUM(CASE WHEN type='adjustment'  THEN 1 ELSE 0 END) AS adj_count
     FROM transactions t $where"
);
$summary->execute($params);
$s = $summary->fetch();

// Trend grouped by day or month
$dateFmt = $groupBy === 'month' ? "DATE_FORMAT(t.created_at,'%Y-%m')" : "DATE(t.created_at)";
$trend = $pdo->prepare(
    "SELECT $dateFmt AS period,
            SUM(CASE WHEN type='stock_in'  THEN quantity ELSE 0 END) AS stock_in,
            SUM(CASE WHEN type='stock_out' THEN quantity ELSE 0 END) AS stock_out,
            COUNT(*) AS tx_count
     FROM transactions t $where
     GROUP BY period ORDER BY period"
);
$trend->execute($params);
$trendRows = $trend->fetchAll();

// Top products by transaction volume
$topProducts = $pdo->prepare(
    "SELECT p.name, p.sku, COUNT(*) AS tx_count, SUM(t.quantity) AS total_qty,
            SUM(t.quantity * t.unit_price) AS total_value
     FROM transactions t
     JOIN products p ON p.id = t.product_id
     $where
     GROUP BY t.product_id, p.name, p.sku
     ORDER BY total_qty DESC LIMIT 10"
);
$topProducts->execute($params);
$topProds = $topProducts->fetchAll();

// Full transaction list
$txList = $pdo->prepare(
    "SELECT t.*, p.name AS product_name, p.sku, u.username
     FROM transactions t
     JOIN products p ON p.id = t.product_id
     LEFT JOIN users u ON u.id = t.performed_by
     $where
     ORDER BY t.created_at DESC"
);
$txList->execute($params);
$transactions = $txList->fetchAll();

$badges = ['stock_in'=>'bg-success','stock_out'=>'bg-danger','adjustment'=>'bg-info'];
$labels = ['stock_in'=>'Stock In','stock_out'=>'Stock Out','adjustment'=>'Adjustment'];
?>

<!-- Filters -->
<form method="GET" class="row g-2 mb-4">
    <div class="col-6 col-md-2">
        <label class="form-label small">From</label>
        <input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>">
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label small">To</label>
        <input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>">
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label small">Type</label>
        <select name="type" class="form-select form-select-sm">
            <option value="all"        <?= $type==='all'?'selected':'' ?>>All Types</option>
            <option value="stock_in"   <?= $type==='stock_in'?'selected':'' ?>>Stock In</option>
            <option value="stock_out"  <?= $type==='stock_out'?'selected':'' ?>>Stock Out</option>
            <option value="adjustment" <?= $type==='adjustment'?'selected':'' ?>>Adjustment</option>
        </select>
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label small">Group By</label>
        <select name="group" class="form-select form-select-sm">
            <option value="day"   <?= $groupBy==='day'?'selected':'' ?>>Day</option>
            <option value="month" <?= $groupBy==='month'?'selected':'' ?>>Month</option>
        </select>
    </div>
    <div class="col-12 col-md-4 d-flex align-items-end gap-2">
        <button class="btn btn-sm btn-primary">Apply</button>
        <a href="?" class="btn btn-sm btn-secondary">Reset</a>
        <a href="/inventory_system/api/reports/export.php?from=<?= $from ?>&to=<?= $to ?>&type=<?= $type ?>"
           class="btn btn-sm btn-success">
            <i class="fas fa-download me-1"></i>Export CSV
        </a>
    </div>
</form>

<!-- KPI Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-exchange-alt"></i></div>
            <div class="info-card-label">Transactions</div>
            <div class="info-card-value"><?= number_format($s['total_tx']) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-arrow-down" style="color:#27ae60"></i></div>
            <div class="info-card-label">Stock In Qty</div>
            <div class="info-card-value"><?= number_format($s['in_qty']) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-arrow-up" style="color:#e74c3c"></i></div>
            <div class="info-card-label">Stock Out Qty</div>
            <div class="info-card-value"><?= number_format($s['out_qty']) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-sliders-h" style="color:#3498db"></i></div>
            <div class="info-card-label">Adjustments</div>
            <div class="info-card-value"><?= number_format($s['adj_count']) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-cubes" style="color:#9b59b6"></i></div>
            <div class="info-card-label">Total Qty</div>
            <div class="info-card-value"><?= number_format($s['total_qty']) ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-peso-sign" style="color:#f39c12"></i></div>
            <div class="info-card-label">Total Value</div>
            <div class="info-card-value" style="font-size:16px">₱<?= number_format($s['total_value'], 2) ?></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Trend Chart -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header"><h5><i class="fas fa-chart-line me-2 text-primary"></i>Movement Trend</h5></div>
            <div class="card-body"><canvas id="trendChart" height="120"></canvas></div>
        </div>
    </div>
    <!-- Top Products -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h5><i class="fas fa-trophy me-2 text-warning"></i>Top Products</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Product</th><th>Qty</th><th>Value</th></tr></thead>
                    <tbody>
                    <?php foreach ($topProds as $tp): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($tp['name']) ?>
                            <br><small class="text-muted"><?= htmlspecialchars($tp['sku']) ?></small>
                        </td>
                        <td><?= number_format($tp['total_qty']) ?></td>
                        <td>₱<?= number_format($tp['total_value'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$topProds): ?>
                    <tr><td colspan="3" class="text-center text-muted py-3">No data.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Full Transaction Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>All Transactions</h5>
        <span class="text-muted small"><?= count($transactions) ?> records</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Reference</th><th>Type</th><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th><th>By</th><th>Date</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($transactions as $tx): ?>
                <tr>
                    <td><code><?= htmlspecialchars($tx['reference_no']) ?></code></td>
                    <td><span class="badge <?= $badges[$tx['type']] ?>"><?= $labels[$tx['type']] ?></span></td>
                    <td><?= htmlspecialchars($tx['product_name']) ?><br><small class="text-muted"><?= htmlspecialchars($tx['sku']) ?></small></td>
                    <td><?= number_format($tx['quantity']) ?></td>
                    <td>₱<?= number_format($tx['unit_price'], 2) ?></td>
                    <td>₱<?= number_format($tx['quantity'] * $tx['unit_price'], 2) ?></td>
                    <td><?= htmlspecialchars($tx['username'] ?? '—') ?></td>
                    <td><?= date('M d, Y H:i', strtotime($tx['created_at'])) ?></td>
                    <td>
                        <a href="/inventory_system/modules/transactions/view.php?id=<?= $tx['id'] ?>"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$transactions): ?>
                <tr><td colspan="9" class="text-center text-muted py-4">No transactions in this period.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$trendLabels = json_encode(array_column($trendRows, 'period'));
$trendIn     = json_encode(array_map('intval', array_column($trendRows, 'stock_in')));
$trendOut    = json_encode(array_map('intval', array_column($trendRows, 'stock_out')));
$extraScripts = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: $trendLabels,
        datasets: [
            { label: 'Stock In',  data: $trendIn,  backgroundColor: 'rgba(46,204,113,.7)' },
            { label: 'Stock Out', data: $trendOut, backgroundColor: 'rgba(231,76,60,.7)' }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { x: { stacked: false }, y: { beginAtZero: true } }
    }
});
</script>
JS;
require_once __DIR__ . '/../../includes/layout_bottom.php';
