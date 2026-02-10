<?php
session_start();

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Connect to database
try {
    $db = new SQLite3('database/chemicals.db');
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Get all chemicals with category names
$result = $db->query("
    SELECT c.*, cat.name as category_name 
    FROM chemicals c 
    LEFT JOIN categories cat ON c.category_id = cat.id 
    ORDER BY c.name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chemical List - Chemical Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .navbar { margin-bottom: 30px; }
        .table th { background: #f1f3f4; }
        .cas-number { font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Chemical Management System</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link active" href="chemicals.php">Chemicals</a>
                <span class="nav-link"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Chemical Inventory</h1>
            <a href="add_chemical.php" class="btn btn-primary">+ Add New Chemical</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if ($result): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>CAS Number</th>
                                    <th>Name</th>
                                    <th>Formula</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($chemical = $result->fetchArray(SQLITE3_ASSOC)): ?>
                                    <tr>
                                        <td>
                                            <span class="cas-number"><?php echo htmlspecialchars($chemical['cas_number']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($chemical['name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($chemical['formula'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($chemical['category_name'] ?? '-'); ?></td>
                                        <td><?php echo $chemical['quantity'] . ' ' . $chemical['unit']; ?></td>
                                        <td><?php echo htmlspecialchars($chemical['location'] ?? '-'); ?></td>
                                        <td>
                                            <a href="view_chemical.php?id=<?php echo $chemical['id']; ?>" 
                                               class="btn btn-sm btn-info" title="View">
                                                View
                                            </a>
                                            <a href="edit_chemical.php?id=<?php echo $chemical['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <h4>No Chemicals Found</h4>
                        <p class="text-muted">Start by adding your first chemical</p>
                        <a href="add_chemical.php" class="btn btn-primary">Add Chemical</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>