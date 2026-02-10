<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    $db = new SQLite3('database/chemicals.db');
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Get statistics
$total_chemicals = $db->querySingle("SELECT COUNT(*) FROM chemicals");
$total_categories = $db->querySingle("SELECT COUNT(*) FROM categories");

// Get recent chemicals
$recent_result = $db->query("SELECT * FROM chemicals ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Chemical Inventory</a>
            <div>
                <span class="text-light me-3"><?php echo $_SESSION['user_name']; ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <h1 class="mb-4">Dashboard</h1>
        
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>Total Chemicals</h5>
                        <h2><?php echo $total_chemicals; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Categories</h5>
                        <h2><?php echo $total_categories; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Chemicals -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Chemicals</h5>
            </div>
            <div class="card-body">
                <?php if ($recent_result): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>CAS</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($chem = $recent_result->fetchArray(SQLITE3_ASSOC)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($chem['name']); ?></td>
                                    <td><code><?php echo htmlspecialchars($chem['cas_number']); ?></code></td>
                                    <td><?php echo $chem['quantity'] . ' ' . $chem['unit']; ?></td>
                                    <td>
                                        <a href="view_chemical.php?id=<?php echo $chem['id']; ?>" class="btn btn-sm btn-info">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No chemicals found. <a href="add_chemical.php">Add your first chemical</a></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="mt-3">
            <a href="chemicals.php" class="btn btn-primary">View All Chemicals</a>
            <a href="add_chemical.php" class="btn btn-success">Add New Chemical</a>
        </div>
    </div>
</body>
</html>