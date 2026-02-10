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

// Get categories for dropdown
$categories = [];
$result = $db->query("SELECT * FROM categories ORDER BY name");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
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
    
    // Validate CAS number (basic check)
    if (!preg_match('/^\d{2,7}-\d{2}-\d$/', $cas_number)) {
        $error = "Invalid CAS number format. Use format: 123456-78-9";
    } elseif ($quantity <= 0) {
        $error = "Quantity must be greater than 0";
    } else {
        try {
            $stmt = $db->prepare("
                INSERT INTO chemicals (cas_number, name, formula, category_id, quantity, unit, location)
                VALUES (:cas, :name, :formula, :cat_id, :qty, :unit, :location)
            ");
            
            $stmt->bindValue(':cas', $cas_number, SQLITE3_TEXT);
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->bindValue(':formula', $formula, SQLITE3_TEXT);
            $stmt->bindValue(':cat_id', $category_id, SQLITE3_INTEGER);
            $stmt->bindValue(':qty', $quantity, SQLITE3_FLOAT);
            $stmt->bindValue(':unit', $unit, SQLITE3_TEXT);
            $stmt->bindValue(':location', $location, SQLITE3_TEXT);
            
            if ($stmt->execute()) {
                $message = "Chemical added successfully!";
                // Clear form
                $_POST = [];
            } else {
                $error = "Failed to add chemical. It might already exist.";
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
    <title>Add Chemical - Chemical Inventory</title>
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
            <h1 class="mb-4">Add New Chemical</h1>
            
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
                                <label class="form-label">CAS Number *</label>
                                <input type="text" name="cas_number" class="form-control" 
                                       value="<?php echo $_POST['cas_number'] ?? ''; ?>" 
                                       placeholder="e.g., 64-17-5" required pattern="\d{2,7}-\d{2}-\d">
                                <div class="form-text">Format: 123456-78-9</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chemical Name *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo $_POST['name'] ?? ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chemical Formula</label>
                                <input type="text" name="formula" class="form-control" 
                                       value="<?php echo $_POST['formula'] ?? ''; ?>" 
                                       placeholder="e.g., C2H5OH">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-control">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"
                                            <?php echo ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantity *</label>
                                <input type="number" step="0.001" name="quantity" class="form-control" 
                                       value="<?php echo $_POST['quantity'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit *</label>
                                <select name="unit" class="form-control" required>
                                    <option value="g" <?php echo ($_POST['unit'] ?? 'g') == 'g' ? 'selected' : ''; ?>>g (grams)</option>
                                    <option value="kg" <?php echo ($_POST['unit'] ?? '') == 'kg' ? 'selected' : ''; ?>>kg (kilograms)</option>
                                    <option value="mg" <?php echo ($_POST['unit'] ?? '') == 'mg' ? 'selected' : ''; ?>>mg (milligrams)</option>
                                    <option value="mL" <?php echo ($_POST['unit'] ?? '') == 'mL' ? 'selected' : ''; ?>>mL (milliliters)</option>
                                    <option value="L" <?php echo ($_POST['unit'] ?? '') == 'L' ? 'selected' : ''; ?>>L (liters)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Storage Location</label>
                                <input type="text" name="location" class="form-control" 
                                       value="<?php echo $_POST['location'] ?? ''; ?>" 
                                       placeholder="e.g., Room A, Shelf 3">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Add Chemical</button>
                            <a href="chemicals.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><strong>CAS Number:</strong> Unique identifier for chemicals. Find it on the chemical bottle or online.</li>
                        <li><strong>Formula:</strong> Chemical formula like H₂O, NaCl, C₂H₅OH</li>
                        <li><strong>Location:</strong> Be specific so you can find the chemical easily</li>
                        <li>Required fields are marked with *</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Auto-format CAS number as user types
    document.addEventListener('DOMContentLoaded', function() {
        const casInput = document.querySelector('input[name="cas_number"]');
        if (casInput) {
            casInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/-/g, '');
                if (value.length > 2) {
                    value = value.substring(0, value.length - 3) + '-' + 
                            value.substring(value.length - 3, value.length - 1) + '-' + 
                            value.substring(value.length - 1);
                }
                e.target.value = value;
            });
        }
    });
    </script>
</body>
</html>