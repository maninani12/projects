<?php
$page_title = 'My Certificates';
require_once '../includes/header.php';
requireLogin();

// Get current user's certificates only
try {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               t.template_name, 
               issuer.name as issuer_name
        FROM certificates c 
        JOIN certificate_templates t ON c.template_id = t.id 
        LEFT JOIN users issuer ON c.issued_by = issuer.id
        WHERE c.user_id = ? AND c.status = 'active'
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $certificates = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log("User certificates error: " . $e->getMessage());
    $certificates = [];
}
?>

<div class="container">
    <div style="margin-bottom: 30px;">
        <h1>📜 My Certificates</h1>
        <p style="color: #666; margin-top: 10px;">
            Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>! 
            Here are all your certificates.
        </p>
    </div>
    
    <?php echo displaySuccessMessage(); ?>
    <?php echo displayErrorMessage(); ?>
    
    <?php if (empty($certificates)): ?>
        <div class="card">
            <div class="card-body" style="text-align: center; padding: 60px 20px;">
                <div style="font-size: 72px; margin-bottom: 20px;">📭</div>
                <h2 style="color: #666; margin-bottom: 10px;">No Certificates Yet</h2>
                <p style="color: #999; font-size: 16px;">
                    You don't have any certificates at the moment. 
                    Certificates will appear here once they are issued to you.
                </p>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h2>Your Certificates (<?php echo count($certificates); ?>)</h2>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Certificate Number</th>
                                <th>Template/Type</th>
                                <th>Issue Date</th>
                                <th>Issued By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificates as $cert): ?>
                                <tr>
                                    <td>
                                        <strong style="color: #1a73e8;">
                                            <?php echo htmlspecialchars($cert['certificate_number']); ?>
                                        </strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($cert['template_name']); ?></td>
                                    <td><?php echo formatDate($cert['issued_date']); ?></td>
                                    <td><?php echo htmlspecialchars($cert['issuer_name'] ?? 'System'); ?></td>
                                    <td>
                                        <span class="badge badge-success">
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
                                        <a href="../<?php echo htmlspecialchars($cert['file_path']); ?>" 
                                           download 
                                           class="btn btn-sm btn-success"
                                           title="Download Certificate">
                                            ⬇️ Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Certificate Stats -->
        <div class="stats-grid" style="margin-top: 30px;">
            <div class="stat-card">
                <h3>Total Certificates</h3>
                <div class="stat-number" style="color: #1a73e8;"><?php echo count($certificates); ?></div>
                <p style="color: #666; font-size: 14px; margin-top: 10px;">Certificates earned</p>
            </div>
            
            <div class="stat-card">
                <h3>Latest Certificate</h3>
                <div style="font-size: 18px; color: #28a745; margin-top: 10px;">
                    <?php echo formatDate($certificates[0]['issued_date']); ?>
                </div>
                <p style="color: #666; font-size: 14px; margin-top: 10px;">Most recent</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>