<?php
$pageTitle = 'Products';
$pageIcon  = 'box';
require_once __DIR__ . '/../../includes/layout_top.php';
requireRole(['admin','manager']);

$products = $pdo->query(
    'SELECT p.*, c.name AS category_name, COALESCE(i.quantity,0) AS stock
     FROM products p
     LEFT JOIN categories c ON c.id=p.category_id
     LEFT JOIN inventory i ON i.product_id=p.id
     WHERE p.is_active=1
     ORDER BY p.name'
)->fetchAll();

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 text-muted">Manage products and pricing</h6>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#prodModal">
        <i class="fas fa-plus me-1"></i>Add Product
    </button>
</div>
<div id="alertBox"></div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="prodTable">
                <thead><tr><th>SKU</th><th>Name</th><th>Category</th><th>Unit</th><th>Price</th><th>Stock</th><th>Reorder</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                <tr id="row-<?= $p['id'] ?>">
                    <td><code><?= htmlspecialchars($p['sku']) ?></code></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['unit']) ?></td>
                    <td>₱<?= number_format($p['price'], 2) ?></td>
                    <td>
                        <?php
                        $sc = $p['stock'] == 0 ? 'bg-danger' : ($p['stock'] <= $p['reorder_level'] ? 'bg-warning text-dark' : 'bg-success');
                        ?>
                        <span class="badge <?= $sc ?>"><?= $p['stock'] ?></span>
                    </td>
                    <td><?= $p['reorder_level'] ?></td>
                    <td>
                        <a href="/inventory_system/modules/products/view.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                        <button class="btn btn-sm btn-outline-primary" onclick="editProd(<?= htmlspecialchars(json_encode($p)) ?>)"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteProd(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name']) ?>')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="prodModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="prodModalTitle">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="prodForm">
                <div class="modal-body">
                    <input type="hidden" id="prodId" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" id="pSku" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" id="pName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category_id" id="pCat" class="form-select">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" id="pUnit" class="form-control" value="pcs">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Price (₱)</label>
                            <input type="number" name="price" id="pPrice" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reorder Level</label>
                            <input type="number" name="reorder_level" id="pReorder" class="form-control" value="10" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="pDesc" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<JS
<script>
const modal = new bootstrap.Modal(document.getElementById('prodModal'));
document.getElementById('prodModal').addEventListener('show.bs.modal', e => {
    if (!e.relatedTarget) return;
    document.getElementById('prodForm').reset();
    document.getElementById('prodId').value = '';
    document.getElementById('prodModalTitle').textContent = 'Add Product';
});
function editProd(p) {
    document.getElementById('prodId').value = p.id;
    document.getElementById('pSku').value = p.sku;
    document.getElementById('pName').value = p.name;
    document.getElementById('pCat').value = p.category_id || '';
    document.getElementById('pUnit').value = p.unit;
    document.getElementById('pPrice').value = p.price;
    document.getElementById('pReorder').value = p.reorder_level;
    document.getElementById('pDesc').value = p.description || '';
    document.getElementById('prodModalTitle').textContent = 'Edit Product';
    modal.show();
}
document.getElementById('prodForm').addEventListener('submit', async e => {
    e.preventDefault();
    const res = await fetch('/inventory_system/api/products/save.php', {method:'POST', body: new FormData(e.target)});
    const data = await res.json();
    showAlert(data.message, data.success ? 'success' : 'danger');
    if (data.success) { modal.hide(); setTimeout(() => location.reload(), 800); }
});
async function deleteProd(id, name) {
    if (!confirm('Delete product "' + name + '"?')) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('/inventory_system/api/products/delete.php', {method:'POST', body: fd});
    const data = await res.json();
    showAlert(data.message, data.success ? 'success' : 'danger');
    if (data.success) document.getElementById('row-' + id).remove();
}
function showAlert(msg, type) {
    document.getElementById('alertBox').innerHTML = `<div class="alert alert-\${type} alert-dismissible"><i class="fas fa-info-circle me-1"></i>\${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
}
</script>
JS;
require_once __DIR__ . '/../../includes/layout_bottom.php';
