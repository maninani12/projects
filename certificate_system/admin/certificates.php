<?php
$page_title = 'Manage Certificates';
require_once '../includes/header.php';
requireAdmin();

// Get all certificates with related data
try {
    $stmt = $pdo->query("
        SELECT c.*, 
               u.name as user_name, 
               u.email as user_email,
               t.template_name, 
               issuer.name as issuer_name
        FROM certificates c 
        JOIN users u ON c.user_id = u.id 
        JOIN certificate_templates t ON c.template_id = t.id 
        LEFT JOIN users issuer ON c.issued_by = issuer.id
        ORDER BY c.created_at DESC
    ");
    $certificates = $stmt->fetchAll();
    
    // Get users for certificate generation
    $stmt = $pdo->query("SELECT id, name, email FROM users WHERE is_active = 1 ORDER BY name");
    $users = $stmt->fetchAll();
    
    // Get templates for certificate generation
    $stmt = $pdo->query("SELECT id, template_name, variables FROM certificate_templates WHERE is_active = 1 ORDER BY template_name");
    $templates = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Certificates page error: " . $e->getMessage());
    $certificates = [];
    $users = [];
    $templates = [];
}
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>📜 Manage Certificates</h2>
            <button class="btn btn-success btn-sm" onclick="openModal('generateCertModal')">➕ Generate Certificate</button>
        </div>
        <div class="card-body">
            <?php echo displaySuccessMessage(); ?>
            <?php echo displayErrorMessage(); ?>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Certificate Number</th>
                            <th>User</th>
                            <th>Template</th>
                            <th>Issue Date</th>
                            <th>Issued By</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($certificates)): ?>
                            <tr>
                                <td colspan="7" class="text-center" style="padding: 40px; color: #999;">
                                    No certificates found. Generate your first certificate!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($certificates as $cert): ?>
                                <tr>
                                    <td>
                                        <strong style="color: #1a73e8;">
                                            <?php echo htmlspecialchars($cert['certificate_number']); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($cert['user_name']); ?><br>
                                        <small style="color: #666;"><?php echo htmlspecialchars($cert['user_email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($cert['template_name']); ?></td>
                                    <td><?php echo formatDate($cert['issued_date']); ?></td>
                                    <td><?php echo htmlspecialchars($cert['issuer_name'] ?? 'System'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $cert['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($cert['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../<?php echo htmlspecialchars($cert['file_path']); ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-primary" 
                                           title="View Certificate">
                                            👁️ View
                                        </a>
                                        <a href="../actions/delete_certificate.php?id=<?php echo $cert['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this certificate? This action cannot be undone.')"
                                           title="Delete Certificate">
                                            🗑️ Delete
                                        </a>
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

<!-- Generate Certificate Modal -->
<div id="generateCertModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h2>Generate New Certificate</h2>
            <span class="modal-close" onclick="closeModal('generateCertModal')">&times;</span>
        </div>
        <form action="../actions/generate_certificate.php" method="POST" id="certForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="user_id">Select User *</label>
                <select id="user_id" name="user_id" class="form-control" required>
                    <option value="">-- Select User --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="template_id">Select Template *</label>
                <select id="template_id" name="template_id" class="form-control" required onchange="loadTemplateVariables()">
                    <option value="">-- Select Template --</option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?php echo $template['id']; ?>" 
                                data-variables="<?php echo htmlspecialchars($template['variables']); ?>">
                            <?php echo htmlspecialchars($template['template_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="templateVariables" style="border-top: 1px solid #ddd; padding-top: 20px; margin-top: 20px;">
                <!-- Dynamic fields will be inserted here -->
            </div>
            
            <div class="form-group">
                <label for="issue_date">Issue Date *</label>
                <input type="date" 
                       id="issue_date" 
                       name="issue_date" 
                       class="form-control" 
                       value="<?php echo date('Y-m-d'); ?>" 
                       max="<?php echo date('Y-m-d'); ?>"
                       required>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-success">✅ Generate Certificate</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('generateCertModal')">Cancel</button>
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
    // Reset form when closing
    if (modalId === 'generateCertModal') {
        document.getElementById('certForm').reset();
        document.getElementById('templateVariables').innerHTML = '';
    }
}

function loadTemplateVariables() {
    const select = document.getElementById('template_id');
    const selectedOption = select.options[select.selectedIndex];
    const variables = selectedOption.getAttribute('data-variables');
    const container = document.getElementById('templateVariables');
    
    container.innerHTML = '';
    
    if (variables && variables.trim() !== '') {
        const vars = variables.split(',');
        let hasVariables = false;
        
        vars.forEach(variable => {
            const varName = variable.trim();
            // Skip certificate_id and issue_date as they're auto-generated
            if (varName && varName !== 'certificate_id' && varName !== 'issue_date') {
                hasVariables = true;
                const formGroup = document.createElement('div');
                formGroup.className = 'form-group';
                
                const label = document.createElement('label');
                const labelText = varName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                label.textContent = labelText + ' *';
                label.setAttribute('for', 'var_' + varName);
                
                const input = document.createElement('input');
                input.type = 'text';
                input.id = 'var_' + varName;
                input.name = 'variables[' + varName + ']';
                input.className = 'form-control';
                input.required = true;
                input.placeholder = 'Enter ' + labelText.toLowerCase();
                
                formGroup.appendChild(label);
                formGroup.appendChild(input);
                container.appendChild(formGroup);
            }
        });
        
        if (hasVariables) {
            const infoDiv = document.createElement('div');
            infoDiv.className = 'alert alert-info';
            infoDiv.style.fontSize = '14px';
            infoDiv.innerHTML = '<strong>Note:</strong> Fill in all template variables. Certificate ID and Issue Date will be generated automatically.';
            container.insertBefore(infoDiv, container.firstChild);
        }
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>