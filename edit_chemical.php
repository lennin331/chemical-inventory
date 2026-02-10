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

// Get chemical details
$stmt = $db->prepare("SELECT * FROM chemicals WHERE id = :id");
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$chemical = $result->fetchArray(SQLITE3_ASSOC);

if (!$chemical) {
    header('Location: chemicals.php');
    exit();
}

// Get categories for dropdown
$categories = [];
$catResult = $db->query("SELECT * FROM categories ORDER BY name");
while ($row = $catResult->fetchArray(SQLITE3_ASSOC)) {
    $categories[] = $row;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cas_number = trim($_POST['cas_number']);
    $name = trim($_POST['name']);
    $formula = trim($_POST['formula']);
    $category_id = $_POST['category_id'] ?: null;
    $quantity = floatval($_POST['quantity']);
    $unit = $_POST['unit'];
    $location = trim($_POST['location']);
    
    if ($quantity <= 0) {
        $error = "Quantity must be greater than 0";
    } else {
        try {
            $stmt = $db->prepare("
                UPDATE chemicals 
                SET cas_number = :cas, name = :name, formula = :formula, 
                    category_id = :cat_id, quantity = :qty, unit = :unit, location = :location
                WHERE id = :id
            ");
            
            $stmt->bindValue(':cas', $cas_number, SQLITE3_TEXT);
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->bindValue(':formula', $formula, SQLITE3_TEXT);
            $stmt->bindValue(':cat_id', $category_id, SQLITE3_INTEGER);
            $stmt->bindValue(':qty', $quantity, SQLITE3_FLOAT);
            $stmt->bindValue(':unit', $unit, SQLITE3_TEXT);
            $stmt->bindValue(':location', $location, SQLITE3_TEXT);
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
            
            if ($stmt->execute()) {
                $message = "Chemical updated successfully!";
                // Update local chemical data
                $chemical['cas_number'] = $cas_number;
                $chemical['name'] = $name;
                $chemical['formula'] = $formula;
                $chemical['category_id'] = $category_id;
                $chemical['quantity'] = $quantity;
                $chemical['unit'] = $unit;
                $chemical['location'] = $location;
            } else {
                $error = "Failed to update chemical";
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Chemical - Chemical Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .navbar { margin-bottom: 30px; }
        .form-container { max-width: 800px; margin: 0 auto; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">🧪 Chemical Inventory</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="chemicals.php">Chemicals</a>
                <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="form-container">
            <h1 class="mb-4">Edit Chemical: <?php echo htmlspecialchars($chemical['name']); ?></h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CAS Number</label>
                                <input type="text" name="cas_number" class="form-control" 
                                       value="<?php echo htmlspecialchars($chemical['cas_number']); ?>" 
                                       required pattern="\d{2,7}-\d{2}-\d">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chemical Name</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($chemical['name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chemical Formula</label>
                                <input type="text" name="formula" class="form-control" 
                                       value="<?php echo htmlspecialchars($chemical['formula']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-control">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"
                                            <?php echo ($chemical['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" step="0.001" name="quantity" class="form-control" 
                                       value="<?php echo $chemical['quantity']; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit</label>
                                <select name="unit" class="form-control" required>
                                    <option value="g" <?php echo $chemical['unit'] == 'g' ? 'selected' : ''; ?>>g</option>
                                    <option value="kg" <?php echo $chemical['unit'] == 'kg' ? 'selected' : ''; ?>>kg</option>
                                    <option value="mg" <?php echo $chemical['unit'] == 'mg' ? 'selected' : ''; ?>>mg</option>
                                    <option value="mL" <?php echo $chemical['unit'] == 'mL' ? 'selected' : ''; ?>>mL</option>
                                    <option value="L" <?php echo $chemical['unit'] == 'L' ? 'selected' : ''; ?>>L</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Storage Location</label>
                                <input type="text" name="location" class="form-control" 
                                       value="<?php echo htmlspecialchars($chemical['location']); ?>">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Chemical</button>
                            <a href="view_chemical.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                            <a href="chemicals.php" class="btn btn-outline-secondary">Back to List</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>