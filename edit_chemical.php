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

// Handle delete action
if (isset($_POST['delete'])) {
    try {
        $stmt = $db->prepare("DELETE FROM chemicals WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
        
        $_SESSION['message'] = "Chemical deleted successfully!";
        $_SESSION['message_type'] = "success";
        header('Location: chemicals.php');
        exit();
    } catch (Exception $e) {
        $error = "Error deleting chemical: " . $e->getMessage();
    }
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

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
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
                // Refresh chemical data
                $stmt = $db->prepare("SELECT * FROM chemicals WHERE id = :id");
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $result = $stmt->execute();
                $chemical = $result->fetchArray(SQLITE3_ASSOC);
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { 
            background: #f8f9fa;
            padding-bottom: 20px;
        }
        .navbar { 
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .form-container { 
            max-width: 800px; 
            margin: 0 auto; 
        }
        .delete-btn {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .delete-btn:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        .left-buttons {
            display: flex;
            gap: 10px;
        }
        .right-buttons {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-flask"></i> Chemical Inventory
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="chemicals.php">Chemicals</a>
                <span class="nav-link text-light">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="form-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="bi bi-pencil-square"></i> Edit Chemical
                </h1>
                <a href="chemicals.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
            
            <!-- Messages -->
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Edit Form Card -->
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Chemical Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="editForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">CAS Number</label>
                                <input type="text" name="cas_number" class="form-control" 
                                       value="<?php echo htmlspecialchars($chemical['cas_number']); ?>" 
                                       required pattern="\d{2,7}-\d{2}-\d">
                                <div class="form-text">Format: 123456-78-9</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Chemical Name</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($chemical['name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Chemical Formula</label>
                                <input type="text" name="formula" class="form-control" 
                                       value="<?php echo htmlspecialchars($chemical['formula']); ?>" 
                                       placeholder="e.g., C2H5OH">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Category</label>
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
                                <label class="form-label fw-bold">Quantity</label>
                                <input type="number" step="0.001" name="quantity" class="form-control" 
                                       value="<?php echo $chemical['quantity']; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Unit</label>
                                <select name="unit" class="form-control" required>
                                    <option value="g" <?php echo $chemical['unit'] == 'g' ? 'selected' : ''; ?>>g (grams)</option>
                                    <option value="kg" <?php echo $chemical['unit'] == 'kg' ? 'selected' : ''; ?>>kg (kilograms)</option>
                                    <option value="mg" <?php echo $chemical['unit'] == 'mg' ? 'selected' : ''; ?>>mg (milligrams)</option>
                                    <option value="mL" <?php echo $chemical['unit'] == 'mL' ? 'selected' : ''; ?>>mL (milliliters)</option>
                                    <option value="L" <?php echo $chemical['unit'] == 'L' ? 'selected' : ''; ?>>L (liters)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Storage Location</label>
                                <input type="text" name="location" class="form-control" 
                                       value="<?php echo htmlspecialchars($chemical['location']); ?>" 
                                       placeholder="e.g., Shelf A, Room 101">
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <div class="left-buttons">
                                <button type="submit" name="update" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Chemical
                                </button>
                                <a href="view_chemical.php?id=<?php echo $id; ?>" class="btn btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </div>
                            <div class="right-buttons">
                                <!-- Delete Button (Triggers Modal) -->
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Additional Info Card -->
            <div class="card mt-4 bg-light">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Date Added:</small>
                            <strong><?php echo date('F j, Y', strtotime($chemical['created_at'])); ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Inventory ID:</small>
                            <strong>CHEM-<?php echo str_pad($chemical['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="h5 text-center mb-3">Are you sure you want to delete this chemical?</p>
                    <div class="alert alert-warning">
                        <strong><?php echo htmlspecialchars($chemical['name']); ?></strong><br>
                        CAS: <?php echo htmlspecialchars($chemical['cas_number']); ?>
                    </div>
                    <p class="text-danger mb-0">
                        <i class="bi bi-info-circle"></i> 
                        This action cannot be undone. All data will be permanently deleted.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="delete" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete Permanently
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-format CAS number -->
    <script>
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
