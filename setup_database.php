<?php
// setup_database.php - Create database and tables
$db_path = 'database/chemicals.db';

echo "<h2>Setting up Chemical Inventory Database</h2>";

// Create directories if they don't exist
if (!is_dir('database')) {
    mkdir('database', 0755, true);
    echo "<div class='alert alert-info'>Created database directory</div>";
}

if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
    echo "<div class='alert alert-info'>Created uploads directory</div>";
}

try {
    // Create or open database
    $db = new PDO('sqlite:' . $db_path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div class='alert alert-success'>✅ Connected to database</div>";
    
    // Create users table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        full_name TEXT NOT NULL,
        role TEXT DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active INTEGER DEFAULT 1
    )");
    echo "<div class='alert alert-success'>✅ Created users table</div>";
    
    // Create categories table
    $db->exec("CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<div class='alert alert-success'>✅ Created categories table</div>";
    
    // Create chemicals table
    $db->exec("CREATE TABLE IF NOT EXISTS chemicals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cas_number TEXT NOT NULL,
        name TEXT NOT NULL,
        formula TEXT,
        category_id INTEGER,
        quantity REAL NOT NULL,
        unit TEXT DEFAULT 'g',
        purity REAL,
        expiry_date DATE,
        reorder_level REAL,
        location TEXT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )");
    echo "<div class='alert alert-success'>✅ Created chemicals table</div>";
    
    // Create admin user
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Check if admin already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, full_name, role) 
                              VALUES (?, ?, ?, ?, 'admin')");
        $stmt->execute(['admin', 'admin@lab.com', $password_hash, 'System Administrator']);
        echo "<div class='alert alert-success'>✅ Created admin user</div>";
    } else {
        echo "<div class='alert alert-warning'>⚠️ Admin user already exists</div>";
    }
    
    // Add sample categories
    $categories = [
        ['Solvents', 'Organic and inorganic solvents'],
        ['Acids', 'Various laboratory acids'],
        ['Bases', 'Alkaline chemicals'],
        ['Salts', 'Inorganic salts'],
        ['Organics', 'Organic compounds']
    ];
    
    foreach ($categories as $cat) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute($cat);
    }
    echo "<div class='alert alert-success'>✅ Added sample categories</div>";
    
    // Add sample chemicals
    $chemicals = [
        ['64-17-5', 'Ethanol', 'C2H5OH', 1, 5000, 'mL', 'Storage Room A'],
        ['7664-93-9', 'Sulfuric Acid', 'H2SO4', 2, 1000, 'mL', 'Acid Cabinet'],
        ['1310-73-2', 'Sodium Hydroxide', 'NaOH', 3, 500, 'g', 'Base Cabinet'],
        ['7647-14-5', 'Sodium Chloride', 'NaCl', 4, 1000, 'g', 'Salt Shelf']
    ];
    
    foreach ($chemicals as $chem) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO chemicals (cas_number, name, formula, category_id, quantity, unit, location) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($chem);
    }
    echo "<div class='alert alert-success'>✅ Added sample chemicals</div>";
    
    echo "<div class='alert alert-success mt-3'><strong>🎉 Setup completed successfully!</strong></div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Setup failed: " . $e->getMessage() . "</div>";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Complete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .container { max-width: 800px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h4>Setup Complete!</h4>
            </div>
            <div class="card-body">
                <h5>Login Credentials:</h5>
                <div class="alert alert-info">
                    <p><strong>Username:</strong> admin</p>
                    <p><strong>Password:</strong> admin123</p>
                </div>
                
                <div class="alert alert-danger">
                    <strong>⚠️ IMPORTANT:</strong> Change this password after first login!
                </div>
                
                <div class="mt-4">
                    <a href="auth/login.php" class="btn btn-primary btn-lg">
                        Go to Login Page
                    </a>
                    <a href="index.php" class="btn btn-secondary btn-lg">
                        Go to Home Page
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>