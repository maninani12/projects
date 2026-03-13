<?php
$page_title = 'Manage Users';
require_once '../includes/header.php';
requireAdmin();

// Get all users with their roles
try {
    $stmt = $pdo->query("
        SELECT u.*, r.role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
    
    // Get all active roles for dropdown
    $stmt = $pdo->query("SELECT * FROM roles WHERE is_active = 1 ORDER BY role_name");
    $roles = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Users page error: " . $e->getMessage());
    $users = [];
    $roles = [];
}
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>👥 Manage Users</h2>
            <button class="btn btn-primary btn-sm" onclick="openModal('addUserModal')">➕ Add New User</button>
        </div>
        <div class="card-body">
            <?php echo displaySuccessMessage(); ?>
            <?php echo displayErrorMessage(); ?>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center" style="padding: 40px; color: #999;">
                                    No users found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['role_name'] === 'admin' ? 'danger' : 'info'; ?>">
                                            <?php echo ucfirst(htmlspecialchars($user['role_name'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" 
                                                onclick='editUser(<?php echo json_encode($user, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                            ✏️ Edit
                                        </button>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="../actions/delete_user.php?id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                🗑️ Delete
                                            </a>
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

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New User</h2>
            <span class="modal-close" onclick="closeModal('addUserModal')">&times;</span>
        </div>
        <form action="../actions/save_user.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password * (min 6 characters)</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="role_id">Role *</label>
                <select id="role_id" name="role_id" class="form-control" required>
                    <option value="">-- Select Role --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>">
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" checked> Active
                </label>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Create User</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit User</h2>
            <span class="modal-close" onclick="closeModal('editUserModal')">&times;</span>
        </div>
        <form action="../actions/save_user.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" id="edit_user_id" name="user_id">
            
            <div class="form-group">
                <label for="edit_name">Full Name *</label>
                <input type="text" id="edit_name" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="edit_email">Email Address *</label>
                <input type="email" id="edit_email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="edit_password">Password (leave blank to keep current)</label>
                <input type="password" id="edit_password" name="password" class="form-control" minlength="6">
                <small style="color: #666;">Only fill if you want to change the password</small>
            </div>
            
            <div class="form-group">
                <label for="edit_role_id">Role *</label>
                <select id="edit_role_id" name="role_id" class="form-control" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>">
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1"> Active
                </label>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Update User</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
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

function editUser(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role_id').value = user.role_id;
    document.getElementById('edit_is_active').checked = user.is_active == 1;
    document.getElementById('edit_password').value = '';
    openModal('editUserModal');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>