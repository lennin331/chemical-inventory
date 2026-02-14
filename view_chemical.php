<?php
session_start();

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get chemical ID from URL
$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: chemicals.php');
    exit();
}

// Connect to database
try {
    $db = new SQLite3('database/chemicals.db');
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Get chemical details with category name
$stmt = $db->prepare("
    SELECT c.*, cat.name as category_name 
    FROM chemicals c 
    LEFT JOIN categories cat ON c.category_id = cat.id 
    WHERE c.id = :id
");
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$chemical = $result->fetchArray(SQLITE3_ASSOC);

if (!$chemical) {
    header('Location: chemicals.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($chemical['name']); ?> - Chemical Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .navbar { margin-bottom: 30px; }
        .chemical-header { background: #e9ecef; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        .info-table th { width: 30%; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Chemical Inventory</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="chemicals.php">Chemicals</a>
                <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="chemical-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2"><?php echo htmlspecialchars($chemical['name']); ?></h1>
                    <p class="text-muted mb-0">
                        CAS: <code><?php echo htmlspecialchars($chemical['cas_number']); ?></code>
                    </p>
                </div>
                <div>
                    <a href="chemicals.php" class="btn btn-secondary">Back to List</a>
                    <a href="edit_chemical.php?id=<?php echo $chemical['id']; ?>" class="btn btn-warning">Edit</a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Chemical Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless info-table">
                            <tr>
                                <th>CAS Number:</th>
                                <td><code><?php echo htmlspecialchars($chemical['cas_number']); ?></code></td>
                            </tr>
                            <tr>
                                <th>Chemical Name:</th>
                                <td><strong><?php echo htmlspecialchars($chemical['name']); ?></strong></td>
                            </tr>
                            <?php if ($chemical['formula']): ?>
                            <tr>
                                <th>Chemical Formula:</th>
                                <td><?php echo htmlspecialchars($chemical['formula']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Category:</th>
                                <td><?php echo htmlspecialchars($chemical['category_name'] ?? 'Uncategorized'); ?></td>
                            </tr>
                            <tr>
                                <th>Quantity:</th>
                                <td>
                                    <span class="display-6">
                                        <?php echo $chemical['quantity']; ?> <?php echo $chemical['unit']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php if ($chemical['location']): ?>
                            <tr>
                                <th>Storage Location:</th>
                                <td><?php echo htmlspecialchars($chemical['location']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($chemical['created_at']): ?>
                            <tr>
                                <th>Date Added:</th>
                                <td><?php echo date('F j, Y', strtotime($chemical['created_at'])); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="edit_chemical.php?id=<?php echo $chemical['id']; ?>" class="btn btn-warning">
                                Edit This Chemical
                            </a>
                            <a href="chemicals.php" class="btn btn-secondary">
                                Back to Chemical List
                            </a>
                            <button onclick="printChemical()" class="btn btn-info">
                                Print Details
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Chemical Safety</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Store in appropriate conditions</li>
                            <li>Use proper personal protective equipment</li>
                            <li>Check expiry date if applicable</li>
                            <li>Follow laboratory safety protocols</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="chemicals.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to All Chemicals
            </a>
        </div>
    </div>
    
    <script>
    function printChemical() {
        window.location.href = "print.php"
    }
    </script>
</body>
</html>
