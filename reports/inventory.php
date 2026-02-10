<?php
$pageTitle = "Inventory Report";
require_once '../config/config.php';
requireLogin();

$db = Database::getInstance()->getConnection();

// Get inventory summary
$query = "
    SELECT 
        c.category_id,
        cat.name as category_name,
        COUNT(*) as item_count,
        SUM(c.quantity) as total_quantity,
        SUM(CASE WHEN c.quantity <= c.reorder_level AND c.reorder_level > 0 THEN 1 ELSE 0 END) as low_stock_count,
        SUM(CASE WHEN c.expiry_date IS NOT NULL AND c.expiry_date <= date('now', '+30 days') THEN 1 ELSE 0 END) as expiring_count
    FROM chemicals c 
    LEFT JOIN categories cat ON c.category_id = cat.id 
    GROUP BY c.category_id, cat.name 
    ORDER BY cat.name
";

$summary = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Get low stock items
$low_stock = $db->query("
    SELECT c.*, cat.name as category_name 
    FROM chemicals c 
    LEFT JOIN categories cat ON c.category_id = cat.id 
    WHERE c.quantity <= c.reorder_level AND c.reorder_level > 0 
    ORDER BY (c.quantity / c.reorder_level)
")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clipboard-data"></i> Inventory Report</h2>
    <div class="btn-group">
        <a href="export_inventory.php" class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Summary by Category</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th>Items</th>
                                <th>Total Quantity</th>
                                <th>Low Stock Items</th>
                                <th>Expiring Soon</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summary as $cat): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($cat['category_name'] ?? 'Uncategorized'); ?></strong></td>
                                    <td><?php echo $cat['item_count']; ?></td>
                                    <td><?php echo round($cat['total_quantity'], 2); ?></td>
                                    <td>
                                        <?php if ($cat['low_stock_count'] > 0): ?>
                                            <span class="badge bg-warning"><?php echo $cat['low_stock_count']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($cat['expiring_count'] > 0): ?>
                                            <span class="badge bg-danger"><?php echo $cat['expiring_count']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($cat['low_stock_count'] > 0 && $cat['expiring_count'] > 0): ?>
                                            <span class="badge bg-danger">Attention Needed</span>
                                        <?php elseif ($cat['low_stock_count'] > 0): ?>
                                            <span class="badge bg-warning">Low Stock</span>
                                        <?php elseif ($cat['expiring_count'] > 0): ?>
                                            <span class="badge bg-warning">Expiring Soon</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Good</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Low Stock Items</h5>
            </div>
            <div class="card-body">
                <?php if ($low_stock): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Chemical</th>
                                    <th>CAS</th>
                                    <th>Category</th>
                                    <th>Current Quantity</th>
                                    <th>Reorder Level</th>
                                    <th>Remaining %</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock as $chem): 
                                    $percentage = ($chem['quantity'] / $chem['reorder_level']) * 100;
                                ?>
                                    <tr class="quantity-low">
                                        <td><strong><?php echo htmlspecialchars($chem['name']); ?></strong></td>
                                        <td><code><?php echo htmlspecialchars($chem['cas_number']); ?></code></td>
                                        <td><?php echo htmlspecialchars($chem['category_name'] ?? '-'); ?></td>
                                        <td><?php echo $chem['quantity']; ?> <?php echo $chem['unit']; ?></td>
                                        <td><?php echo $chem['reorder_level']; ?> <?php echo $chem['unit']; ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-<?php echo $percentage < 20 ? 'danger' : ($percentage < 50 ? 'warning' : 'info'); ?>" 
                                                     role="progressbar" style="width: <?php echo min($percentage, 100); ?>%">
                                                    <?php echo round($percentage, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($percentage < 20): ?>
                                                <span class="badge bg-danger">Critical</span>
                                            <?php elseif ($percentage < 50): ?>
                                                <span class="badge bg-warning">Low</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Below Reorder</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="../chemicals/view.php?id=<?php echo $chem['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <a href="../transactions/add.php?chemical_id=<?php echo $chem['id']; ?>&type=checkin" 
                                               class="btn btn-sm btn-success">
                                                <i class="bi bi-plus-circle"></i> Reorder
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle display-1 text-success"></i>
                        <h4 class="mt-3">No Low Stock Items</h4>
                        <p class="text-muted">All inventory levels are above reorder points</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>