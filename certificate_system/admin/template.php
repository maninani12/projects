<?php
$page_title = 'Certificate Templates';
require_once '../includes/header.php';
requireAdmin();

// Handle messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Handle file upload if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_template'])) {
    $name = htmlspecialchars($_POST['template_name']);
    $variables = htmlspecialchars($_POST['variables']);
    $html_content = $_POST['template_html'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Handle background image upload
    $bg_path = '';
    if (isset($_FILES['background_image']) && $_FILES['background_image']['tmp_name']) {
        $ext = pathinfo($_FILES['background_image']['name'], PATHINFO_EXTENSION);
        $bg_name = 'bg_' . time() . '.' . $ext;
        $bg_dir = __DIR__ . '/../uploads/templates/';
        if (!file_exists($bg_dir)) mkdir($bg_dir, 0755, true);
        move_uploaded_file($_FILES['background_image']['tmp_name'], $bg_dir . $bg_name);
        $bg_path = 'uploads/templates/' . $bg_name;
    }

    // Save to database
    $stmt = $pdo->prepare("INSERT INTO certificate_templates (template_name, variables, template_html, background_image, is_active) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $variables, $html_content, $bg_path, $is_active]);
    $_SESSION['success'] = "Template '$name' added successfully!";
    header("Location: templates.php");
    exit;
}

// Fetch templates
$stmt = $pdo->query("SELECT * FROM certificate_templates ORDER BY created_at DESC");
$templates = $stmt->fetchAll();
?>

<div class="container mt-4">
    <h2>🎨 Certificate Templates with Background</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Add Template Button -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
        <i class="bi bi-plus-circle"></i> Add Template
    </button>

    <!-- Templates Table -->
    <div class="table-responsive card shadow-sm p-2">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Variables</th>
                    <th>Background</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($templates)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No templates available</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($templates as $t): ?>
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td><?= htmlspecialchars($t['template_name']) ?></td>
                            <td><?= htmlspecialchars($t['variables']) ?></td>
                            <td>
                                <?php if($t['background_image']): ?>
                                    <img src="../<?= $t['background_image'] ?>" style="height:50px; border-radius:4px;">
                                <?php else: ?>
                                    <span class="text-muted">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $t['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $t['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                            <td>
                                <a href="edit_template.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="../actions/delete_template.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this template?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Template Modal -->
<div class="modal fade" id="addTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Certificate Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_template" value="1">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Template Name</label>
                        <input type="text" name="template_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Variables (comma separated)</label>
                        <input type="text" name="variables" class="form-control" placeholder="name,email,date">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Template HTML</label>
                        <textarea name="template_html" class="form-control" rows="6" placeholder="Certificate content HTML" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Background Image</label>
                        <input type="file" name="background_image" class="form-control" accept="image/*">
                        <small class="text-muted">Optional: Upload an image for certificate background</small>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" checked class="form-check-input">
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Template</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
