<?php
$pageTitle = 'User Management';
$pageIcon  = 'users';
require_once __DIR__ . '/../../includes/layout_top.php';
requireRole(['admin']);

$users = $pdo->query('SELECT id, username, email, role, is_active, created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 text-muted">Manage system users and roles</h6>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal">
        <i class="fas fa-plus me-1"></i>Add User
    </button>
</div>

<div id="alertBox"></div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>#</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                <tbody id="userTable">
                <?php foreach ($users as $i => $u): ?>
                <tr id="row-<?= $u['id'] ?>">
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge bg-<?= ['admin'=>'danger','manager'=>'warning text-dark','inventory_officer'=>'info text-dark','staff'=>'secondary'][$u['role']] ?? 'secondary' ?>"><?= $u['role'] === 'inventory_officer' ? 'Inv. Officer' : ucfirst($u['role']) ?></span></td>
                    <td><span class="badge <?= $u['is_active'] ? 'bg-success' : 'bg-secondary' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)"><i class="fas fa-edit"></i></button>
                        <?php if ($u['id'] != $user['id']): ?>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>')"><i class="fas fa-trash"></i></button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm">
                <div class="modal-body">
                    <input type="hidden" id="userId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" id="uUsername" class="form-control" required minlength="3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="uEmail" class="form-control" required>
                    </div>
                    <div class="mb-3" id="pwGroup">
                        <label class="form-label">Password <small class="text-muted" id="pwHint">(leave blank to keep current)</small></label>
                        <input type="password" name="password" id="uPassword" class="form-control" minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" id="uRole" class="form-select">
                            <option value="staff">Staff</option>
                            <option value="inventory_officer">Inventory Officer</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" id="uActive" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
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
const modal = new bootstrap.Modal(document.getElementById('userModal'));

document.getElementById('userModal').addEventListener('show.bs.modal', function(e) {
    if (!e.relatedTarget) return; // opened via editUser
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('modalTitle').textContent = 'Add User';
    document.getElementById('pwHint').style.display = 'none';
    document.getElementById('uPassword').required = true;
});

function editUser(u) {
    document.getElementById('userId').value = u.id;
    document.getElementById('uUsername').value = u.username;
    document.getElementById('uEmail').value = u.email;
    document.getElementById('uRole').value = u.role;
    document.getElementById('uActive').value = u.is_active;
    document.getElementById('uPassword').value = '';
    document.getElementById('uPassword').required = false;
    document.getElementById('pwHint').style.display = '';
    document.getElementById('modalTitle').textContent = 'Edit User';
    modal.show();
}

document.getElementById('userForm').addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await fetch('/inventory_system/api/users/save.php', {method:'POST', body: fd});
    const data = await res.json();
    showAlert(data.message, data.success ? 'success' : 'danger');
    if (data.success) { modal.hide(); setTimeout(() => location.reload(), 800); }
});

async function deleteUser(id, name) {
    if (!confirm('Delete user "' + name + '"?')) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('/inventory_system/api/users/delete.php', {method:'POST', body: fd});
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
