<?php
$pageTitle = 'Categories';
$pageIcon  = 'tags';
require_once __DIR__ . '/../../includes/layout_top.php';
requireRole(['admin','manager']);

$categories = $pdo->query('SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 text-muted">Product categories</h6>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#catModal">
        <i class="fas fa-plus me-1"></i>Add Category
    </button>
</div>
<div id="alertBox"></div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>#</th><th>Name</th><th>Description</th><th>Products</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $i => $c): ?>
            <tr id="row-<?= $c['id'] ?>">
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><?= htmlspecialchars($c['description'] ?? '-') ?></td>
                <td><span class="badge bg-primary"><?= $c['product_count'] ?></span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editCat(<?= htmlspecialchars(json_encode($c)) ?>)"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCat(<?= $c['id'] ?>, '<?= htmlspecialchars($c['name']) ?>')"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="catModalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="catForm">
                <div class="modal-body">
                    <input type="hidden" id="catId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" id="catName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="catDesc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<JS
<script>
const modal = new bootstrap.Modal(document.getElementById('catModal'));
document.getElementById('catModal').addEventListener('show.bs.modal', e => {
    if (!e.relatedTarget) return;
    document.getElementById('catForm').reset();
    document.getElementById('catId').value = '';
    document.getElementById('catModalTitle').textContent = 'Add Category';
});
function editCat(c) {
    document.getElementById('catId').value = c.id;
    document.getElementById('catName').value = c.name;
    document.getElementById('catDesc').value = c.description || '';
    document.getElementById('catModalTitle').textContent = 'Edit Category';
    modal.show();
}
document.getElementById('catForm').addEventListener('submit', async e => {
    e.preventDefault();
    const res = await fetch('/inventory_system/api/categories/save.php', {method:'POST', body: new FormData(e.target)});
    const data = await res.json();
    showAlert(data.message, data.success ? 'success' : 'danger');
    if (data.success) { modal.hide(); setTimeout(() => location.reload(), 800); }
});
async function deleteCat(id, name) {
    if (!confirm('Delete category "' + name + '"?')) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('/inventory_system/api/categories/delete.php', {method:'POST', body: fd});
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
