<?php
$page_title = 'Manage Templates';
require_once '../includes/header.php';
requireAdmin();

// Get all templates
try {
    $stmt = $pdo->query("
        SELECT t.*, u.name as creator_name 
        FROM certificate_templates t 
        LEFT JOIN users u ON t.created_by = u.id 
        ORDER BY t.created_at DESC
    ");
    $templates = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("Templates page error: " . $e->getMessage());
    $templates = [];
}
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>🎨 Manage Templates</h2>
            <button class="btn btn-primary btn-sm" onclick="openModal('addTemplateModal')">➕ Add New Template</button>
        </div>
        <div class="card-body">
            <?php echo displaySuccessMessage(); ?>
            <?php echo displayErrorMessage(); ?>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Template Name</th>
                            <th>Variables</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($templates)): ?>
                            <tr>
                                <td colspan="7" class="text-center" style="padding: 40px; color: #999;">
                                    No templates found. Create your first template!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($templates as $template): ?>
                                <tr>
                                    <td><?php echo $template['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($template['template_name']); ?></strong></td>
                                    <td>
                                        <small style="color: #666;">
                                            <?php echo htmlspecialchars($template['variables']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $template['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $template['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($template['creator_name'] ?? 'System'); ?></td>
                                    <td><?php echo formatDate($template['created_at']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" 
                                                onclick='previewTemplate(<?php echo json_encode($template, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                                                title="Preview Template">
                                            👁️ Preview
                                        </button>
                                        <button class="btn btn-sm btn-secondary" 
                                                onclick='editTemplate(<?php echo json_encode($template, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'
                                                title="Edit Template">
                                            ✏️ Edit
                                        </button>
                                        <a href="../actions/delete_template.php?id=<?php echo $template['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this template?')"
                                           title="Delete Template">
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

<!-- Add Template Modal -->
<div id="addTemplateModal" class="modal">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h2>Add New Template</h2>
            <span class="modal-close" onclick="closeModal('addTemplateModal')">&times;</span>
        </div>
        <form action="../actions/save_template.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="template_name">Template Name *</label>
                <input type="text" id="template_name" name="template_name" class="form-control" required 
                       placeholder="e.g., Achievement Certificate">
            </div>
            
            <div class="form-group">
                <label for="variables">Template Variables * (comma-separated)</label>
                <input type="text" id="variables" name="variables" class="form-control" required
                       placeholder="e.g., recipient_name,course_name,issue_date">
                <small style="color: #666;">
                    Variables to be replaced in the template. Use underscores for spaces.<br>
                    <strong>Note:</strong> certificate_id is automatically included.
                </small>
            </div>
            
            <div class="form-group">
                <label for="template_html">Template HTML *</label>
                <textarea id="template_html" name="template_html" class="form-control" rows="12" required
                          placeholder="Enter HTML template with placeholders like {recipient_name}, {course_name}, etc."></textarea>
                <small style="color: #666;">
                    Use placeholders like <code>{variable_name}</code> in your HTML. Example: <code>{recipient_name}</code>, <code>{course_name}</code>
                </small>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" checked> Active
                </label>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Create Template</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('addTemplateModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Template Modal -->
<div id="editTemplateModal" class="modal">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h2>Edit Template</h2>
            <span class="modal-close" onclick="closeModal('editTemplateModal')">&times;</span>
        </div>
        <form action="../actions/save_template.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" id="edit_template_id" name="template_id">
            
            <div class="form-group">
                <label for="edit_template_name">Template Name *</label>
                <input type="text" id="edit_template_name" name="template_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="edit_variables">Template Variables * (comma-separated)</label>
                <input type="text" id="edit_variables" name="variables" class="form-control" required>
                <small style="color: #666;">Variables to be replaced in the template.</small>
            </div>
            
            <div class="form-group">
                <label for="edit_template_html">Template HTML *</label>
                <textarea id="edit_template_html" name="template_html" class="form-control" rows="12" required></textarea>
                <small style="color: #666;">Use placeholders like <code>{variable_name}</code> in your HTML.</small>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1"> Active
                </label>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Update Template</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('editTemplateModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Template Modal -->
<div id="previewTemplateModal" class="modal">
    <div class="modal-content" style="max-width: 1000px;">
        <div class="modal-header">
            <h2>Template Preview</h2>
            <span class="modal-close" onclick="closeModal('previewTemplateModal')">&times;</span>
        </div>
        <div style="background: #f5f5f5; padding: 20px; border-radius: 5px;">
            <div id="previewContent" class="certificate-preview" style="background: white; padding: 20px;"></div>
        </div>
        <div style="margin-top: 20px; text-align: center;">
            <button type="button" class="btn btn-secondary" onclick="closeModal('previewTemplateModal')">Close</button>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function editTemplate(template) {
    document.getElementById('edit_template_id').value = template.id;
    document.getElementById('edit_template_name').value = template.template_name;
    document.getElementById('edit_variables').value = template.variables;
    document.getElementById('edit_template_html').value = template.template_html;
    document.getElementById('edit_is_active').checked = template.is_active == 1;
    openModal('editTemplateModal');
}

function previewTemplate(template) {
    let html = template.template_html;
    const variables = template.variables.split(',');
    
    // Sample data for preview
    const sampleData = {
        'recipient_name': 'John Doe',
        'course_name': 'Web Development Masterclass',
        'issue_date': '<?php echo date("F d, Y"); ?>',
        'certificate_id': 'CERT-<?php echo date("Y"); ?>-SAMPLE01',
        'completion_date': '<?php echo date("F d, Y"); ?>',
        'grade': 'A+',
        'score': '95%'
    };
    
    // Replace all variables with sample data
    variables.forEach(variable => {
        const varName = variable.trim();
        const placeholder = '{' + varName + '}';
        const value = sampleData[varName] || 'Sample ' + varName.replace(/_/g, ' ');
        // Global replace
        const regex = new RegExp(placeholder.replace(/[.*+?^${}()|[\]\\]/g, '\\===============================================================================
Continue to next message for certificates.php, templates.php, roles.php...
==============================================================================='), 'g');
        html = html.replace(regex, value);
    });
    
    document.getElementById('previewContent').innerHTML = html;
    openModal('previewTemplateModal');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
