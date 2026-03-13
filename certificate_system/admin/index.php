<?php
$page_title = 'Admin Dashboard';
require_once '../includes/header.php';
requireAdmin();

// Get statistics
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $total_users = $stmt->fetch()['count'];
    
    // Total certificates
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM certificates");
    $total_certificates = $stmt->fetch()['count'];
    
    // Total templates
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM certificate_templates WHERE is_active = 1");
    $total_templates = $stmt->fetch()['count'];
    
    // Certificates this month
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM certificates 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ");
    $certificates_this_month = $stmt->fetch()['count'];
    
    // Recent certificates
    $stmt = $pdo->query("
        SELECT c.*, u.name as user_name, t.template_name 
        FROM certificates c 
        JOIN users u ON c.user_id = u.id 
        JOIN certificate_templates t ON c.template_id = t.id 
        ORDER BY c.created_at DESC 
        LIMIT 5
    ");
    $recent_certificates = $stmt->fetchAll();
    
    // Recent activities
    $stmt = $pdo->query("
        SELECT a.*, u.name as user_name 
        FROM activity_logs a 
        LEFT JOIN users u ON a.user_id = u.id 
        ORDER BY a.created_at DESC 
        LIMIT 10
    ");
    $recent_activities = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $total_users = $total_certificates = $total_templates = $certificates_this_month = 0;
    $recent_certificates = $recent_activities = [];
}
?>

<div class="container">
    <h1 style="margin-bottom: 30px;">📊 Admin Dashboard</h1>
    
    <?php echo displaySuccessMessage(); ?>
    <?php echo displayErrorMessage(); ?>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card" style="border-left-color: #1a73e8;">
            <h3>Total Users</h3>
            <div class="stat-number" style="color: #1a73e8;"><?php echo $total_users; ?></div>
            <p style="color: #666; font-size: 14px; margin-top: 10px;">Active users in system</p>
        </div>
        
        <div class="stat-card" style="border-left-color: #28a745;">
            <h3>Total Certificates</h3>
            <div class="stat-number" style="color: #28a745;"><?php echo $total_certificates; ?></div>
            <p style="color: #666; font-size: 14px; margin-top: 10px;">Certificates issued</p>
        </div>
        
        <div class="stat-card" style="border-left-color: #ffc107;">
            <h3>Active Templates</h3>
            <div class="stat-number" style="color: #ffc107;"><?php echo $total_templates; ?></div>
            <p style="color: #666; font-size: 14px; margin-top: 10px;">Ready to use</p>
        </div>
        
        <div class="stat-card" style="border-left-color: #17a2b8;">
            <h3>This Month</h3>
            <div class="stat-number" style="color: #17a2b8;"><?php echo $certificates_this_month; ?></div>
            <p style="color: #666; font-size: 14px; margin-top: 10px;">Certificates issued</p>
        </div>
    </div>
    
    <!-- Recent Certificates -->
    <div class="card">
        <div class="card-header">
            <h2>📜 Recent Certificates</h2>
            <a href="certificates.php" class="btn btn-primary btn-sm">View All</a>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Certificate Number</th>
                            <th>User</th>
                            <th>Template</th>
                            <th>Issue Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_certificates)): ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 40px; color: #999;">
                                    No certificates generated yet
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_certificates as $cert): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($cert['certificate_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($cert['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars($cert['template_name']); ?></td>
                                    <td><?php echo formatDate($cert['issued_date']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $cert['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($cert['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../<?php echo htmlspecialchars($cert['file_path']); ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h2>📋 Recent Activity</h2>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_activities)): ?>
                            <tr>
                                <td colspan="5" class="text-center" style="padding: 40px; color: #999;">
                                    No activities logged yet
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['user_name'] ?? 'System'); ?></td>
                                    <td><span class="badge badge-info"><?php echo htmlspecialchars($activity['action']); ?></span></td>
                                    <td><?php echo htmlspecialchars($activity['description']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['ip_address']); ?></td>
                                    <td><?php echo formatDateTime($activity['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
