<?php
$page_title = 'Manage Roles';
require_once '../includes/header.php';
requireAdmin();

// Get all roles
try {
    $stmt = $pdo->query("SELECT * FROM roles ORDER BY id ASC");
    $roles = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Roles page error: " . $e->getMessage());
    $roles = [];
}
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>🔐 Manage Roles</h2>
            <button class="btn btn-primary btn-sm" onclick="openModal('addRoleModal')">➕ Add New Role</button>
        </div>
        <div class="card-body">
            <?php echo displaySuccessMessage(); ?>
            <?php echo displayErrorMessage(); ?>
            
            <div class="alert alert-info" style="margin-bottom: 20px;">
                <strong>ℹ️ Note:</strong> Currently, only 'admin' and 'user' roles are active and functional. 
                You can create additional custom roles here for future use. Custom roles will require 
                permissions implementation to function properly.
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Permissions</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($roles)): ?>
                            <tr>
                                <td colspan="7" class="text-center" style="padding: 40px; color: #999;">
                                    No roles found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td><?php echo $role['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($role['role_name']); ?></strong>
                                        <?php if ($role['id'] <= 2): ?>
                                            <span class="badge badge-warning" style="margin-left: 5px;">System</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($role['description']); ?></td>
                                    <td>
                                        <small style="color: #666;">
                                            <?php echo htmlspecialchars($role['permissions']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $role['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $role['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($role['created_at']); ?></td>
                                    <td>
                                        <?php if ($role['id'] > 2): // Only allow editing custom roles ?>
                                            <button class="btn btn-sm btn-secondary" 
                                                    onclick='editRole(<?php echo json_encode($role, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                                                    title="Edit Role">
                                                ✏️ Edit
                                            </button>
                                            <a href="../actions/delete_role.php?id=<?php echo $role['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this role?')"
                                               title="Delete Role">
                                                🗑️ Delete
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Protected</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Role Modal -->
<div id="addRoleModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Role</h2>
            <span class="modal-close" onclick="closeModal('addRoleModal')">&times;</span>
        </div>
        <form action="../actions/save_role.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="role_name">Role Name *</label>
                <input type="text" id="role_name" name="role_name" class="form-control" required
                       placeholder="e.g., manager, moderator">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"
                          placeholder="Describe the role and its responsibilities"></textarea>
            </div>
            
            <div class="form-group">
                <label for="permissions">Permissions (comma-separated)</label>
                <input type="text" id="permissions" name="permissions" class="form-control" 
                       placeholder="e.g., view_certificates,download_certificates,manage_users">
                <small style="color: #666;">
                    Examples: view_certificates, download_certificates, manage_users, manage_templates, generate_certificates
                </small>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" checked> Active
                </label>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Create Role</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addRoleModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Role Modal -->
<div id="editRoleModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Role</h2>
            <span class="modal-close" onclick="closeModal('editRoleModal')">&times;</span>
        </div>
        <form action="../actions/save_role.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" id="edit_role_id" name="role_id">
            
            <div class="form-group">
                <label for="edit_role_name">Role Name *</label>
                <input type="text" id="edit_role_name" name="role_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="edit_description">Description</label>
                <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="edit_permissions">Permissions (comma-separated)</label>
                <input type="text" id="edit_permissions" name="permissions" class="form-control">
                <small style="color: #666;">
                    Examples: view_certificates, download_certificates, manage_users, manage_templates
                </small>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1"> Active
                </label>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Update Role</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editRoleModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function editRole(role) {
    document.getElementById('edit_role_id').value = role.id;
    document.getElementById('edit_role_name').value = role.role_name;
    document.getElementById('edit_description').value = role.description || '';
    document.getElementById('edit_permissions').value = role.permissions || '';
    document.getElementById('edit_is_active').checked = role.is_active == 1;
    openModal('editRoleModal');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>