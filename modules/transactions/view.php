<?php
// ── Data & auth BEFORE any output ──────────────────────────
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
startSecureSession();
requireLogin();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: /inventory_system/modules/transactions/index.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT t.id, t.reference_no, t.type, t.quantity, t.unit_price, t.notes, t.created_at,
            p.name AS product_name, p.sku, p.unit,
            c.name AS category, u.username AS performed_by_name
     FROM transactions t
     JOIN products p ON p.id = t.product_id
     LEFT JOIN categories c ON c.id = p.category_id
     LEFT JOIN users u ON u.id = t.performed_by
     WHERE t.id = ?'
);
$stmt->execute([$id]);
$t = $stmt->fetch();

if (!$t) {
    header('Location: /inventory_system/modules/transactions/index.php');
    exit;
}

$badges = ['stock_in' => 'bg-success', 'stock_out' => 'bg-danger', 'adjustment' => 'bg-info'];
$labels = ['stock_in' => 'Stock In',   'stock_out' => 'Stock Out', 'adjustment' => 'Adjustment'];

// ── Layout (outputs HTML from here) ────────────────────────
$pageTitle = 'Transaction Receipt — ' . htmlspecialchars($t['reference_no']);
$pageIcon  = 'receipt';
require_once __DIR__ . '/../../includes/layout_top.php';
?>

<div class="mb-3 d-flex gap-2">
    <a href="/inventory_system/modules/transactions/index.php" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Transactions
    </a>
    <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-print me-1"></i>Print Receipt
    </button>
</div>

<div class="row g-3 justify-content-center">
    <div class="col-lg-7">
        <div class="card" id="receiptCard">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-receipt me-2 text-primary"></i>Transaction Receipt</h5>
                <span class="badge <?= $badges[$t['type']] ?> fs-6"><?= $labels[$t['type']] ?></span>
            </div>
            <div class="card-body">

                <!-- Reference banner -->
                <div class="text-center p-3 mb-4 rounded" style="background:#f8f9fa;border:2px dashed #dee2e6;">
                    <div class="text-muted small mb-1">Reference Number</div>
                    <div style="font-size:22px;font-weight:700;letter-spacing:2px;font-family:monospace;">
                        <?= htmlspecialchars($t['reference_no']) ?>
                    </div>
                    <div class="text-muted small mt-1">
                        <?= date('F d, Y — h:i A', strtotime($t['created_at'])) ?>
                    </div>
                </div>

                <!-- Product & transaction info -->
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded" style="background:#f8f9fa;">
                            <div class="text-muted small fw-semibold text-uppercase mb-2">Product</div>
                            <div class="fw-bold"><?= htmlspecialchars($t['product_name']) ?></div>
                            <div class="text-muted small">SKU: <?= htmlspecialchars($t['sku']) ?></div>
                            <div class="text-muted small">Category: <?= htmlspecialchars($t['category'] ?? '—') ?></div>
                            <div class="text-muted small">Unit: <?= htmlspecialchars($t['unit']) ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded" style="background:#f8f9fa;">
                            <div class="text-muted small fw-semibold text-uppercase mb-2">Transaction</div>
                            <div class="fw-bold">
                                Type: <span class="badge <?= $badges[$t['type']] ?>"><?= $labels[$t['type']] ?></span>
                            </div>
                            <div class="text-muted small mt-1">
                                By: <strong><?= htmlspecialchars($t['performed_by_name'] ?? '—') ?></strong>
                            </div>
                            <div class="text-muted small">
                                Date: <strong><?= date('M d, Y H:i', strtotime($t['created_at'])) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Amounts -->
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Quantity</td>
                        <td class="text-end fw-bold fs-5">
                            <?= number_format($t['quantity']) ?> <?= htmlspecialchars($t['unit']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Unit Price</td>
                        <td class="text-end">₱<?= number_format($t['unit_price'], 2) ?></td>
                    </tr>
                    <tr style="border-top:2px solid #dee2e6;">
                        <td class="fw-bold fs-5">Total Value</td>
                        <td class="text-end fw-bold fs-5 text-primary">
                            ₱<?= number_format($t['quantity'] * $t['unit_price'], 2) ?>
                        </td>
                    </tr>
                </table>

                <?php if (!empty($t['notes'])): ?>
                <hr>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase mb-1">Notes</div>
                    <p class="mb-0"><?= htmlspecialchars($t['notes']) ?></p>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .sidebar-overlay, .topbar, .btn { display: none !important; }
    .main-content { margin-left: 0 !important; width: 100% !important; }
    #receiptCard { box-shadow: none !important; border: 1px solid #ccc !important; }
}
</style>

<?php require_once __DIR__ . '/../../includes/layout_bottom.php'; ?>
