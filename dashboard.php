<?php
$pageTitle = 'Dashboard';
$pageIcon  = 'home';
require_once 'includes/layout_top.php';

// KPI queries
$totalProducts = $pdo->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn();
$totalValue    = $pdo->query('SELECT COALESCE(SUM(i.quantity * p.price),0) FROM inventory i JOIN products p ON p.id=i.product_id')->fetchColumn();
$lowStock      = $pdo->query('SELECT COUNT(*) FROM inventory i JOIN products p ON p.id=i.product_id WHERE i.quantity <= p.reorder_level AND i.quantity > 0')->fetchColumn();
$outOfStock    = $pdo->query('SELECT COUNT(*) FROM inventory i WHERE i.quantity = 0')->fetchColumn();

// Recent transactions
$recentTx = $pdo->query('SELECT t.*, p.name AS product_name, u.username FROM transactions t JOIN products p ON p.id=t.product_id LEFT JOIN users u ON u.id=t.performed_by ORDER BY t.created_at DESC LIMIT 8')->fetchAll();

// Category stock chart data
$catData = $pdo->query('SELECT c.name, COALESCE(SUM(i.quantity),0) AS qty FROM categories c LEFT JOIN products p ON p.category_id=c.id LEFT JOIN inventory i ON i.product_id=p.id GROUP BY c.id, c.name')->fetchAll();

// Low stock items
$lowItems = $pdo->query('SELECT p.name, p.sku, i.quantity, p.reorder_level, c.name AS category FROM inventory i JOIN products p ON p.id=i.product_id LEFT JOIN categories c ON c.id=p.category_id WHERE i.quantity <= p.reorder_level ORDER BY i.quantity ASC LIMIT 5')->fetchAll();
?>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-box"></i></div>
            <div class="info-card-label">Total Products</div>
            <div class="info-card-value"><?= number_format($totalProducts) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-peso-sign" style="color:#27ae60"></i></div>
            <div class="info-card-label">Inventory Value</div>
            <div class="info-card-value">₱<?= number_format($totalValue, 2) ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-exclamation-triangle" style="color:#f39c12"></i></div>
            <div class="info-card-label">Low Stock</div>
            <div class="info-card-value"><?= $lowStock ?></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="info-card">
            <div class="info-card-icon"><i class="fas fa-times-circle" style="color:#e74c3c"></i></div>
            <div class="info-card-label">Out of Stock</div>
            <div class="info-card-value"><?= $outOfStock ?></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Stock by Category Chart -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><h5><i class="fas fa-chart-bar text-primary me-2"></i>Stock by Category</h5></div>
            <div class="card-body"><canvas id="catChart" height="120"></canvas></div>
        </div>
    </div>
    <!-- Low Stock Alert -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><h5><i class="fas fa-exclamation-triangle text-warning me-2"></i>Low Stock Alerts</h5></div>
            <div class="card-body p-0">
                <?php if ($lowItems): ?>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Product</th><th>Category</th><th>Qty</th><th>Min</th></tr></thead>
                    <tbody>
                    <?php foreach ($lowItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?><br><small class="text-muted"><?= htmlspecialchars($item['sku']) ?></small></td>
                        <td><?= htmlspecialchars($item['category'] ?? '-') ?></td>
                        <td><span class="badge <?= $item['quantity'] == 0 ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= $item['quantity'] ?></span></td>
                        <td><?= $item['reorder_level'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="p-3 text-success"><i class="fas fa-check-circle me-1"></i>All stock levels are healthy.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-exchange-alt text-primary me-2"></i>Recent Transactions</h5>
        <a href="/inventory_system/modules/transactions/index.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Reference</th><th>Product</th><th>Type</th><th>Qty</th><th>By</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($recentTx as $tx): ?>
                <tr>
                    <td><code><?= htmlspecialchars($tx['reference_no']) ?></code></td>
                    <td><?= htmlspecialchars($tx['product_name']) ?></td>
                    <td>
                        <?php
                        $badges = ['stock_in'=>'bg-success','stock_out'=>'bg-danger','adjustment'=>'bg-info'];
                        $labels = ['stock_in'=>'Stock In','stock_out'=>'Stock Out','adjustment'=>'Adjustment'];
                        ?>
                        <span class="badge <?= $badges[$tx['type']] ?>"><?= $labels[$tx['type']] ?></span>
                    </td>
                    <td><?= number_format($tx['quantity']) ?></td>
                    <td><?= htmlspecialchars($tx['username'] ?? '-') ?></td>
                    <td><?= date('M d, Y H:i', strtotime($tx['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$recentTx): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">No transactions yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$catLabels = json_encode(array_column($catData, 'name'));
$catQty    = json_encode(array_map('intval', array_column($catData, 'qty')));
$extraScripts = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('catChart'), {
    type: 'bar',
    data: {
        labels: $catLabels,
        datasets: [{
            label: 'Quantity in Stock',
            data: $catQty,
            backgroundColor: ['#3498db','#9b59b6','#e74c3c','#f1c40f','#2ecc71','#1abc9c'],
        }]
    },
    options: { responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
});
</script>
JS;
require_once 'includes/layout_bottom.php';
